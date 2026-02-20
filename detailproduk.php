<?php
require_once 'config/database.php';
require_once 'config/check_login.php';

// Get product ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: produk.php');
    exit();
}

$product_id = intval($_GET['id']);
$message = '';
$message_type = '';

// Fetch product details
$product_query = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($product_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product_result = $stmt->get_result();
$product = $product_result->fetch_assoc();

if (!$product) {
    header('Location: produk.php');
    exit();
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isLoggedIn()) {
        header('Location: beranda.php?login_required=1&redirect=detailproduk.php?id=' . $product_id);
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $quantity = intval($_POST['quantity']);
    
    if ($quantity < 1) {
        $quantity = 1;
    }
    
    // Check if product already in cart
    $check_cart_query = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($check_cart_query);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $cart_result = $stmt->get_result();
    
    if ($cart_result->num_rows > 0) {
        // Update existing cart item
        $cart_item = $cart_result->fetch_assoc();
        $new_quantity = $cart_item['quantity'] + $quantity;
        
        if ($new_quantity > $product['stock']) {
            $message = 'Stok tidak mencukupi. Stok tersisa: ' . $product['stock'];
            $message_type = 'error';
        } else {
            $update_query = "UPDATE cart SET quantity = ?, added_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ii", $new_quantity, $cart_item['id']);
            
            if ($stmt->execute()) {
                $message = 'Produk berhasil ditambahkan ke keranjang';
                $message_type = 'success';
            } else {
                $message = 'Gagal menambahkan produk ke keranjang';
                $message_type = 'error';
            }
        }
    } else {
        // Add new item to cart
        if ($quantity > $product['stock']) {
            $message = 'Stok tidak mencukupi. Stok tersisa: ' . $product['stock'];
            $message_type = 'error';
        } else {
            $insert_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iii", $user_id, $product_id, $quantity);
            
            if ($stmt->execute()) {
                $message = 'Produk berhasil ditambahkan ke keranjang';
                $message_type = 'success';
            } else {
                $message = 'Gagal menambahkan produk ke keranjang';
                $message_type = 'error';
            }
        }
    }
}

// Fetch related products (same category)
$related_query = "
    SELECT * FROM products 
    WHERE category = ? AND id != ? AND stock > 0 
    ORDER BY RAND() 
    LIMIT 4
";
$stmt = $conn->prepare($related_query);
$stmt->bind_param("si", $product['category'], $product_id);
$stmt->execute();
$related_result = $stmt->get_result();
$related_products = $related_result->fetch_all(MYSQLI_ASSOC);

// Fetch popular products
$popular_query = "
    SELECT * FROM products 
    WHERE popular = 1 AND id != ? AND stock > 0 
    ORDER BY RAND() 
    LIMIT 4
";
$stmt = $conn->prepare($popular_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$popular_result = $stmt->get_result();
$popular_products = $popular_result->fetch_all(MYSQLI_ASSOC);

// Format price
$formatted_price = number_format($product['price'], 0, ',', '.');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Hardjadinata Karya Utama</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    
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

        /* --- HEADER TEMA HKU --- */
        .hku-header-top {
            background-color: var(--primary-blue);
            color: white;
            padding: 15px 0;
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
            z-index: 10;
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

        .hku-nav-link:hover {
            color: var(--primary-red);
            background-color: #fcfcfc;
        }

        .hku-nav-link.active {
            color: var(--primary-red);
            border-bottom-color: var(--primary-red);
            background-color: #f9f9f9;
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

        .dropdown-menu.show {
            display: block;
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

        /* Breadcrumb */
        .breadcrumb {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
            font-size: 14px;
            background-color: transparent;
        }

        .breadcrumb a {
            color: #666;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            color: var(--primary-red);
            text-decoration: underline;
        }

        .breadcrumb span {
            color: var(--primary-red);
            font-weight: 600;
        }

        /* Product Detail Container */
        .product-detail-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px 40px;
        }

        /* Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
            border: 1px solid transparent;
        }
        .alert-success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .alert-info { background-color: #d1ecf1; color: #0c5460; border-color: #bee5eb; }

        /* Product Detail Layout */
        .product-detail-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 60px;
        }

        /* Product Gallery */
        .product-gallery {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid #eee;
        }

        .main-image {
            width: 100%;
            height: 450px;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 15px;
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .main-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .image-thumbnails {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 10px;
        }

        .thumbnail {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
            border: 2px solid #eee;
            cursor: pointer;
            transition: border-color 0.3s;
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .thumbnail:hover { border-color: var(--primary-blue); }
        .thumbnail.active { border-color: var(--primary-red); }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 5px;
        }

        /* Product Info */
        .product-info {
            background-color: white;
            border-radius: 10px;
            padding: 35px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid #eee;
        }

        .product-category {
            display: inline-block;
            background-color: #f0f4f8;
            color: var(--primary-blue);
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .product-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--dark-gray);
            margin-bottom: 15px;
            line-height: 1.3;
        }

        .product-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .rating { color: #ffc107; display: flex; align-items: center; gap: 5px; }
        .review-count { color: #666; font-size: 14px; margin-left: 5px; }

        .stock-status { display: flex; align-items: center; gap: 5px; font-size: 14px; font-weight: 600; }
        .stock-in { color: #28a745; }
        .stock-out { color: var(--primary-red); }

        .product-price { margin-bottom: 25px; }
        .current-price {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--primary-red);
        }

        .product-description {
            margin-bottom: 30px;
            line-height: 1.7;
            color: #555;
        }

        .product-features { margin-bottom: 30px; }
        .features-list { list-style: none; padding: 0; }
        .features-list li {
            padding: 8px 0;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #444;
            font-weight: 500;
        }
        .features-list i { color: var(--primary-red); width: 20px; }

        /* Order Form */
        .order-form { margin-top: 30px; }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .quantity-label { font-weight: 600; color: var(--dark-gray); }

        .qty-input-group {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }

        .qty-btn {
            width: 40px;
            height: 40px;
            background-color: #f8f9fa;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .qty-btn:hover { background-color: #e9ecef; }
        .qty-input {
            width: 60px;
            height: 40px;
            border: none;
            text-align: center;
            font-size: 16px;
            font-weight: 600;
        }
        .qty-input:focus { outline: none; }

        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn-add-cart {
            flex: 1;
            min-width: 200px;
            background-color: var(--primary-blue);
            color: white;
            border: 2px solid var(--primary-blue);
            border-radius: 8px;
            padding: 15px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-transform: uppercase;
        }
        .btn-add-cart:hover { background-color: #002266; border-color: #002266; color: white;}
        .btn-add-cart:disabled { background-color: #ccc; border-color: #ccc; cursor: not-allowed; }

        .btn-buy-now {
            flex: 1;
            min-width: 200px;
            background-color: var(--primary-red);
            color: white;
            border: 2px solid var(--primary-red);
            border-radius: 8px;
            padding: 15px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            text-transform: uppercase;
        }
        .btn-buy-now:hover { background-color: #c00510; border-color: #c00510; color: white; text-decoration: none; }

        .wishlist-btn {
            width: 55px;
            height: 55px;
            background-color: white;
            border: 2px solid #ddd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1.2rem;
            color: #666;
        }
        .wishlist-btn:hover { border-color: var(--primary-red); color: var(--primary-red); }
        .wishlist-btn.active { background-color: var(--primary-red); border-color: var(--primary-red); color: white; }

        /* Product Details Tabs */
        .product-tabs {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 60px;
            border: 1px solid #eee;
        }

        .tab-header {
            display: flex;
            border-bottom: 1px solid #eee;
            background-color: #f8f9fa;
        }

        .tab-button {
            padding: 20px 30px;
            background: none;
            border: none;
            font-size: 16px;
            font-weight: 600;
            color: #666;
            cursor: pointer;
            position: relative;
            transition: all 0.3s;
            text-transform: uppercase;
        }

        .tab-button:hover { color: var(--primary-red); background-color: white; }
        .tab-button.active { color: var(--primary-red); background-color: white; }

        .tab-button.active:after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 3px;
            background-color: var(--primary-red);
        }

        .tab-content { padding: 35px; }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; line-height: 1.8; color: #555;}

        .specs-table { width: 100%; border-collapse: collapse; }
        .specs-table tr { border-bottom: 1px solid #eee; }
        .specs-table td { padding: 15px 0; }
        .specs-table td:first-child { font-weight: 600; color: var(--dark-gray); width: 250px; }

        /* FAQ inside Tabs */
        .accordion-button:not(.collapsed) {
            color: var(--primary-red);
            background-color: #fff5f5;
        }
        .accordion-button:focus { box-shadow: none; border-color: rgba(227, 6, 19, 0.2); }

        /* Related Products */
        .related-products { margin-bottom: 60px; }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
        }

        .section-header h2 {
            color: var(--primary-blue);
            font-size: 1.6rem;
            margin: 0;
            font-weight: 800;
            text-transform: uppercase;
        }

        .view-all {
            color: var(--primary-red);
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .view-all:hover { text-decoration: underline; color: #c00510; }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
        }

        .product-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #eee;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            border-color: var(--primary-blue);
        }

        .product-image {
            height: 200px;
            overflow: hidden;
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .product-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: transform 0.3s;
        }

        .product-card:hover .product-image img { transform: scale(1.08); }

        .product-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: white;
        }
        .product-badge.featured { background-color: var(--primary-blue); }
        .product-badge.popular { background-color: var(--primary-red); }

        .product-info-small { padding: 20px; }
        .product-category-small {
            color: #888; font-size: 11px; margin-bottom: 8px; text-transform: uppercase; font-weight: 600;
        }
        .product-title-small {
            font-weight: 700; margin-bottom: 10px; font-size: 14px; line-height: 1.4; height: 40px; overflow: hidden; color: var(--dark-gray);
        }
        .product-price-small {
            color: var(--primary-red); font-weight: 800; font-size: 16px; margin-bottom: 15px;
        }

        .product-actions { display: flex; gap: 10px; }
        
        .btn-detail-small {
            flex: 1;
            background-color: transparent;
            color: var(--primary-blue);
            border: 2px solid var(--primary-blue);
            border-radius: 6px;
            padding: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            text-transform: uppercase;
        }
        .btn-detail-small:hover { background-color: var(--primary-blue); color: white; text-decoration: none; }

        .btn-cart-small {
            width: 40px;
            height: 40px;
            background-color: var(--primary-red);
            border: none;
            border-radius: 6px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }
        .btn-cart-small:hover { background-color: #c00510; color: white; text-decoration: none; }

        /* Modal & Form Styles */
        .login-modal, .register-modal {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0, 34, 85, 0.7); z-index: 1000; justify-content: center; align-items: center;
        }
        .login-content, .register-content {
            background-color: white; border-radius: 10px; width: 90%; max-width: 450px;
            padding: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto; position: relative;
        }
        .btn-login, .btn-register {
            background-color: var(--primary-blue); color: white; border: none; border-radius: 8px;
            padding: 12px; width: 100%; font-weight: 600; cursor: pointer; transition: background-color 0.3s;
        }
        .btn-login:hover, .btn-register:hover { background-color: #002266; }

        /* Footer */
        .footer {
            background-color: #001f55; color: white; padding: 60px 0 30px; margin-top: 60px;
            border-top: 5px solid var(--primary-red);
        }
        .footer-brand { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
        .footer-logo-img { height: 55px; background-color: white; border-radius: 30px; padding: 3px; }
        .footer-brand-title { color: white; font-size: 1.2rem; font-weight: 700; margin: 0; line-height: 1.2; }
        .footer h5 { color: white; font-weight: 600; margin-bottom: 25px; font-size: 1.1rem; }
        .footer-links { list-style: none; padding: 0; }
        .footer-links li { margin-bottom: 10px; }
        .footer-links a { color: #ccc; text-decoration: none; transition: color 0.3s; }
        .footer-links a:hover { color: white; }
        .social-icons a {
            display: inline-block; width: 36px; height: 36px; background: rgba(255,255,255,0.1);
            color: white; border-radius: 50%; text-align: center; line-height: 36px; margin-right: 10px; transition: background 0.3s;
        }
        .social-icons a:hover { background: var(--primary-red); }
        .copyright {
            text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);
            color: #aaa; font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .hku-header-actions { display: none; }
            .product-detail-layout { grid-template-columns: 1fr; }
            .products-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .action-buttons { flex-direction: column; }
            .btn-add-cart, .btn-buy-now { min-width: 100%; }
            .tab-header { flex-wrap: wrap; }
            .tab-button { padding: 15px; flex: 1; min-width: 120px; text-align: center; font-size: 14px; }
        }
        @media (max-width: 576px) {
            .hku-brand-section img { height: 45px; }
            .hku-brand-text h1 { font-size: 16px; }
            .hku-nav-container { flex-direction: column; width: 100%; }
            .hku-nav-link { width: 100%; text-align: center; border-bottom: 1px solid #eee; }
            .hku-nav-link.active { border-left: 4px solid var(--primary-red); border-bottom: none; }
            .product-title { font-size: 1.5rem; }
            .current-price { font-size: 1.8rem; }
            .products-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

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

                <a href="javascript:void(0);" class="nav-icon" id="cartLink">
                    <div style="position: relative;">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-badge" id="cartCount">
                            <?php 
                            if (isLoggedIn()) {
                                $user_id = $_SESSION['user_id'];
                                $cart_count_query = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE user_id = $user_id");
                                $cart_count = $cart_count_query->fetch_assoc()['total'] ?? 0;
                                echo $cart_count;
                            } else {
                                echo '0';
                            }
                            ?>
                        </span>
                    </div>
                    <span class="mt-1">Keranjang</span>
                </a>
                
                <div id="userSection">
                    <?php if (isLoggedIn()): ?>
                        <div class="user-dropdown" style="position: relative;">
                            <a href="javascript:void(0);" class="nav-icon" id="userDropdown">
                                <i class="fas fa-user"></i>
                                <span class="mt-1">
                                    <?php echo isset($_SESSION['first_name']) ? htmlspecialchars($_SESSION['first_name']) : 'Akun'; ?>
                                </span>
                            </a>
                            <div class="dropdown-menu" id="userDropdownMenu">
                                <span class="dropdown-item-text">
                                    <small>Logged in as:</small><br>
                                    <strong><?php echo htmlspecialchars($_SESSION['user_email']); ?></strong>
                                </span>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="orders.php"><i class="fas fa-shopping-bag me-2"></i>My Orders</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
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
            <a href="produk.php" class="hku-nav-link active">PRODUK</a>
            <a href="hubungikami.php" class="hku-nav-link">HUBUNGI KAMI</a>
        </div>
    </nav>

    <div class="breadcrumb">
        <a href="beranda.php">Beranda</a> &nbsp; <i class="fas fa-chevron-right" style="font-size:10px; color:#ccc;"></i> &nbsp;
        <a href="produk.php">Produk</a> &nbsp; <i class="fas fa-chevron-right" style="font-size:10px; color:#ccc;"></i> &nbsp;
        <a href="produk.php?category=<?php echo urlencode($product['category']); ?>"><?php echo htmlspecialchars($product['category']); ?></a> &nbsp; <i class="fas fa-chevron-right" style="font-size:10px; color:#ccc;"></i> &nbsp;
        <span><?php echo htmlspecialchars($product['name']); ?></span>
    </div>

    <div class="product-detail-container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="product-detail-layout">
            <div class="product-gallery">
                <div class="main-image" id="mainImage">
                    <img src="uploads/<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" id="currentImage">
                </div>
                <div class="image-thumbnails">
                    <div class="thumbnail active" data-image="uploads/<?php echo htmlspecialchars($product['image_url']); ?>">
                        <img src="uploads/<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    </div>
            </div>

            <div class="product-info">
                <div class="product-category"><?php echo htmlspecialchars($product['category']); ?></div>
                <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="product-meta">
                    <div class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                        <span class="review-count">(24 ulasan)</span>
                    </div>
                    <div class="stock-status <?php echo $product['stock'] > 0 ? 'stock-in' : 'stock-out'; ?>">
                        <i class="fas <?php echo $product['stock'] > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                        <?php echo $product['stock'] > 0 ? 'Stok Tersedia' : 'Stok Habis'; ?>
                        <?php if ($product['stock'] > 0): ?>
                            <span>(<?php echo $product['stock']; ?> unit)</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="product-price">
                    <div class="current-price">Rp <?php echo $formatted_price; ?></div>
                </div>
                
                <div class="product-description">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div>
                
                <div class="product-features">
                    <ul class="features-list">
                        <li><i class="fas fa-check-circle"></i> Garansi resmi 1 tahun dari HKU</li>
                        <li><i class="fas fa-check-circle"></i> Free konsultasi teknis & instalasi</li>
                        <li><i class="fas fa-check-circle"></i> Suku cadang terjamin keasliannya</li>
                        <li><i class="fas fa-check-circle"></i> Pengiriman aman seluruh Indonesia</li>
                    </ul>
                </div>
                
                <form method="POST" class="order-form">
                    <div class="quantity-control">
                        <div class="quantity-label">Jumlah Order:</div>
                        <div class="qty-input-group">
                            <button type="button" class="qty-btn" id="decreaseQty">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" name="quantity" id="quantity" class="qty-input" value="1" min="1" max="<?php echo $product['stock']; ?>">
                            <button type="button" class="qty-btn" id="increaseQty">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <button type="submit" name="add_to_cart" class="btn-add-cart" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                        </button>
                        
                        <a href="checkout.php?product_id=<?php echo $product_id; ?>&quantity=1" class="btn-buy-now" id="buyNowBtn" <?php echo $product['stock'] <= 0 ? 'onclick="return false;" style="opacity:0.5; cursor:not-allowed;"' : ''; ?>>
                            <i class="fas fa-bolt"></i> Beli Sekarang
                        </a>
                        
                        <button type="button" class="wishlist-btn" id="wishlistBtn" title="Simpan ke Wishlist">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="product-tabs">
            <div class="tab-header">
                <button class="tab-button active" data-tab="description">Deskripsi Lengkap</button>
                <button class="tab-button" data-tab="specifications">Spesifikasi Teknis</button>
                <button class="tab-button" data-tab="faq">Tanya Jawab (FAQ)</button>
            </div>
            
            <div class="tab-content">
                <div class="tab-pane active" id="description">
                    <div class="product-description-detail">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                        
                        <p class="mt-4 fw-bold">Mengapa memilih produk dari Hardjadinata Karya Utama?</p>
                        <ul>
                            <li>Material produk bermutu tinggi yang telah lolos standardisasi pabrik.</li>
                            <li>Tingkat efisiensi mesin maksimal dengan daya tahan (durability) jangka panjang.</li>
                            <li>Proses perawatan berkala mudah dengan ketersediaan komponen pendukung yang memadai.</li>
                            <li>Aplikatif untuk segala jenis skala perindustrian modern.</li>
                            <li>Purna jual memuaskan berkat dukungan mekanik teknis HKU yang berpengalaman.</li>
                        </ul>
                    </div>
                </div>
                
                <div class="tab-pane" id="specifications">
                    <table class="specs-table">
                        <tr>
                            <td>Kategori</td>
                            <td><?php echo htmlspecialchars($product['category']); ?></td>
                        </tr>
                        <tr>
                            <td>Nama Produk</td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                        </tr>
                        <tr>
                            <td>Kapasitas / Tipe</td>
                            <td>
                                <?php 
                                if ($product['category'] === 'Boiler') {
                                    echo 'Tipe Industri (Max Output: 500-1000 kg/jam)';
                                } elseif ($product['category'] === 'FBR Burner') {
                                    echo 'Single Stage, High Efficiency Burner';
                                } elseif ($product['category'] === 'Valve & Instrumentation') {
                                    echo 'Digital Control, Tahan Tekanan Tinggi';
                                } else {
                                    echo 'Standard Industrial Sparepart';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Material Utama</td>
                            <td>Standard Industrial Grade / Stainless Steel</td>
                        </tr>
                        <tr>
                            <td>Sistem Kelistrikan</td>
                            <td>220V / 380V (Menyesuaikan model mesin)</td>
                        </tr>
                        <tr>
                            <td>Standardisasi</td>
                            <td>SNI & ISO 9001:2015 Approved</td>
                        </tr>
                        <tr>
                            <td>Masa Garansi</td>
                            <td>12 Bulan (S&K Berlaku)</td>
                        </tr>
                    </table>
                </div>
                
                <div class="tab-pane" id="faq">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item mb-3" style="border: 1px solid #eee; border-radius: 8px; overflow: hidden;">
                            <h2 class="accordion-header">
                                <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Apakah produk ini selalu ready stock?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Ya, selama status produk menunjukkan "Stok Tersedia", berarti barang ada di gudang HKU dan siap dikirim begitu pembayaran telah terverifikasi.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item mb-3" style="border: 1px solid #eee; border-radius: 8px; overflow: hidden;">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Berapa lama estimasi pengiriman barang?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Untuk pengiriman area Surabaya & sekitarnya membutuhkan waktu 1-2 hari kerja. Wilayah luar Jawa Timur 2-4 hari kerja, dan pengiriman luar pulau menyesuaikan layanan ekspedisi kargo (umumnya 4-7 hari kerja).
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item mb-3" style="border: 1px solid #eee; border-radius: 8px; overflow: hidden;">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Bagaimana prosedur klaim garansinya?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Semua unit memiliki garansi resmi. Anda bisa langsung menghubungi kontak Customer Service HKU dengan melampirkan nomor *Invoice* atau *Delivery Order*. Teknisi kami akan memberikan panduan lebih lanjut atau melakukan visit ke lokasi jika diperlukan.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item mb-3" style="border: 1px solid #eee; border-radius: 8px; overflow: hidden;">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Apakah HKU menyediakan jasa teknisi instalasi di lokasi?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Ya, kami memiliki tim tenaga ahli yang melayani jasa *commissioning* dan *installation* ke pabrik Anda. Biaya jasa instalasi ini dapat didiskusikan secara terpisah dengan tim Sales kami.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($related_products)): ?>
            <div class="related-products">
                <div class="section-header">
                    <h2>Produk Sejenis</h2>
                    <a href="produk.php?category=<?php echo urlencode($product['category']); ?>" class="view-all">
                        Lihat Semua <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                
                <div class="products-grid">
                    <?php foreach ($related_products as $related): ?>
                        <div class="product-card">
                            <?php if ($related['featured']): ?>
                                <span class="product-badge featured">Unggulan</span>
                            <?php elseif ($related['popular']): ?>
                                <span class="product-badge popular">Populer</span>
                            <?php endif; ?>
                            
                            <div class="product-image">
                                <img src="uploads/<?php echo htmlspecialchars($related['image_url']); ?>" alt="<?php echo htmlspecialchars($related['name']); ?>">
                            </div>
                            
                            <div class="product-info-small">
                                <div class="product-category-small"><?php echo htmlspecialchars($related['category']); ?></div>
                                <div class="product-title-small">
                                    <a href="detailproduk.php?id=<?php echo $related['id']; ?>" style="color:inherit; text-decoration:none;"><?php echo htmlspecialchars($related['name']); ?></a>
                                </div>
                                <div class="product-price-small">Rp <?php echo number_format($related['price'], 0, ',', '.'); ?></div>
                                
                                <div class="product-actions">
                                    <a href="detailproduk.php?id=<?php echo $related['id']; ?>" class="btn-detail-small">Detail</a>
                                    <a href="javascript:void(0);" class="btn-cart-small add-to-cart-quick" data-id="<?php echo $related['id']; ?>" data-name="<?php echo htmlspecialchars($related['name']); ?>">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($popular_products)): ?>
            <div class="related-products">
                <div class="section-header">
                    <h2>Mungkin Anda Juga Suka</h2>
                    <a href="produk.php?popular=1" class="view-all">
                        Lihat Semua Populer <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                
                <div class="products-grid">
                    <?php foreach ($popular_products as $popular): ?>
                        <div class="product-card">
                            <span class="product-badge popular">Populer</span>
                            <div class="product-image">
                                <img src="uploads/<?php echo htmlspecialchars($popular['image_url']); ?>" alt="<?php echo htmlspecialchars($popular['name']); ?>">
                            </div>
                            
                            <div class="product-info-small">
                                <div class="product-category-small"><?php echo htmlspecialchars($popular['category']); ?></div>
                                <div class="product-title-small">
                                    <a href="detailproduk.php?id=<?php echo $popular['id']; ?>" style="color:inherit; text-decoration:none;"><?php echo htmlspecialchars($popular['name']); ?></a>
                                </div>
                                <div class="product-price-small">Rp <?php echo number_format($popular['price'], 0, ',', '.'); ?></div>
                                
                                <div class="product-actions">
                                    <a href="detailproduk.php?id=<?php echo $popular['id']; ?>" class="btn-detail-small">Detail</a>
                                    <a href="javascript:void(0);" class="btn-cart-small add-to-cart-quick" data-id="<?php echo $popular['id']; ?>" data-name="<?php echo htmlspecialchars($popular['name']); ?>">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
            
            <?php if (isset($_GET['login_required'])): ?>
                <div class="alert alert-info">Silakan masuk (login) untuk melanjutkan ke keranjang</div>
            <?php endif; ?>
            
            <h5 class="mb-4 text-center">SIGN IN</h5>
            
            <form id="loginForm" method="POST">
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
                    <a id="showRegister" style="color: var(--primary-blue); cursor: pointer;">Register Now</a>
                    <a href="forgot_password.php" style="color: var(--primary-red); text-decoration: none;">Forgot Password?</a>
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
            <form id="registerForm" method="POST">
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
                    <a id="showLogin" style="color: var(--primary-blue); cursor: pointer;">Already have an account? Sign In</a>
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
        // User Dropdown
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

        // Modal Auth
        const userLogin = document.getElementById('userLogin');
        const loginModal = document.getElementById('loginModal');
        const registerModal = document.getElementById('registerModal');
        
        if (userLogin) userLogin.addEventListener('click', () => loginModal.style.display = 'flex');
        document.getElementById('closeLogin').addEventListener('click', () => loginModal.style.display = 'none');
        document.getElementById('closeRegister').addEventListener('click', () => registerModal.style.display = 'none');
        
        document.getElementById('showRegister').addEventListener('click', () => {
            loginModal.style.display = 'none';
            registerModal.style.display = 'flex';
        });
        document.getElementById('showLogin').addEventListener('click', () => {
            registerModal.style.display = 'none';
            loginModal.style.display = 'flex';
        });
        
        window.addEventListener('click', (e) => {
            if (e.target === loginModal) loginModal.style.display = 'none';
            if (e.target === registerModal) registerModal.style.display = 'none';
        });

        // Search logic
        const searchInput = document.querySelector('.search-bar input');
        const searchButton = document.querySelector('.search-bar button');
        const executeSearch = () => {
            const term = searchInput.value.trim();
            if (term !== '') window.location.href = 'produk.php?search=' + encodeURIComponent(term);
        };
        searchButton.addEventListener('click', executeSearch);
        searchInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') executeSearch(); });

        document.addEventListener('DOMContentLoaded', function() {
            // Image Thumbnail Selection
            const thumbnails = document.querySelectorAll('.thumbnail');
            const mainImage = document.getElementById('currentImage');
            thumbnails.forEach(thumbnail => {
                thumbnail.addEventListener('click', function() {
                    thumbnails.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    mainImage.src = this.getAttribute('data-image');
                });
            });
            
            // Quantity Control
            const quantityInput = document.getElementById('quantity');
            const maxStock = <?php echo $product['stock']; ?>;
            
            document.getElementById('decreaseQty').addEventListener('click', function() {
                let val = parseInt(quantityInput.value);
                if (val > 1) quantityInput.value = val - 1;
                updateBuyNowLink();
            });
            
            document.getElementById('increaseQty').addEventListener('click', function() {
                let val = parseInt(quantityInput.value);
                if (val < maxStock) quantityInput.value = val + 1;
                else alert('Stok maksimum adalah ' + maxStock + ' unit');
                updateBuyNowLink();
            });
            
            quantityInput.addEventListener('change', function() {
                let val = parseInt(this.value);
                if (isNaN(val) || val < 1) this.value = 1;
                else if (val > maxStock) {
                    this.value = maxStock;
                    alert('Stok maksimum adalah ' + maxStock + ' unit');
                }
                updateBuyNowLink();
            });

            function updateBuyNowLink() {
                const buyBtn = document.getElementById('buyNowBtn');
                if(buyBtn && !buyBtn.disabled) {
                    let qty = quantityInput.value;
                    let productId = <?php echo $product_id; ?>;
                    buyBtn.href = `checkout.php?product_id=${productId}&quantity=${qty}`;
                }
            }
            
            // Tab Switching
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabPanes = document.querySelectorAll('.tab-pane');
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    tabPanes.forEach(pane => pane.classList.remove('active'));
                    document.getElementById(tabId).classList.add('active');
                });
            });
            
            // Wishlist Toggle
            const wishlistBtn = document.getElementById('wishlistBtn');
            const wishlistIcon = wishlistBtn.querySelector('i');
            let isWishlisted = false;
            wishlistBtn.addEventListener('click', function() {
                isWishlisted = !isWishlisted;
                if (isWishlisted) {
                    wishlistIcon.classList.remove('far');
                    wishlistIcon.classList.add('fas');
                    this.classList.add('active');
                    alert('Produk ditambahkan ke wishlist');
                } else {
                    wishlistIcon.classList.remove('fas');
                    wishlistIcon.classList.add('far');
                    this.classList.remove('active');
                    alert('Produk dihapus dari wishlist');
                }
            });
            
            // Quick Add to Cart (Related & Popular Products)
            document.querySelectorAll('.add-to-cart-quick').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const productId = this.getAttribute('data-id');
                    const productName = this.getAttribute('data-name');
                    
                    <?php if (isLoggedIn()): ?>
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', 'add_to_cart.php', true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.onload = function() {
                            if (xhr.status === 200) {
                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    if (response.success) {
                                        alert(productName + ' berhasil ditambahkan ke keranjang!');
                                        updateCartCount();
                                    } else {
                                        alert('Error: ' + response.message);
                                    }
                                } catch (e) {
                                    alert('Error parsing response');
                                }
                            }
                        };
                        xhr.send('product_id=' + productId + '&quantity=1');
                    <?php else: ?>
                        loginModal.style.display = 'flex';
                    <?php endif; ?>
                });
            });

            // Cart link logic
            const cartLink = document.getElementById('cartLink');
            if(cartLink) {
                cartLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    <?php if (isLoggedIn()): ?>
                        window.location.href = 'cart.php';
                    <?php else: ?>
                        loginModal.style.display = 'flex';
                    <?php endif; ?>
                });
            }

            // Update cart count
            function updateCartCount() {
                <?php if (isLoggedIn()): ?>
                    const xhr = new XMLHttpRequest();
                    xhr.open('GET', 'get_cart_count.php', true);
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.success) document.getElementById('cartCount').textContent = response.count;
                            } catch (e) {}
                        }
                    };
                    xhr.send();
                <?php endif; ?>
            }

            setInterval(updateCartCount, 30000);
            updateCartCount();
        });
    </script>
</body>
</html>