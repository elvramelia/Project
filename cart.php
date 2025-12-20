<?php
require_once 'config/database.php';
require_once 'config/check_login.php';

if (!isLoggedIn()) {
    header('Location: index.php?login_required=1');
    exit();
}

$user_id = $_SESSION['user_id'];

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
        }
    } elseif (isset($_POST['remove_item'])) {
        // Remove single item
        $cart_id = intval($_POST['cart_id']);
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cart_id, $user_id);
        
        if ($stmt->execute()) {
            $message = 'Produk berhasil dihapus dari keranjang';
            $message_type = 'success';
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
    <title>Keranjang Belanja - Megatek Industrial Persada</title>
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

        /* User dropdown */
        .user-dropdown {
            position: relative;
            display: inline-block;
        }

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

        .dropdown-item {
            display: block;
            padding: 10px 15px;
            color: var(--dark-gray);
            text-decoration: none;
            transition: background 0.3s;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: var(--primary-blue);
        }

        .dropdown-divider {
            border-top: 1px solid #eee;
            margin: 5px 0;
        }

        /* Cart Badge */
        .cart-badge {
            position: absolute;
            top: -5px;
            right: 5px;
            background-color: #ff4444;
            color: white;
            font-size: 10px;
            min-width: 16px;
            height: 16px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
        }

        /* Cart Hero Section */
        .cart-hero {
            background-color: var(--primary-blue);
            color: white;
            padding: 40px 0;
            text-align: center;
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

        /* Cart Container */
        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 15px;
        }

        /* Messages */
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

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
        }

        /* Cart Layout */
        .cart-layout {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }

        /* Cart Items Section */
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
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-clear:hover {
            background-color: #ff4444;
            color: white;
            border-color: #ff4444;
            text-decoration: none;
        }

        /* Empty Cart */
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-cart i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-cart h3 {
            color: #666;
            margin-bottom: 15px;
        }

        .empty-cart p {
            color: #999;
            margin-bottom: 30px;
        }

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

        .btn-shopping:hover {
            background-color: #153a6e;
            color: white;
            text-decoration: none;
        }

        /* Cart Table */
        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cart-table thead {
            background-color: #f8f9fa;
        }

        .cart-table th {
            padding: 15px;
            text-align: left;
            color: var(--dark-gray);
            font-weight: 600;
            border-bottom: 2px solid #eee;
        }

        .cart-table td {
            padding: 20px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #eee;
        }

        .cart-item {
            transition: background-color 0.3s;
        }

        .cart-item:hover {
            background-color: #f9f9f9;
        }

        /* Product Info */
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
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-details {
            flex: 1;
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

        /* Quantity Control */
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

        /* Price */
        .price {
            font-weight: 600;
            color: var(--primary-blue);
        }

        .item-total {
            font-weight: 700;
            color: var(--dark-gray);
        }

        /* Remove Button */
        .btn-remove {
            background: none;
            border: none;
            color: #ff4444;
            cursor: pointer;
            padding: 5px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .btn-remove:hover {
            background-color: rgba(255, 68, 68, 0.1);
        }

        /* Cart Summary */
        .cart-summary {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .cart-summary h2 {
            color: var(--primary-blue);
            font-size: 1.5rem;
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

        .summary-actions {
            margin-top: 30px;
        }

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
        }

        .btn-checkout:hover {
            background-color: #153a6e;
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

        /* Shipping Notice */
        .shipping-notice {
            background-color: #f0f7ff;
            border-left: 4px solid var(--primary-blue);
            padding: 15px;
            margin-top: 25px;
            border-radius: 5px;
        }

        .shipping-notice h4 {
            color: var(--primary-blue);
            margin-bottom: 10px;
            font-size: 1rem;
        }

        .shipping-notice p {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        /* Modal Styles */
        .login-modal, .register-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .login-content, .register-content {
            background-color: white;
            border-radius: 10px;
            width: 90%;
            max-width: 450px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .login-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .login-header h3 {
            color: var(--primary-blue);
            font-weight: 600;
            margin-bottom: 5px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
        }

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
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-blue);
            outline: none;
            box-shadow: 0 0 0 2px rgba(26, 75, 140, 0.2);
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

        .btn-login:hover, .btn-register:hover {
            background-color: #153a6e;
        }

        .login-links, .register-links {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            font-size: 14px;
        }

        .login-links a, .register-links a {
            color: var(--primary-blue);
            text-decoration: none;
            cursor: pointer;
        }

        .login-links a:hover, .register-links a:hover {
            text-decoration: underline;
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #666;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
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

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .cart-layout {
                grid-template-columns: 1fr;
            }
            
            .cart-summary {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .top-bar {
                display: none;
            }
            
            .main-menu {
                display: none;
            }
            
            .search-bar {
                max-width: 200px;
            }
            
            .nav-icon span {
                display: none;
            }
            
            .nav-icon {
                min-width: auto;
            }
            
            .cart-table {
                display: block;
                overflow-x: auto;
            }
            
            .cart-table thead {
                display: none;
            }
            
            .cart-table tbody,
            .cart-table tr,
            .cart-table td {
                display: block;
                width: 100%;
            }
            
            .cart-table tr {
                margin-bottom: 20px;
                border: 1px solid #eee;
                border-radius: 8px;
                padding: 15px;
            }
            
            .cart-table td {
                border: none;
                padding: 10px 0;
                position: relative;
                padding-left: 50%;
            }
            
            .cart-table td:before {
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 45%;
                padding-right: 10px;
                font-weight: 600;
                color: var(--dark-gray);
            }
            
            .product-info {
                flex-direction: column;
                text-align: center;
            }
            
            .quantity-control {
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .nav-icons {
                gap: 10px;
            }
            
            .search-bar {
                max-width: 150px;
            }
            
            .navbar-brand img {
                height: 30px;
            }
            
            .cart-hero h1 {
                font-size: 1.8rem;
            }
            
            .cart-container {
                padding: 30px 15px;
            }
            
            .cart-items-section,
            .cart-summary {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- Top Bar -->
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
                    <span class="cart-badge" id="cartCount">
                        <?php echo $total_items; ?>
                    </span>
                </div>
                <span>Cart</span>
            </a>
            
            <div id="userSection">
                <?php if (isLoggedIn()): ?>
                    <!-- User sudah login -->
                    <div class="user-dropdown">
                        <a href="javascript:void(0);" class="nav-icon" id="userDropdown">
                            <i class="fas fa-user"></i>
                            <span>
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
                            <span class="dropdown-item-text">
                                <small>Logged in as:</small><br>
                                <strong><?php echo htmlspecialchars($_SESSION['user_email']); ?></strong>
                            </span>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a>
                            <a class="dropdown-item" href="orders.php"><i class="fas fa-shopping-bag me-2"></i>My Orders</a>
                            <a class="dropdown-item" href="wishlist.php"><i class="fas fa-heart me-2"></i>Wishlist</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- User belum login -->
                    <a href="javascript:void(0);" class="nav-icon" id="userLogin">
                        <i class="fas fa-user"></i>
                        <span>Masuk/Daftar</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Menu Horizontal -->
    <div class="main-menu">
        <div class="menu-container">
            <a href="beranda.php" class="menu-category <?php echo basename($_SERVER['PHP_SELF']) == 'produk.php' ? 'active' : ''; ?>">
                <span>Beranda</span>
            </a>
            <a href="tentangkami.php" class="menu-category">Tentang Kami</a>
            <a href="produk.php" class="menu-category">Produk</a>
            <a href="hubungikami.php" class="menu-category">Hubungi Kami</a>
            <a href="promo.php" class="menu-category">Promo</a>
        </div>
    </div>

    <!-- Cart Hero Section -->
    <section class="cart-hero">
        <div class="container">
            <h1>Keranjang Belanja</h1>
            <p>Periksa dan kelola produk yang telah Anda tambahkan ke keranjang belanja</p>
        </div>
    </section>

    <!-- Cart Container -->
    <div class="cart-container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($cart_items)): ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Keranjang Anda Kosong</h3>
                <p>Tambahkan produk ke keranjang untuk melanjutkan pembelian</p>
                <a href="produk.php" class="btn-shopping">
                    <i class="fas fa-shopping-bag me-2"></i>Mulai Belanja
                </a>
            </div>
        <?php else: ?>
            <!-- Cart Layout -->
            <div class="cart-layout">
                <!-- Cart Items -->
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
                                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                                </div>
                                                <div class="product-details">
                                                    <div class="product-name">
                                                        <a href="product_detail.php?id=<?php echo $item['product_id']; ?>">
                                                            <?php echo htmlspecialchars($item['name']); ?>
                                                        </a>
                                                    </div>
                                                    <div class="product-category">
                                                        <?php echo htmlspecialchars($item['category']); ?>
                                                    </div>
                                                    <?php if ($item['stock'] < $item['quantity']): ?>
                                                        <div style="color: #ff4444; font-size: 12px; margin-top: 5px;">
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
                            <button type="submit" name="update_cart" class="btn-update">
                                <i class="fas fa-sync-alt me-2"></i>Perbarui Keranjang
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Cart Summary -->
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
                        <a href="checkout.php" type="button" class="btn-checkout" onclick="proceedToCheckout()">
                            <i class="fas fa-lock me-2"></i>Lanjut ke Pembayaran
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

    <!-- Login Modal -->
    <div class="login-modal" id="loginModal">
        <div class="login-content">
            <button class="close-btn" id="closeLogin">&times;</button>
            <div class="login-header">
                <h3>Megatek Industrial Persada</h3>
                <p>Surabaya</p>
            </div>
            
            <h5 class="mb-4 text-center">SIGN IN TO YOUR ACCOUNT</h5>
            
            <form id="loginForm" method="POST">
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="example@gmail.com" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Type Password" required>
                </div>
                
                <button type="submit" class="btn-login">LOGIN</button>
                
                <div class="login-links">
                    <a id="showRegister">Register Now</a>
                    <a href="forgot_password.php">Forgot Password?</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="register-modal" id="registerModal">
        <div class="register-content">
            <button class="close-btn" id="closeRegister">&times;</button>
            <div class="login-header">
                <h3>Megatek Industrial Persada</h3>
                <p>Surabaya</p>
            </div>
            
            <h5 class="mb-4 text-center">CREATE NEW ACCOUNT</h5>
            
            <form id="registerForm" method="POST">
                <div class="name-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="reg_email">Email address</label>
                    <input type="email" class="form-control" id="reg_email" name="email" placeholder="example@gmail.com" required>
                </div>
                
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="tel" class="form-control" id="phone_number" name="phone_number" placeholder="Phone Number" required>
                </div>
                
                <div class="form-group">
                    <label for="reg_password">Password</label>
                    <input type="password" class="form-control" id="reg_password" name="password" placeholder="Create Password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                
                <button type="submit" class="btn-register">REGISTER</button>
                
                <div class="register-links">
                    <a id="showLogin">Already have an account? Sign In</a>
                </div>
            </form>
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
        // Update cart count badge on all pages
        function updateCartCount() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_cart_count.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            document.getElementById('cartCount').textContent = response.count;
                        }
                    } catch (e) {
                        console.error('Error updating cart count:', e);
                    }
                }
            };
            xhr.send();
        }

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
        function proceedToCheckout() {
            // First update cart to ensure quantities are correct
            document.getElementById('cartForm').submit();
            
            // After form submission, redirect to checkout
            setTimeout(function() {
                window.location.href = 'checkout.php';
            }, 500);
        }

        // Modal functionality
        const userLogin = document.getElementById('userLogin');
        const loginModal = document.getElementById('loginModal');
        const registerModal = document.getElementById('registerModal');
        const closeLogin = document.getElementById('closeLogin');
        const closeRegister = document.getElementById('closeRegister');
        const showRegister = document.getElementById('showRegister');
        const showLogin = document.getElementById('showLogin');
        
        if (userLogin) {
            userLogin.addEventListener('click', function() {
                loginModal.style.display = 'flex';
            });
        }
        
        closeLogin.addEventListener('click', function() {
            loginModal.style.display = 'none';
        });
        
        closeRegister.addEventListener('click', function() {
            registerModal.style.display = 'none';
        });
        
        if (showRegister) {
            showRegister.addEventListener('click', function() {
                loginModal.style.display = 'none';
                registerModal.style.display = 'flex';
            });
        }
        
        if (showLogin) {
            showLogin.addEventListener('click', function() {
                registerModal.style.display = 'none';
                loginModal.style.display = 'flex';
            });
        }
        
        window.addEventListener('click', function(event) {
            if (event.target === loginModal) {
                loginModal.style.display = 'none';
            }
            if (event.target === registerModal) {
                registerModal.style.display = 'none';
            }
        });

        // Search functionality
        const searchInput = document.querySelector('.search-bar input');
        const searchButton = document.querySelector('.search-bar button');
        
        searchButton.addEventListener('click', function() {
            const searchTerm = searchInput.value.trim();
            if (searchTerm !== '') {
                window.location.href = 'produk.php?search=' + encodeURIComponent(searchTerm);
            }
        });
        
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const searchTerm = this.value.trim();
                if (searchTerm !== '') {
                    window.location.href = 'produk.php?search=' + encodeURIComponent(searchTerm);
                }
            }
        });

        // Dropdown menu functionality for logged in user
        const userDropdown = document.getElementById('userDropdown');
        const userDropdownMenu = document.getElementById('userDropdownMenu');

        if (userDropdown) {
            userDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdownMenu.style.display = userDropdownMenu.style.display === 'block' ? 'none' : 'block';
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function() {
                if (userDropdownMenu) {
                    userDropdownMenu.style.display = 'none';
                }
            });
            
            // Prevent dropdown from closing when clicking inside
            if (userDropdownMenu) {
                userDropdownMenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        }

        // Update cart count every 30 seconds
        setInterval(updateCartCount, 30000);

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
            
            // Highlight Cart in menu
            const menuLinks = document.querySelectorAll('.menu-category');
            menuLinks.forEach(link => {
                if (link.textContent.includes('Cart') || link.getAttribute('href') === 'cart.php') {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>