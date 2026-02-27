<?php
require_once 'config/database.php';
require_once 'config/check_login.php';

if (!isLoggedIn()) {
    header('Location: index.php?login_required=1');
    exit();
}

$user_id = $_SESSION['user_id'];

// Menghitung jumlah item di keranjang untuk Header
$cart_count = 0;
$cart_count_query = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE user_id = $user_id");
if ($cart_count_query) {
    $cart_count = $cart_count_query->fetch_assoc()['total'] ?? 0;
}

// Handle actions: update, remove, clear
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        // Update quantities
        $updated = false;
        foreach ($_POST['quantity'] as $cart_id => $quantity) {
            $quantity = intval($quantity);
            if ($quantity < 1) {
                // Remove item if quantity is 0
                $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ii", $cart_id, $user_id);
                $stmt->execute();
                $updated = true;
            } else {
                // Update quantity
                $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
                $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
                $stmt->execute();
                $updated = true;
            }
        }
        
        if ($updated) {
            $message = 'Keranjang berhasil diperbarui';
            $message_type = 'success';
            // Update cart count after modification
            $cart_count_query = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE user_id = $user_id");
            $cart_count = $cart_count_query->fetch_assoc()['total'] ?? 0;
        }
    } elseif (isset($_POST['remove_item'])) {
        // Remove single item
        $cart_id = intval($_POST['cart_id']);
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cart_id, $user_id);
        
        if ($stmt->execute()) {
            $message = 'Produk berhasil dihapus dari keranjang';
            $message_type = 'success';
            // Update cart count after modification
            $cart_count_query = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE user_id = $user_id");
            $cart_count = $cart_count_query->fetch_assoc()['total'] ?? 0;
        } else {
            $message = 'Gagal menghapus produk';
            $message_type = 'error';
        }
    } elseif (isset($_POST['clear_cart'])) {
        // Clear entire cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $message = 'Keranjang berhasil dikosongkan';
            $message_type = 'success';
            $cart_count = 0;
        } else {
            $message = 'Gagal mengosongkan keranjang';
            $message_type = 'error';
        }
    }
}

// Fetch cart items with product details
$cart_query = "
    SELECT 
        c.id as cart_id,
        c.quantity,
        c.added_at,
        p.id as product_id,
        p.name,
        p.description,
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

// Calculate totals
$subtotal = 0;
$total_items = 0;
foreach ($cart_items as $item) {
    $item_total = $item['price'] * $item['quantity'];
    $subtotal += $item_total;
    $total_items += $item['quantity'];
}

$tax = $subtotal * 0.11; // 11% tax
$shipping = $total_items > 0 ? 25000 : 0; // Shipping cost
$grand_total = $subtotal + $tax + $shipping;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Hardjadinata Karya Utama</title>
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

        /* ===== CART SPECIFIC STYLES ===== */
        .cart-hero {
            background-color: var(--primary-blue);
            color: white;
            padding: 40px 0;
            text-align: center;
            border-bottom: 5px solid var(--primary-red);
        }

        .cart-hero h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .cart-hero p {
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
        }

        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 15px;
        }

        .cart-layout {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }

        .cart-items-section {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .cart-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .cart-section-header h2 {
            color: var(--primary-blue);
            font-size: 1.5rem;
            margin: 0;
            font-weight: 700;
        }

        .btn-clear {
            background-color: #f8f9fa;
            color: #666;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 8px 15px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-clear:hover {
            background-color: var(--primary-red);
            color: white;
            border-color: var(--primary-red);
        }

        /* Cart Table */
        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cart-table th {
            padding: 15px;
            text-align: left;
            color: var(--dark-gray);
            font-weight: 600;
            border-bottom: 2px solid #eee;
            background-color: #f8f9fa;
        }

        .cart-table td {
            padding: 20px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #eee;
        }

        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .product-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
            border: 1px solid #eee;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .product-name a {
            color: var(--dark-gray);
            text-decoration: none;
        }

        .product-name a:hover {
            color: var(--primary-blue);
        }

        .product-category {
            font-size: 12px;
            color: #666;
            background-color: #f0f7ff;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .qty-input {
            width: 70px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
            font-family: 'Poppins', sans-serif;
        }

        .btn-qty {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background-color: white;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-qty:hover {
            background-color: var(--primary-blue);
            color: white;
            border-color: var(--primary-blue);
        }

        .price { font-weight: 600; color: var(--primary-blue); }
        .item-total { font-weight: 700; color: var(--dark-gray); }

        .btn-remove {
            background: none;
            border: none;
            color: var(--primary-red);
            cursor: pointer;
            padding: 5px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .btn-remove:hover {
            background-color: rgba(227, 6, 19, 0.1);
        }

        /* Cart Summary */
        .cart-summary {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            height: fit-content;
            position: sticky;
            top: 150px; /* Disesuaikan dengan sticky header */
        }

        .cart-summary h2 {
            color: var(--primary-blue);
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
        }

        .summary-label { color: #666; }
        .summary-value { font-weight: 500; color: var(--dark-gray); }

        .summary-total {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            font-size: 1.2rem;
        }

        .total-label { font-weight: 700; color: var(--dark-gray); }
        .total-value { font-weight: 700; color: var(--primary-red); }

        .btn-checkout {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 15px;
            width: 100%;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 16px;
            margin-bottom: 15px;
            margin-top: 20px;
            text-align: center;
            display: block;
            text-decoration: none;
        }

        .btn-checkout:hover {
            background-color: #002266;
            color: white;
        }

        .btn-update {
            background-color: transparent;
            color: var(--primary-blue);
            border: 1px solid var(--primary-blue);
            border-radius: 8px;
            padding: 12px;
            width: 100%;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }

        .btn-update:hover {
            background-color: var(--primary-blue);
            color: white;
        }

        .shipping-notice {
            background-color: #f0f7ff;
            border-left: 4px solid var(--primary-blue);
            padding: 15px;
            margin-top: 25px;
            border-radius: 5px;
        }

        .shipping-notice h4 { color: var(--primary-blue); margin-bottom: 10px; font-size: 1rem; font-weight: 600;}
        .shipping-notice p { color: #666; font-size: 0.9rem; margin-bottom: 5px; }

        /* Empty Cart */
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .empty-cart i { font-size: 4rem; color: #ddd; margin-bottom: 20px; }
        .empty-cart h3 { color: var(--primary-blue); margin-bottom: 15px; font-weight: 700;}
        .empty-cart p { color: #999; margin-bottom: 30px; }

        .btn-shopping {
            background-color: var(--primary-blue);
            color: white;
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
            transition: background-color 0.3s;
        }

        .btn-shopping:hover { background-color: #002266; color: white; }

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

        .footer h5 {
            color: white;
            font-weight: 600;
            margin-bottom: 25px;
            font-size: 1.1rem;
        }

        .footer-links { list-style: none; padding: 0; }
        .footer-links li { margin-bottom: 10px; }
        .footer-links a { color: #ccc; text-decoration: none; transition: color 0.3s; }
        .footer-links a:hover { color: white; }

        .social-icons a {
            display: inline-block;
            width: 36px;
            height: 36px;
            background: rgba(255,255,255,0.1);
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 36px;
            margin-right: 10px;
            transition: background 0.3s;
        }

        .social-icons a:hover { background: var(--primary-red); }

        .copyright {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: #aaa;
            font-size: 14px;
        }

        /* ===== MODALS (Sama dengan beranda.php) ===== */
        .login-modal, .register-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 34, 85, 0.7);
            z-index: 10000;
            justify-content: center;
            align-items: center;
        }

        .login-content, .register-content {
            background-color: white;
            border-radius: 10px;
            width: 90%;
            max-width: 450px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .btn-login, .btn-register {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px;
            width: 100%;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-login:hover, .btn-register:hover { background-color: #002266; }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .hku-header-actions { display: none; }
            .cart-layout { grid-template-columns: 1fr; }
            .cart-summary { position: static; }
        }
        @media (max-width: 768px) {
            .cart-table { display: block; overflow-x: auto; }
            .cart-table thead { display: none; }
            .cart-table tbody, .cart-table tr, .cart-table td { display: block; width: 100%; }
            .cart-table tr { margin-bottom: 20px; border: 1px solid #eee; border-radius: 8px; padding: 15px; }
            .cart-table td { border: none; padding: 10px 0; position: relative; padding-left: 50%; text-align: right; }
            .cart-table td:before { 
                content: attr(data-label); position: absolute; left: 0; width: 45%; 
                text-align: left; font-weight: 600; color: var(--dark-gray); 
            }
            .product-info { flex-direction: column; text-align: center; }
            .quantity-control { justify-content: flex-end; }
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
                        <?php else: ?>
                            <a href="javascript:void(0);" class="nav-icon" id="userLogin">
                                <i class="fas fa-user"></i>
                                <span class="mt-1">Masuk/Daftar</span>
                            </a>
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

    <section class="cart-hero">
        <div class="container">
            <h1>Keranjang Belanja</h1>
            <p>Periksa dan kelola produk yang telah Anda tambahkan ke keranjang belanja Anda.</p>
        </div>
    </section>

    <div class="cart-container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Keranjang Anda Kosong</h3>
                <p>Tambahkan produk ke keranjang untuk melanjutkan pembelian di Hardjadinata Karya Utama</p>
                <a href="produk.php" class="btn-shopping">
                    <i class="fas fa-shopping-bag me-2"></i>Mulai Belanja
                </a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <div class="cart-items-section">
                    <div class="cart-section-header">
                        <h2>Produk dalam Keranjang (<?php echo $total_items; ?>)</h2>
                        <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan keranjang?')">
                            <button type="submit" name="clear_cart" class="btn-clear">
                                <i class="fas fa-trash"></i> Kosongkan Keranjang
                            </button>
                        </form>
                    </div>
                    
                    <form method="POST" id="cartForm">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Kuantitas</th>
                                    <th>Subtotal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <?php 
                                    $item_price = $item['price'];
                                    $item_subtotal = $item_price * $item['quantity'];
                                    ?>
                                    <tr class="cart-item">
                                        <td data-label="Produk">
                                            <div class="product-info">
                                                <div class="product-image">
                                                    <img src="uploads/<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                                </div>
                                                <div class="product-details">
                                                    <div class="product-name">
                                                        <a href="detailproduk.php?id=<?php echo $item['product_id']; ?>">
                                                            <?php echo htmlspecialchars($item['name']); ?>
                                                        </a>
                                                    </div>
                                                    <div class="product-category">
                                                        <?php echo htmlspecialchars($item['category']); ?>
                                                    </div>
                                                    <?php if ($item['stock'] < $item['quantity']): ?>
                                                        <div style="color: var(--primary-red); font-size: 12px; margin-top: 5px;">
                                                            <i class="fas fa-exclamation-triangle"></i> Stok tersedia: <?php echo $item['stock']; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Harga" class="price">
                                            Rp <?php echo number_format($item_price, 0, ',', '.'); ?>
                                        </td>
                                        <td data-label="Kuantitas">
                                            <div class="quantity-control">
                                                <button type="button" class="btn-qty" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, -1)">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number" 
                                                       name="quantity[<?php echo $item['cart_id']; ?>]" 
                                                       class="qty-input" 
                                                       value="<?php echo $item['quantity']; ?>" 
                                                       min="1" 
                                                       max="<?php echo $item['stock']; ?>"
                                                       onchange="validateQuantity(this)">
                                                <button type="button" class="btn-qty" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, 1)">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td data-label="Subtotal" class="item-total">
                                            Rp <?php echo number_format($item_subtotal, 0, ',', '.'); ?>
                                        </td>
                                        <td data-label="Aksi">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                                <button type="submit" name="remove_item" class="btn-remove" 
                                                        onclick="return confirm('Hapus produk dari keranjang?')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <div style="margin-top: 30px; text-align: right;">
                            <button type="submit" name="update_cart" class="btn-update" style="width: auto; padding: 12px 25px;">
                                <i class="fas fa-sync-alt me-2"></i>Perbarui Keranjang
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="cart-summary">
                    <h2>Ringkasan Belanja</h2>
                    
                    <div class="summary-item">
                        <span class="summary-label">Subtotal</span>
                        <span class="summary-value">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="summary-label">Pajak (11%)</span>
                        <span class="summary-value">Rp <?php echo number_format($tax, 0, ',', '.'); ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="summary-label">Biaya Pengiriman</span>
                        <span class="summary-value">Rp <?php echo number_format($shipping, 0, ',', '.'); ?></span>
                    </div>
                    
                    <div class="summary-total">
                        <span class="total-label">Total</span>
                        <span class="total-value">Rp <?php echo number_format($grand_total, 0, ',', '.'); ?></span>
                    </div>
                    
                    <div class="summary-actions">
                        <a href="checkout.php" class="btn-checkout" style="text-align: center; display: block; text-decoration: none;">
    <i class="fas fa-lock me-2"></i>Lanjut Pembayaran
</a>
                        
                        <a href="produk.php" class="btn-update" style="text-align: center; display: block; text-decoration: none;">
                            <i class="fas fa-arrow-left me-2"></i>Lanjut Belanja
                        </a>
                    </div>
                    
                    <div class="shipping-notice">
                        <h4><i class="fas fa-shipping-fast me-2"></i>Informasi Pengiriman</h4>
                        <p>• Estimasi pengiriman: 1-3 hari kerja</p>
                        <p>• Gratis ongkir untuk pembelian di atas Rp 2.000.000</p>
                        <p>• Dukungan kurir: JNE, TIKI, J&T, Sicepat</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="login-modal" id="loginModal">
        <div class="login-content">
            <button class="close-btn" id="closeLogin" style="position: absolute; top: 15px; right: 15px; border: none; background: transparent; font-size: 20px;">&times;</button>
            <div class="text-center mb-4">
                <h3 style="color: var(--primary-blue); font-weight: 700;">HKU</h3>
                <p class="text-muted">Surabaya</p>
            </div>
            
            <h5 class="mb-4 text-center">SIGN IN</h5>
            
            <form id="loginForm" method="POST" action="proses_login.php">
                <div class="mb-3">
                    <label class="form-label fw-bold">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-login">LOGIN</button>
                <div class="d-flex justify-content-between mt-3" style="font-size: 14px;">
                    <a id="showRegister" style="color: var(--primary-blue); cursor: pointer; text-decoration:none;">Register Now</a>
                </div>
            </form>
        </div>
    </div>

    <div class="register-modal" id="registerModal">
        <div class="register-content">
            <button class="close-btn" id="closeRegister" style="position: absolute; top: 15px; right: 15px; border: none; background: transparent; font-size: 20px;">&times;</button>
            <div class="text-center mb-4">
                <h3 style="color: var(--primary-blue); font-weight: 700;">HKU</h3>
                <p class="text-muted">Surabaya</p>
            </div>
            <h5 class="mb-4 text-center">CREATE ACCOUNT</h5>
            <form id="registerForm" method="POST" action="proses_register.php">
                <div class="d-flex gap-3 mb-3">
                    <div class="w-50">
                        <label class="form-label fw-bold">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="w-50">
                        <label class="form-label fw-bold">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" class="form-control" id="reg_email" name="email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Phone</label>
                    <input type="tel" class="form-control" id="phone_number" name="phone_number" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Password</label>
                    <input type="password" class="form-control" id="reg_password" name="password" required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn-register">REGISTER</button>
                <div class="mt-3 text-center" style="font-size: 14px;">
                    <a id="showLogin" style="color: var(--primary-blue); cursor: pointer; text-decoration:none;">Already have an account? Sign In</a>
                </div>
            </form>
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
        // Update quantity with buttons
        function updateQuantity(cartId, change) {
            const input = document.querySelector(`input[name="quantity[${cartId}]"]`);
            let newValue = parseInt(input.value) + change;
            
            // Get max value from input attribute
            const maxValue = parseInt(input.getAttribute('max'));
            if (newValue < 1) newValue = 1;
            if (newValue > maxValue) newValue = maxValue;
            
            input.value = newValue;
        }

        // Validate quantity input
        function validateQuantity(input) {
            const maxValue = parseInt(input.getAttribute('max'));
            let value = parseInt(input.value);
            
            if (isNaN(value) || value < 1) {
                input.value = 1;
            } else if (value > maxValue) {
                input.value = maxValue;
                alert('Stok tidak mencukupi. Stok tersedia: ' + maxValue);
            }
        }

        // Proceed to checkout
        function proceedToCheckout(e) {
            e.preventDefault();
            // Simpan cart terlebih dahulu sebelum redirect
            document.getElementById('cartForm').submit();
            setTimeout(function() {
                window.location.href = 'checkout.php';
            }, 500);
        }

        // Modal & Auth Logic (Diadopsi dari beranda.php)
        const userLogin = document.getElementById('userLogin');
        const loginModal = document.getElementById('loginModal');
        const registerModal = document.getElementById('registerModal');
        const closeLogin = document.getElementById('closeLogin');
        const closeRegister = document.getElementById('closeRegister');
        
        if (userLogin) userLogin.addEventListener('click', () => loginModal.style.display = 'flex');
        if (closeLogin) closeLogin.addEventListener('click', () => loginModal.style.display = 'none');
        if (closeRegister) closeRegister.addEventListener('click', () => registerModal.style.display = 'none');
        
        const showRegBtn = document.getElementById('showRegister');
        if (showRegBtn) {
            showRegBtn.addEventListener('click', () => {
                loginModal.style.display = 'none';
                registerModal.style.display = 'flex';
            });
        }

        const showLoginBtn = document.getElementById('showLogin');
        if (showLoginBtn) {
            showLoginBtn.addEventListener('click', () => {
                registerModal.style.display = 'none';
                loginModal.style.display = 'flex';
            });
        }
        
        window.addEventListener('click', (e) => {
            if (e.target === loginModal) loginModal.style.display = 'none';
            if (e.target === registerModal) registerModal.style.display = 'none';
        });

        // Dropdown menu
        const userDropdown = document.getElementById('userDropdown');
        const userDropdownMenu = document.getElementById('userDropdownMenu');
        if (userDropdown) {
            userDropdown.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdownMenu.classList.toggle('show');
            });
            document.addEventListener('click', () => userDropdownMenu.classList.remove('show'));
            userDropdownMenu.addEventListener('click', (e) => e.stopPropagation());
        }

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