<?php
require_once 'config/database.php';
require_once 'config/check_login.php';

if (!isLoggedIn()) {
    header('Location: index.php?login_required=1');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Menghitung jumlah item di keranjang untuk Header
$cart_count = 0;
$cart_count_query = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE user_id = $user_id");
if ($cart_count_query) {
    $cart_count = $cart_count_query->fetch_assoc()['total'] ?? 0;
}

// Fetch cart items
$cart_query = "
    SELECT 
        c.id as cart_id,
        c.quantity,
        c.added_at,
        p.id as product_id,
        p.name,
        p.price,
        p.image_url,
        p.category,
        p.stock
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
    ORDER BY c.added_at DESC
";

$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_result = $stmt->get_result();
$cart_items = $cart_result->fetch_all(MYSQLI_ASSOC);

if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

// Calculate totals
$subtotal = 0;
$total_items = 0;
foreach ($cart_items as $item) {
    $item_total = $item['price'] * $item['quantity'];
    $subtotal += $item_total;
    $total_items += $item['quantity'];
}

$tax = $subtotal * 0.11;
$shipping = $total_items > 0 ? 25000 : 0;
$grand_total = $subtotal + $tax + $shipping;

// Fetch user data
$user_query = "SELECT first_name, last_name, email, phone_number, address, city, province, postal_code FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = $conn->real_escape_string(trim($_POST['shipping_address']));
    $shipping_city = $conn->real_escape_string(trim($_POST['shipping_city']));
    $shipping_province = $conn->real_escape_string(trim($_POST['shipping_province']));
    $shipping_postal_code = $conn->real_escape_string(trim($_POST['shipping_postal_code']));
    $payment_method = $conn->real_escape_string($_POST['payment_method']);
    $bank_name = isset($_POST['bank_option']) ? $conn->real_escape_string($_POST['bank_option']) : null;
    $notes = $conn->real_escape_string(trim($_POST['notes']));
    
    // Generate unique order number
    $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
    
    // Combine full shipping address
    $full_shipping_address = $shipping_address . ", " . $shipping_city . ", " . $shipping_province . " " . $shipping_postal_code;
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert order
        $order_query = "INSERT INTO orders (order_number, user_id, total_amount, status, shipping_address, payment_method, bank_name, payment_status) 
                VALUES (?, ?, ?, 'pending', ?, ?, ?, 'pending')";

        $stmt = $conn->prepare($order_query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        // Bind parameters: s i d s s
        $stmt->bind_param("sidsss", 
            $order_number, 
            $user_id, 
            $grand_total, 
            $full_shipping_address, 
            $payment_method,
            $bank_name
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $order_id = $stmt->insert_id;
        
        if (!$order_id) {
            throw new Exception("Failed to get order ID");
        }
        
        // Insert order items and update product stock
        foreach ($cart_items as $item) {
            $item_subtotal = $item['price'] * $item['quantity'];
            
            // Insert order item
            $order_item_query = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal) 
                                 VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($order_item_query);
            
            if (!$stmt) {
                throw new Exception("Order item prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("iisidd", $order_id, $item['product_id'], $item['name'], $item['quantity'], $item['price'], $item_subtotal);
            
            if (!$stmt->execute()) {
                throw new Exception("Order item execute failed: " . $stmt->error);
            }
            
            // Update product stock
            $update_stock_query = "UPDATE products SET stock = stock - ? WHERE id = ?";
            $stmt = $conn->prepare($update_stock_query);
            
            if (!$stmt) {
                throw new Exception("Update stock prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            
            if (!$stmt->execute()) {
                throw new Exception("Update stock execute failed: " . $stmt->error);
            }
        }
        
        // Clear cart
        $clear_cart_query = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($clear_cart_query);
        
        if (!$stmt) {
            throw new Exception("Clear cart prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Clear cart execute failed: " . $stmt->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        // Redirect to order confirmation
        header("Location: order_confirmation.php?order_id=$order_id");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Transaction failed: " . $e->getMessage());
        $message = 'Terjadi kesalahan saat memproses pesanan: ' . $e->getMessage();
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Hardjadinata Karya Utama</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* Warna Tema Baru HKU */
            --primary-blue: #003893; 
            --primary-red: #e30613;
            --light-gray: #f8f9fa;
            --dark-gray: #222;
        }

        body {
            font-family: "Poppins", sans-serif;
            background-color: var(--light-gray);
            color: var(--dark-gray);
            margin: 0;
            padding: 0;
        }

        /* ===== STICKY HEADER HKU ===== */
        .sticky-wrapper {
            position: sticky;
            top: 0;
            z-index: 9999;
        }

        .hku-header-top {
            background-color: var(--primary-blue);
            color: white;
            padding: 15px 0;
            position: relative;
            z-index: 9999;
        }

        .hku-brand-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .hku-brand-section img {
            height: 65px;
            background: white;
            border-radius: 40px;
            padding: 4px;
        }

        .hku-brand-text h1 {
            font-size: 26px;
            font-weight: 800;
            margin: 0;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .hku-brand-text p {
            font-size: 14px;
            margin: 0;
            font-weight: 400;
            letter-spacing: 0.5px;
        }

        .hku-header-actions {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .search-bar {
            position: relative;
            width: 300px;
        }

        .search-bar input {
            border-radius: 4px;
            border: none;
            padding: 8px 40px 8px 15px;
            font-size: 14px;
            width: 100%;
        }

        .search-bar button {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--primary-blue);
            cursor: pointer;
        }

        .nav-icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: white !important;
            text-decoration: none;
            font-size: 12px;
            transition: color 0.3s;
        }

        .nav-icon i {
            font-size: 20px;
            margin-bottom: 3px;
        }

        .nav-icon:hover {
            color: var(--primary-red) !important;
        }

        /* Garis Merah Pemisah */
        .hku-divider {
            height: 5px;
            background-color: var(--primary-red);
            width: 100%;
        }

        /* Menu Navigasi Putih */
        .hku-main-nav {
            background-color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            position: relative;
            z-index: 9998;
        }

        .hku-nav-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .hku-nav-link {
            padding: 15px 30px;
            font-weight: 700;
            color: var(--primary-blue);
            text-decoration: none;
            text-transform: uppercase;
            border-bottom: 4px solid transparent;
            transition: all 0.3s;
            font-size: 15px;
        }

        .hku-nav-link:hover, .hku-nav-link.active {
            color: var(--primary-red);
            background-color: #fcfcfc;
            border-bottom-color: var(--primary-red);
        }

        /* User dropdown */
        .dropdown-menu {
            position: absolute;
            right: 0;
            top: 100%;
            min-width: 200px;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 8px;
            z-index: 1000;
            display: none;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            color: var(--dark-gray);
            transition: background 0.3s;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: var(--primary-blue);
        }

        /* Cart Badge */
        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--primary-red);
            color: white;
            font-size: 10px;
            min-width: 16px;
            height: 16px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            border: 2px solid var(--primary-blue);
        }

        /* ===== CHECKOUT SPECIFIC STYLES ===== */
        .checkout-hero {
            background-color: var(--primary-blue);
            color: white;
            padding: 40px 0;
            text-align: center;
            border-bottom: 5px solid var(--primary-red);
        }

        .checkout-hero h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .checkout-hero p {
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
        }

        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 15px;
        }

        .checkout-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }

        /* Checkout Steps */
        .checkout-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            flex: 1;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #f0f0f0;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-bottom: 10px;
            transition: all 0.3s;
        }

        .step.active .step-circle {
            background-color: var(--primary-blue);
            color: white;
        }

        .step.completed .step-circle {
            background-color: #4CAF50;
            color: white;
        }

        .step.completed .step-circle i { display: block; }
        .step.completed .step-circle span { display: none; }
        .step-circle i { display: none; }

        .step-label { font-size: 12px; color: #666; font-weight: 500; }
        .step.active .step-label { color: var(--primary-blue); font-weight: 600; }

        /* Checkout Sections */
        .checkout-section {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .section-header h2 {
            color: var(--primary-blue);
            font-size: 1.3rem;
            margin: 0;
            font-weight: 700;
        }

        /* Form Styles */
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: var(--dark-gray); }
        .form-control {
            width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px;
            font-size: 14px; transition: all 0.3s;
        }
        .form-control:focus {
            border-color: var(--primary-blue); outline: none; box-shadow: 0 0 0 2px rgba(0, 56, 147, 0.2);
        }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }

        /* Payment Methods */
        .payment-methods { display: grid; gap: 15px; }
        .payment-method { position: relative; }
        .payment-method input[type="radio"] { position: absolute; opacity: 0; }
        .payment-label {
            display: block; padding: 20px; border: 2px solid #e0e0e0; border-radius: 8px;
            cursor: pointer; transition: all 0.3s;
        }
        .payment-label:hover { border-color: #ccc; background-color: #f9f9f9; }
        .payment-method input[type="radio"]:checked + .payment-label {
            border-color: var(--primary-blue); background-color: #f0f7ff;
        }

        .payment-info { display: flex; align-items: center; gap: 15px; }
        .payment-icon { font-size: 24px; color: var(--primary-blue); min-width: 40px; }
        .payment-details h4 { margin: 0 0 5px 0; color: var(--dark-gray); font-weight: 600;}
        .payment-details p { margin: 0; color: #666; font-size: 13px; }

        .payment-banks {
            padding: 15px; background-color: #f9f9f9; border-radius: 8px; margin-top: 10px; display: none;
        }

        .bank-option {
            display: flex; align-items: center; padding: 10px; border: 1px solid #e0e0e0;
            border-radius: 6px; margin-bottom: 10px; cursor: pointer; transition: all 0.3s;
        }
        .bank-option:hover { background-color: white; border-color: #ccc; }
        .bank-option input { margin-right: 10px; }
        .bank-logo { width: 40px; height: 40px; object-fit: contain; margin-right: 15px; }
        .bank-info h5 { margin: 0; font-size: 14px; font-weight: 600;}
        .bank-info p { margin: 0; font-size: 12px; color: #666; }

        .bank-option input[type="radio"]:checked ~ .bank-info h5 { color: var(--primary-blue); }
        .bank-option input[type="radio"]:checked { accent-color: var(--primary-blue); }
        .bank-option:has(input:checked) { border-color: var(--primary-blue); background-color: #f0f7ff; }

        .qris-code {
            text-align: center; padding: 20px; background-color: white; border-radius: 8px;
            border: 1px solid #e0e0e0; margin-top: 10px; display: none;
        }
        .qris-image { max-width: 200px; margin: 0 auto 15px; }
        .qris-image img { width: 100%; height: auto; }
        .qris-info { font-size: 12px; color: #666; }

        /* Order Summary */
        .order-summary {
            background-color: white; border-radius: 10px; padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); height: fit-content;
            position: sticky; top: 150px;
        }
        .order-summary h2 {
            color: var(--primary-blue); font-size: 1.3rem; margin-bottom: 25px;
            padding-bottom: 15px; border-bottom: 1px solid #eee; font-weight: 700;
        }

        .product-list { max-height: 300px; overflow-y: auto; margin-bottom: 20px; }
        .product-item { display: flex; align-items: center; padding: 15px 0; border-bottom: 1px solid #f0f0f0; }
        .product-item:last-child { border-bottom: none; }
        .product-img { width: 60px; height: 60px; border-radius: 8px; overflow: hidden; flex-shrink: 0; margin-right: 15px; border: 1px solid #eee;}
        .product-img img { width: 100%; height: 100%; object-fit: cover; }
        .product-info { flex: 1; }
        .product-name { font-weight: 600; margin-bottom: 5px; font-size: 14px; }
        .product-meta { display: flex; justify-content: space-between; font-size: 13px; color: #666; }

        .price-summary { margin: 20px 0; }
        .summary-row {
            display: flex; justify-content: space-between; margin-bottom: 10px;
            padding-bottom: 10px; border-bottom: 1px solid #f0f0f0;
        }
        .summary-label { color: #666; }
        .summary-value { font-weight: 500; color: var(--dark-gray); }

        .summary-total {
            display: flex; justify-content: space-between; margin-top: 20px;
            padding-top: 20px; border-top: 2px solid #eee; font-size: 1.2rem;
        }
        .total-label { font-weight: 700; color: var(--dark-gray); }
        .total-value { font-weight: 700; color: var(--primary-red); }

        /* Buttons */
        .btn-back {
            background-color: #f8f9fa; color: #666; border: 1px solid #ddd;
            border-radius: 8px; padding: 12px 25px; font-weight: 500; cursor: pointer;
            transition: all 0.3s; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-back:hover { background-color: var(--primary-red); color: white; border-color: var(--primary-red); }

        .btn-submit {
            background-color: var(--primary-blue); color: white; border: none;
            border-radius: 8px; padding: 15px 30px; font-weight: 600; cursor: pointer;
            transition: background-color 0.3s; font-size: 16px; width: 100%;
        }
        .btn-submit:hover { background-color: #002266; }
        .btn-submit:disabled { background-color: #ccc; cursor: not-allowed; }

        .terms-agreement { margin-top: 20px; font-size: 13px; color: #666; }
        .terms-agreement a { color: var(--primary-blue); text-decoration: none; font-weight: 600; }
        .terms-agreement a:hover { text-decoration: underline; color: var(--primary-red); }

        /* ===== FOOTER HKU ===== */
        .footer {
            background-color: #001f55;
            color: white;
            padding: 60px 0 30px;
            margin-top: 60px;
            border-top: 5px solid var(--primary-red);
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .footer-logo-img {
            height: 55px;
            background-color: white;
            border-radius: 30px;
            padding: 3px;
        }

        .footer-brand-title {
            color: white;
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
        }

        .footer h5 { color: white; font-weight: 600; margin-bottom: 25px; font-size: 1.1rem; }
        .footer-links { list-style: none; padding: 0; }
        .footer-links li { margin-bottom: 10px; }
        .footer-links a { color: #ccc; text-decoration: none; transition: color 0.3s; }
        .footer-links a:hover { color: white; }

        .social-icons a {
            display: inline-block; width: 36px; height: 36px; background: rgba(255,255,255,0.1);
            color: white; border-radius: 50%; text-align: center; line-height: 36px;
            margin-right: 10px; transition: background 0.3s;
        }
        .social-icons a:hover { background: var(--primary-red); }

        .copyright {
            text-align: center; margin-top: 40px; padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1); color: #aaa; font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .hku-header-actions { display: none; }
            .checkout-layout { grid-template-columns: 1fr; }
            .order-summary { position: static; margin-top: 30px; }
        }
        @media (max-width: 768px) {
            .form-row { grid-template-columns: 1fr; }
            .checkout-steps { flex-direction: column; gap: 20px; }
            .step { flex-direction: row; text-align: left; gap: 15px; }
            .step-circle { margin-bottom: 0; }
        }
    </style>
</head>
<body>

    <div class="sticky-wrapper">
        <header class="hku-header-top">
            <div class="container d-flex justify-content-between align-items-center">
                <div class="hku-brand-section">
                    <img src="uploads/logoHKU.png" alt="HKU Logo">
                    <div class="hku-brand-text">
                        <h1>HARDJADINATA KARYA UTAMA</h1>
                        <p>Your Trusted Partner in Industrial Spareparts</p>
                    </div>
                </div>

                <div class="hku-header-actions">
                    <div class="search-bar">
                        <input type="text" placeholder="Cari produk, kategori, atau brand">
                        <button type="button"><i class="fas fa-search"></i></button>
                    </div>

                    <a href="cart.php" class="nav-icon">
                        <div style="position: relative;">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-badge" id="cartCount"><?php echo $cart_count; ?></span>
                        </div>
                        <span class="mt-1">Keranjang</span>
                    </a>
                    
                    <div id="userSection">
                        <?php if (isLoggedIn()): ?>
                            <div class="user-dropdown" style="position: relative;">
                                <a href="javascript:void(0);" class="nav-icon" id="userDropdown">
                                    <i class="fas fa-user"></i>
                                    <span class="mt-1">
                                        <?php 
                                        if (isset($_SESSION['first_name']) && !empty($_SESSION['first_name'])) {
                                            echo htmlspecialchars($_SESSION['first_name']);
                                        } else {
                                            echo 'Akun';
                                        }
                                        ?>
                                    </span>
                                </a>
                                <div class="dropdown-menu" id="userDropdownMenu">
                                    <span class="dropdown-item-text" style="display: block; padding: 10px 15px;">
                                        <small>Logged in as:</small><br>
                                        <strong><?php echo htmlspecialchars($_SESSION['user_email']); ?></strong>
                                    </span>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="orders.php" style="display: block; padding: 10px 15px; text-decoration: none;"><i class="fas fa-shopping-bag me-2"></i>My Orders</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="logout.php" style="display: block; padding: 10px 15px; text-decoration: none;">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </header>

        <div class="hku-divider"></div>

        <nav class="hku-main-nav">
            <div class="container hku-nav-container">
                <a href="beranda.php" class="hku-nav-link">BERANDA</a>
                <a href="tentangkami.php" class="hku-nav-link">TENTANG KAMI</a>
                <a href="produk.php" class="hku-nav-link">PRODUK</a>
                <a href="hubungikami.php" class="hku-nav-link">HUBUNGI KAMI</a>
            </div>
        </nav>
    </div>

    <section class="checkout-hero">
        <div class="container">
            <h1>Checkout Pembayaran</h1>
            <p>Selesaikan pembayaran untuk memproses pesanan Anda</p>
        </div>
    </section>

    <div class="checkout-container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show">
                <i class="fas fa-<?php echo $message_type === 'error' ? 'exclamation-circle' : 'check-circle'; ?> me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="checkout-steps">
            <div class="step completed">
                <div class="step-circle">
                    <span>1</span>
                    <i class="fas fa-check"></i>
                </div>
                <div class="step-label">Keranjang</div>
            </div>
            <div class="step active">
                <div class="step-circle">
                    <span>2</span>
                    <i class="fas fa-check"></i>
                </div>
                <div class="step-label">Checkout</div>
            </div>
            <div class="step">
                <div class="step-circle">
                    <span>3</span>
                </div>
                <div class="step-label">Pembayaran</div>
            </div>
            <div class="step">
                <div class="step-circle">
                    <span>4</span>
                </div>
                <div class="step-label">Selesai</div>
            </div>
        </div>

        <div class="checkout-layout">
            <div class="checkout-form">
                <form method="POST" id="checkoutForm">
                    <div class="checkout-section">
                        <div class="section-header">
                            <h2><i class="fas fa-map-marker-alt me-2"></i>Alamat Pengiriman</h2>
                        </div>
                        
                        <div class="form-group">
                            <label for="full_name">Nama Lengkap *</label>
                            <input type="text" class="form-control" id="full_name" 
                                   value="<?php echo htmlspecialchars(($user_data['first_name'] ?? '') . ' ' . ($user_data['last_name'] ?? '')); ?>" 
                                   readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Nomor Telepon *</label>
                            <input type="tel" class="form-control" id="phone" 
                                   value="<?php echo htmlspecialchars($user_data['phone_number'] ?? ''); ?>" 
                                   readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="shipping_address">Alamat Lengkap *</label>
                            <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required><?php echo htmlspecialchars($user_data['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="shipping_city">Kota *</label>
                                <input type="text" class="form-control" id="shipping_city" name="shipping_city" 
                                       value="<?php echo htmlspecialchars($user_data['city'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="shipping_province">Provinsi *</label>
                                <input type="text" class="form-control" id="shipping_province" name="shipping_province" 
                                       value="<?php echo htmlspecialchars($user_data['province'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="shipping_postal_code">Kode Pos *</label>
                            <input type="text" class="form-control" id="shipping_postal_code" name="shipping_postal_code" 
                                   value="<?php echo htmlspecialchars($user_data['postal_code'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="checkout-section">
                        <div class="section-header">
                            <h2><i class="fas fa-credit-card me-2"></i>Metode Pembayaran</h2>
                        </div>
                        
                        <div class="payment-methods">
                            <div class="payment-method">
                                <input type="radio" id="payment_bank" name="payment_method" value="bank_transfer" required>
                                <label for="payment_bank" class="payment-label">
                                    <div class="payment-info">
                                        <div class="payment-icon">
                                            <i class="fas fa-university"></i>
                                        </div>
                                        <div class="payment-details">
                                            <h4>Transfer Bank</h4>
                                            <p>Transfer melalui ATM/Internet Banking/Mobile Banking</p>
                                        </div>
                                    </div>
                                </label>
                                <div class="payment-banks" id="bankOptions">
                                    <label class="bank-option" for="bank_bca">
                                        <input type="radio" id="bank_bca" name="bank_option" value="bca">
                                        <img src="uploads/logobankbca.jpeg" class="bank-logo">
                                        <div class="bank-info">
                                            <h5>Bank Central Asia (BCA)</h5>
                                            <p>1234567890 - Hardjadinata Karya Utama</p>
                                        </div>
                                    </label>

                                    <label class="bank-option" for="bank_mandiri">
                                        <input type="radio" id="bank_mandiri" name="bank_option" value="mandiri">
                                        <img src="uploads/logobankmandiri.png" class="bank-logo">
                                        <div class="bank-info">
                                            <h5>Bank Mandiri</h5>
                                            <p>0987654321 - Hardjadinata Karya Utama</p>
                                        </div>
                                    </label>

                                    <label class="bank-option" for="bank_bri">
                                        <input type="radio" id="bank_bri" name="bank_option" value="bri">
                                        <img src="uploads/logobankbri.png" class="bank-logo">
                                        <div class="bank-info">
                                            <h5>Bank Rakyat Indonesia (BRI)</h5>
                                            <p>1122334455 - Hardjadinata Karya Utama</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="payment-method">
                                <input type="radio" id="payment_qris" name="payment_method" value="qris" required>
                                <label for="payment_qris" class="payment-label">
                                    <div class="payment-info">
                                        <div class="payment-icon">
                                            <i class="fas fa-qrcode"></i>
                                        </div>
                                        <div class="payment-details">
                                            <h4>QRIS</h4>
                                            <p>Scan QR Code menggunakan aplikasi e-wallet/banking</p>
                                        </div>
                                    </div>
                                </label>
                                <div class="qris-code" id="qrisCode">
                                    <div class="qris-image">
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=HKU-ORDER-<?php echo time(); ?>" alt="QR Code">
                                    </div>
                                    <p class="qris-info">Scan QR Code di atas untuk membayar</p>
                                </div>
                            </div>

                            <div class="payment-method">
                                <input type="radio" id="payment_cod" name="payment_method" value="cod" required>
                                <label for="payment_cod" class="payment-label">
                                    <div class="payment-info">
                                        <div class="payment-icon">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                        <div class="payment-details">
                                            <h4>Cash on Delivery (COD)</h4>
                                            <p>Bayar saat barang diterima (Area Surabaya dan sekitarnya)</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group mt-4">
                            <label for="notes">Catatan Pesanan (Opsional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Catatan tambahan untuk pesanan Anda..."></textarea>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; margin-top: 30px;">
                        <a href="cart.php" class="btn-back">
                            <i class="fas fa-arrow-left"></i>Kembali
                        </a>
                        <button type="submit" class="btn-submit" id="submitOrder" style="width: auto;">
                            <i class="fas fa-lock me-2"></i>Buat Pesanan & Bayar
                        </button>
                    </div>
                </form>
            </div>

            <div class="order-summary">
                <h2>Ringkasan Pesanan</h2>
                
                <div class="product-list">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="product-item">
                            <div class="product-img">
                                <img src="uploads/<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                            <div class="product-info">
                                <div class="product-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="product-meta">
                                    <span><?php echo $item['quantity']; ?> x Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></span>
                                    <span style="color: var(--primary-blue); font-weight: 600;">Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="price-summary">
                    <div class="summary-row">
                        <span class="summary-label">Subtotal</span>
                        <span class="summary-value">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Pajak (11%)</span>
                        <span class="summary-value">Rp <?php echo number_format($tax, 0, ',', '.'); ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Pengiriman</span>
                        <span class="summary-value">Rp <?php echo number_format($shipping, 0, ',', '.'); ?></span>
                    </div>
                </div>
                
                <div class="summary-total">
                    <span class="total-label">Total Pembayaran</span>
                    <span class="total-value">Rp <?php echo number_format($grand_total, 0, ',', '.'); ?></span>
                </div>
                
                <div class="terms-agreement">
                    <p>Dengan mengeklik "Buat Pesanan & Bayar", Anda menyetujui <a href="#">Syarat & Ketentuan</a> dan <a href="#">Kebijakan Privasi</a> Hardjadinata Karya Utama.</p>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="footer-brand">
                        <img src="uploads/logoHKU.png" alt="HKU Logo" class="footer-logo-img">
                        <span class="footer-brand-title">Hardjadinata<br>Karya Utama</span>
                    </div>
                    <p>PT. Hardjadinata Karya Utama - Your trusted partner for industrial spareparts solutions.</p>
                    <div class="social-icons mt-3">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Produk Kami</h5>
                    <ul class="footer-links">
                        <li><a href="produk.php?category=FBR Burner">Burner Series</a></li>
                        <li><a href="produk.php?category=Boiler">Boiler Series</a></li>
                        <li><a href="produk.php?category=Valve & Instrumentation">Valve & Instrumentation</a></li>
                        <li><a href="produk.php?category=Sparepart">Spare Parts</a></li>
                        <li><a href="produk.php">Semua Produk</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Informasi</h5>
                    <ul class="footer-links">
                        <li><a href="aboutus.php">Tentang Kami</a></li>
                        <li><a href="contact.php">Hubungi Kami</a></li>
                        <li><a href="#">Syarat & Ketentuan</a></li>
                        <li><a href="#">Kebijakan Privasi</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Kontak Info</h5>
                    <p><i class="fas fa-map-marker-alt me-2"></i> Surabaya, Indonesia</p>
                    <p><i class="fas fa-phone me-2"></i> +62 31 1234 5678</p>
                    <p><i class="fas fa-envelope me-2"></i> info@hku.co.id</p>
                </div>
            </div>
            <div class="copyright">
                © Copyright 2026 PT. Hardjadinata Karya Utama. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Dropdown menu functionality for logged in user
        const userDropdown = document.getElementById('userDropdown');
        const userDropdownMenu = document.getElementById('userDropdownMenu');

        if (userDropdown) {
            userDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdownMenu.classList.toggle('show');
            });

            document.addEventListener('click', function() {
                userDropdownMenu.classList.remove('show');
            });

            userDropdownMenu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }

        // Payment method selection
        const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
        const bankOptions = document.getElementById('bankOptions');
        const qrisCode = document.getElementById('qrisCode');
        
        paymentMethods.forEach(method => {
            method.addEventListener('change', function() {
                // Hide all payment details first
                bankOptions.style.display = 'none';
                qrisCode.style.display = 'none';
                
                // Show selected payment details
                if (this.value === 'bank_transfer') {
                    bankOptions.style.display = 'block';
                } else if (this.value === 'qris') {
                    qrisCode.style.display = 'block';
                }
            });
        });

        // Form validation
        const checkoutForm = document.getElementById('checkoutForm');
        const submitButton = document.getElementById('submitOrder');
        
        checkoutForm.addEventListener('submit', function(e) {
            const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
            
            if (!selectedPayment) {
                e.preventDefault();
                alert('Silakan pilih metode pembayaran');
                return false;
            }
            
            if (selectedPayment.value === 'bank_transfer') {
                const selectedBank = document.querySelector('input[name="bank_option"]:checked');
                if (!selectedBank) {
                    e.preventDefault();
                    alert('Silakan pilih bank untuk transfer');
                    return false;
                }
            }
            
            // Validate shipping address fields
            const shippingAddress = document.getElementById('shipping_address').value;
            const shippingCity = document.getElementById('shipping_city').value;
            const shippingProvince = document.getElementById('shipping_province').value;
            const shippingPostalCode = document.getElementById('shipping_postal_code').value;
            
            if (!shippingAddress.trim() || !shippingCity.trim() || !shippingProvince.trim() || !shippingPostalCode.trim()) {
                e.preventDefault();
                alert('Silakan lengkapi semua field alamat pengiriman');
                return false;
            }
            
            // Disable button to prevent double submission
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
            
            return true;
        });

        // Show/hide bank options when bank transfer is selected
        document.getElementById('payment_bank').addEventListener('change', function() {
            if (this.checked) {
                bankOptions.style.display = 'block';
            }
        });

        // Auto-fill address if user data exists
        document.addEventListener('DOMContentLoaded', function() {
            const userAddress = "<?php echo addslashes($user_data['address'] ?? ''); ?>";
            if (userAddress) {
                document.getElementById('shipping_address').value = userAddress;
            }
        });

        // Character counter for notes
        const notesTextarea = document.getElementById('notes');
        notesTextarea.addEventListener('input', function() {
            const maxLength = 500;
            const currentLength = this.value.length;
            
            if (currentLength > maxLength) {
                this.value = this.value.substring(0, maxLength);
                alert('Catatan maksimal 500 karakter');
            }
        });

        // Search logic
        const searchInput = document.querySelector('.search-bar input');
        const searchButton = document.querySelector('.search-bar button');
        const executeSearch = () => {
            const term = searchInput.value.trim();
            if (term !== '') window.location.href = 'produk.php?search=' + encodeURIComponent(term);
        };
        if(searchButton) searchButton.addEventListener('click', executeSearch);
        if(searchInput) searchInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') executeSearch(); });
    </script>
</body>
</html>