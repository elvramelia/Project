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
    header('Location: keranjang.php');
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
    $notes = $conn->real_escape_string(trim($_POST['notes']));
    
    // Generate unique order number
    $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert order
        $order_query = "INSERT INTO orders (order_number, user_id, total_amount, status, shipping_address, payment_method, payment_status) 
                        VALUES (?, ?, ?, 'pending', ?, ?, 'pending')";
        $stmt = $conn->prepare($order_query);
        $stmt->bind_param("sidis", $order_number, $user_id, $grand_total, $shipping_address, $payment_method);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        
        // Insert order items and update product stock
        foreach ($cart_items as $item) {
            $item_subtotal = $item['price'] * $item['quantity'];
            
            // Insert order item
            $order_item_query = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal) 
                                 VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($order_item_query);
            $stmt->bind_param("iisidd", $order_id, $item['product_id'], $item['name'], $item['quantity'], $item['price'], $item_subtotal);
            $stmt->execute();
            
            // Update product stock
            $update_stock_query = "UPDATE products SET stock = stock - ? WHERE id = ?";
            $stmt = $conn->prepare($update_stock_query);
            $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $stmt->execute();
        }
        
        // Clear cart
        $clear_cart_query = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($clear_cart_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Redirect to order confirmation
        header("Location: order_confirmation.php?order_id=$order_id");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $message = 'Terjadi kesalahan saat memproses pesanan. Silakan coba lagi.';
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Megatek Industrial Persada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-blue: #1a4b8c;
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

        /* Top Bar */
        .top-bar {
            background-color: #f0f2f5;
            padding: 5px 0;
            font-size: 12px;
            border-bottom: 1px solid #e0e0e0;
        }

        .top-bar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .top-bar-links {
            display: flex;
            gap: 20px;
        }

        .top-bar-links a {
            color: #666;
            text-decoration: none;
            transition: color 0.3s;
        }

        .top-bar-links a:hover {
            color: var(--primary-blue);
        }

        .app-promo {
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--primary-blue);
            font-weight: 500;
        }

        /* Main Navbar */
        .navbar {
            background-color: white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            padding: 10px 20px;
        }

        .navbar-brand img {
            height: 40px;
        }

        .search-bar {
            flex-grow: 1;
            max-width: 500px;
            margin: 0 auto;
            position: relative;
        }

        .search-bar input {
            border-radius: 20px;
            border: 1px solid #ddd;
            padding: 10px 45px 10px 20px;
            font-size: 14px;
            width: 100%;
            background-color: #f8f9fa;
        }

        .search-bar button {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
        }

        .nav-icons {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #666;
            text-decoration: none;
            font-size: 12px;
            min-width: 50px;
        }

        .nav-icon i {
            font-size: 20px;
            margin-bottom: 3px;
        }

        .nav-icon:hover {
            color: var(--primary-blue);
            text-decoration: none;
        }

        /* Main Menu Horizontal */
        .main-menu {
            background-color: white;
            border-bottom: 1px solid #e0e0e0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .menu-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .menu-category {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 15px 0;
            font-weight: 500;
            color: var(--dark-gray);
            text-decoration: none;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .menu-category:hover {
            color: var(--primary-blue);
            border-bottom-color: var(--primary-blue);
        }

        .menu-category.active {
            color: var(--primary-blue);
            border-bottom-color: var(--primary-blue);
        }

        .menu-category i {
            font-size: 14px;
            margin-left: 5px;
        }

        /* Checkout Hero Section */
        .checkout-hero {
            background-color: var(--primary-blue);
            color: white;
            padding: 40px 0;
            text-align: center;
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

        /* Checkout Container */
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 15px;
        }

        /* Checkout Layout */
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

        .step.completed .step-circle i {
            display: block;
        }

        .step.completed .step-circle span {
            display: none;
        }

        .step-circle i {
            display: none;
        }

        .step-label {
            font-size: 12px;
            color: #666;
            font-weight: 500;
        }

        .step.active .step-label {
            color: var(--primary-blue);
            font-weight: 600;
        }

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
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-gray);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-blue);
            outline: none;
            box-shadow: 0 0 0 2px rgba(26, 75, 140, 0.2);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        /* Payment Methods */
        .payment-methods {
            display: grid;
            gap: 15px;
        }

        .payment-method {
            position: relative;
        }

        .payment-method input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .payment-label {
            display: block;
            padding: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .payment-label:hover {
            border-color: #ccc;
            background-color: #f9f9f9;
        }

        .payment-method input[type="radio"]:checked + .payment-label {
            border-color: var(--primary-blue);
            background-color: #f0f7ff;
        }

        .payment-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .payment-icon {
            font-size: 24px;
            color: var(--primary-blue);
            min-width: 40px;
        }

        .payment-details h4 {
            margin: 0 0 5px 0;
            color: var(--dark-gray);
        }

        .payment-details p {
            margin: 0;
            color: #666;
            font-size: 13px;
        }

        .payment-banks {
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
            margin-top: 10px;
            display: none;
        }

        .bank-option {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .bank-option:hover {
            background-color: white;
            border-color: #ccc;
        }

        .bank-option input {
            margin-right: 10px;
        }

        .bank-logo {
            width: 40px;
            height: 40px;
            object-fit: contain;
            margin-right: 15px;
        }

        .bank-info h5 {
            margin: 0;
            font-size: 14px;
        }

        .bank-info p {
            margin: 0;
            font-size: 12px;
            color: #666;
        }

        .qris-code {
            text-align: center;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            margin-top: 10px;
            display: none;
        }

        .qris-image {
            max-width: 200px;
            margin: 0 auto 15px;
        }

        .qris-image img {
            width: 100%;
            height: auto;
        }

        .qris-info {
            font-size: 12px;
            color: #666;
        }

        /* Order Summary */
        .order-summary {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .order-summary h2 {
            color: var(--primary-blue);
            font-size: 1.3rem;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .product-list {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
        }

        .product-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .product-item:last-child {
            border-bottom: none;
        }

        .product-img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
            margin-right: 15px;
        }

        .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info {
            flex: 1;
        }

        .product-name {
            font-weight: 500;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: #666;
        }

        .price-summary {
            margin: 20px 0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
        }

        .summary-label {
            color: #666;
        }

        .summary-value {
            font-weight: 500;
            color: var(--dark-gray);
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            font-size: 1.2rem;
        }

        .total-label {
            font-weight: 700;
            color: var(--dark-gray);
        }

        .total-value {
            font-weight: 700;
            color: var(--primary-blue);
        }

        /* Buttons */
        .btn-back {
            background-color: #f8f9fa;
            color: #666;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 12px 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-back:hover {
            background-color: #e9ecef;
            color: #333;
            text-decoration: none;
        }

        .btn-submit {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 15px 30px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 16px;
            width: 100%;
        }

        .btn-submit:hover {
            background-color: #153a6e;
        }

        .btn-submit:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .terms-agreement {
            margin-top: 20px;
            font-size: 13px;
            color: #666;
        }

        .terms-agreement a {
            color: var(--primary-blue);
            text-decoration: none;
        }

        .terms-agreement a:hover {
            text-decoration: underline;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
            border: 1px solid transparent;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        /* Footer */
        .footer {
            background-color: #1a1a1a;
            color: white;
            padding: 60px 0 30px;
            margin-top: 60px;
        }

        .footer-logo {
            height: 40px;
            margin-bottom: 20px;
        }

        .footer h5 {
            color: white;
            font-weight: 600;
            margin-bottom: 25px;
            font-size: 1.1rem;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #aaa;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: white;
        }

        .social-icons a {
            display: inline-block;
            width: 36px;
            height: 36px;
            background: #333;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 36px;
            margin-right: 10px;
            transition: background 0.3s;
        }

        .social-icons a:hover {
            background: var(--primary-blue);
        }

        .copyright {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #333;
            color: #aaa;
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .checkout-layout {
                grid-template-columns: 1fr;
            }
            
            .order-summary {
                position: static;
                margin-top: 30px;
            }
        }

        @media (max-width: 768px) {
            .top-bar {
                display: none;
            }
            
            .main-menu {
                display: none;
            }
            
            .checkout-hero h1 {
                font-size: 1.8rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .checkout-steps {
                flex-direction: column;
                gap: 20px;
            }
            
            .step {
                flex-direction: row;
                text-align: left;
                gap: 15px;
            }
            
            .step-circle {
                margin-bottom: 0;
            }
        }

        @media (max-width: 576px) {
            .checkout-container {
                padding: 30px 15px;
            }
            
            .checkout-section,
            .order-summary {
                padding: 20px;
            }
            
            .payment-info {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>

    <!-- Top Bar -->
    <div class="top-bar">
        <div class="top-bar-content">
            <div class="top-bar-links">
                <a href="aboutus.php">Tentang Megatek</a>
                <a href="seller_center.php">Pusat Edukasi Seller</a>
                <a href="promo.php">Promo</a>
                <a href="support.php">Megatek Care</a>
            </div>
            <a href="#" class="app-promo">
                <i class="fas fa-mobile-alt"></i>
                <span>Gratis Ongkir + Banyak Promo belanja di aplikasi ></span>
            </a>
        </div>
    </div>

    <!-- Main Navbar -->
    <nav class="navbar d-flex align-items-center">
        <a class="navbar-brand mx-2" href="index.php">
            <img src="gambar/LOGO.png" alt="Megatek Logo">
        </a>

        <div class="search-bar">
            <input type="text" class="form-control" placeholder="Cari produk, kategori, atau brand">
            <button type="button">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <div class="nav-icons">
            <a href="keranjang.php" class="nav-icon">
                <div style="position: relative;">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-badge"><?php echo $total_items; ?></span>
                </div>
                <span>Cart</span>
            </a>
            
            <div id="userSection">
                <?php if (isLoggedIn()): ?>
                    <div class="user-dropdown">
                        <a href="javascript:void(0);" class="nav-icon" id="userDropdown">
                            <i class="fas fa-user"></i>
                            <span><?php echo htmlspecialchars($user_data['first_name'] ?? 'Akun'); ?></span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <a href="seller_center.php" class="nav-icon">
                <i class="fas fa-store"></i>
                <span>Mulai Berjualan</span>
            </a>
        </div>
    </nav>

    <!-- Main Menu Horizontal -->
    <div class="main-menu">
        <div class="menu-container">
            <a href="produk.php" class="menu-category">
                <span>Semua Kategori</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <a href="produk.php?category=FBR Burner" class="menu-category">FBR Burner</a>
            <a href="produk.php?category=Boiler" class="menu-category">Boiler</a>
            <a href="produk.php?category=Valve & Instrumentation" class="menu-category">Valve & Instrumentation</a>
            <a href="produk.php?category=Sparepart" class="menu-category">Spare Part</a>
            <a href="produk.php?featured=1" class="menu-category">Featured</a>
            <a href="produk.php?popular=1" class="menu-category">Popular</a>
            <a href="promo.php" class="menu-category">Promo</a>
        </div>
    </div>

    <!-- Checkout Hero Section -->
    <section class="checkout-hero">
        <div class="container">
            <h1>Checkout Pembayaran</h1>
            <p>Selesaikan pembayaran untuk menyelesaikan pesanan Anda</p>
        </div>
    </section>

    <!-- Checkout Container -->
    <div class="checkout-container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Checkout Steps -->
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
            <!-- Left Column: Checkout Form -->
            <div class="checkout-form">
                <form method="POST" id="checkoutForm">
                    <!-- Shipping Address Section -->
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

                    <!-- Payment Method Section -->
                    <div class="checkout-section">
                        <div class="section-header">
                            <h2><i class="fas fa-credit-card me-2"></i>Metode Pembayaran</h2>
                        </div>
                        
                        <div class="payment-methods">
                            <!-- Bank Transfer -->
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
                                    <div class="bank-option">
                                        <input type="radio" id="bank_bca" name="bank_option" value="bca">
                                        <img src="https://upload.wikimedia.org/wikipedia/id/thumb/5/5c/Bank_Central_Asia.svg/2560px-Bank_Central_Asia.svg.png" alt="BCA" class="bank-logo">
                                        <div class="bank-info">
                                            <h5>Bank Central Asia (BCA)</h5>
                                            <p>1234567890 - Megatek Industrial Persada</p>
                                        </div>
                                    </div>
                                    <div class="bank-option">
                                        <input type="radio" id="bank_mandiri" name="bank_option" value="mandiri">
                                        <img src="https://upload.wikimedia.org/wikipedia/id/thumb/a/ad/Bank_Mandiri_logo_2016.svg/2560px-Bank_Mandiri_logo_2016.svg.png" alt="Mandiri" class="bank-logo">
                                        <div class="bank-info">
                                            <h5>Bank Mandiri</h5>
                                            <p>0987654321 - Megatek Industrial Persada</p>
                                        </div>
                                    </div>
                                    <div class="bank-option">
                                        <input type="radio" id="bank_bri" name="bank_option" value="bri">
                                        <img src="https://upload.wikimedia.org/wikipedia/id/thumb/5/5c/BRI_2020.svg/2560px-BRI_2020.svg.png" alt="BRI" class="bank-logo">
                                        <div class="bank-info">
                                            <h5>Bank Rakyat Indonesia (BRI)</h5>
                                            <p>1122334455 - Megatek Industrial Persada</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- QRIS -->
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
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=MEGATEK-ORDER-<?php echo time(); ?>" alt="QR Code">
                                    </div>
                                    <p class="qris-info">Scan QR Code di atas untuk membayar</p>
                                </div>
                            </div>

                            <!-- COD -->
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
                        
                        <div class="form-group">
                            <label for="notes">Catatan Pesanan (Opsional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Catatan tambahan untuk pesanan Anda..."></textarea>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div style="display: flex; justify-content: space-between; margin-top: 30px;">
                        <a href="keranjang.php" class="btn-back">
                            <i class="fas fa-arrow-left"></i>Kembali ke Keranjang
                        </a>
                        <button type="submit" class="btn-submit" id="submitOrder">
                            <i class="fas fa-lock me-2"></i>Buat Pesanan & Bayar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Column: Order Summary -->
            <div class="order-summary">
                <h2>Ringkasan Pesanan</h2>
                
                <div class="product-list">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="product-item">
                            <div class="product-img">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                            <div class="product-info">
                                <div class="product-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="product-meta">
                                    <span><?php echo $item['quantity']; ?> x Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></span>
                                    <span>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></span>
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
                    <p>Dengan mengeklik "Buat Pesanan & Bayar", Anda menyetujui <a href="#">Syarat & Ketentuan</a> dan <a href="#">Kebijakan Privasi</a> Megatek Industrial Persada.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <img src="gambar/LOGO-white.png" alt="Megatek Logo" class="footer-logo">
                    <p>PT. Megatek Industrial Persada - Your trusted partner for industrial solutions since 2010.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Products</h5>
                    <ul class="footer-links">
                        <li><a href="produk.php?category=FBR Burner">Burner Series</a></li>
                        <li><a href="produk.php?category=Boiler">Boiler Series</a></li>
                        <li><a href="produk.php?category=Valve & Instrumentation">Valve & Instrumentation</a></li>
                        <li><a href="produk.php?category=Sparepart">Spare Parts</a></li>
                        <li><a href="produk.php">All Products</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Information</h5>
                    <ul class="footer-links">
                        <li><a href="aboutus.php">About Us</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Terms of Use</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Contact Info</h5>
                    <p><i class="fas fa-map-marker-alt me-2"></i> Surabaya, Indonesia</p>
                    <p><i class="fas fa-phone me-2"></i> +62 31 1234 5678</p>
                    <p><i class="fas fa-envelope me-2"></i> info@megatek.co.id</p>
                </div>
            </div>
            <div class="copyright">
                © Copyright 2023 PT. Megatek Industrial Persada. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
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
            // If user has address data, auto-fill shipping fields
            const userAddress = "<?php echo $user_data['address'] ?? ''; ?>";
            if (userAddress) {
                document.getElementById('shipping_address').value = userAddress;
            }
            
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Character counter for notes
        const notesTextarea = document.getElementById('notes');
        notesTextarea.addEventListener('input', function() {
            const maxLength = 500;
            const currentLength = this.value.length;
            
            if (currentLength > maxLength) {
                this.value = this.value.substring(0, maxLength);
            }
        });

        // Smooth scroll to sections
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId !== '#') {
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 100,
                            behavior: 'smooth'
                        });
                    }
                }
            });
        });
    </script>
</body>
</html>