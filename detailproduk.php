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
        header('Location: index.php?login_required=1&redirect=detailproduk.php?id=' . $product_id);
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
    <title><?php echo htmlspecialchars($product['name']); ?> - Megatek Industrial Persada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    
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

        /* Reuse all common styles from cart.php */
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

        /* Breadcrumb */
        .breadcrumb {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
            font-size: 14px;
        }

        .breadcrumb a {
            color: #666;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            color: var(--primary-blue);
            text-decoration: underline;
        }

        .breadcrumb span {
            color: var(--primary-blue);
            font-weight: 500;
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

        /* Product Detail Layout */
        .product-detail-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 60px;
        }

        @media (max-width: 992px) {
            .product-detail-layout {
                grid-template-columns: 1fr;
            }
        }

        /* Product Gallery */
        .product-gallery {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .main-image {
            width: 100%;
            height: 400px;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 15px;
            background-color: #f8f9fa;
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
            border: 2px solid transparent;
            cursor: pointer;
            transition: border-color 0.3s;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .thumbnail:hover {
            border-color: #ccc;
        }

        .thumbnail.active {
            border-color: var(--primary-blue);
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Product Info */
        .product-info {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .product-category {
            display: inline-block;
            background-color: #f0f7ff;
            color: var(--primary-blue);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 15px;
        }

        .product-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-gray);
            margin-bottom: 15px;
            line-height: 1.3;
        }

        .product-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #ffc107;
        }

        .review-count {
            color: #666;
            font-size: 14px;
        }

        .stock-status {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }

        .stock-in {
            color: #28a745;
        }

        .stock-out {
            color: #dc3545;
        }

        .product-price {
            margin-bottom: 25px;
        }

        .current-price {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-blue);
        }

        .product-description {
            margin-bottom: 30px;
            line-height: 1.6;
            color: #555;
        }

        /* Product Features */
        .product-features {
            margin-bottom: 30px;
        }

        .features-list {
            list-style: none;
            padding: 0;
        }

        .features-list li {
            padding: 8px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .features-list i {
            color: var(--primary-blue);
            width: 20px;
        }

        /* Order Form */
        .order-form {
            margin-top: 30px;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .quantity-label {
            font-weight: 500;
            color: var(--dark-gray);
        }

        .qty-input-group {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            width: fit-content;
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

        .qty-btn:hover {
            background-color: #e9ecef;
        }

        .qty-input {
            width: 60px;
            height: 40px;
            border: none;
            text-align: center;
            font-size: 16px;
            font-weight: 500;
        }

        .qty-input:focus {
            outline: none;
        }

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
            border: none;
            border-radius: 8px;
            padding: 15px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-add-cart:hover {
            background-color: #153a6e;
        }

        .btn-add-cart:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .btn-buy-now {
            flex: 1;
            min-width: 200px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 15px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-buy-now:hover {
            background-color: #218838;
            color: white;
            text-decoration: none;
        }

        .wishlist-btn {
            width: 50px;
            height: 50px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .wishlist-btn:hover {
            background-color: #f8f9fa;
            border-color: #ff6b6b;
        }

        .wishlist-btn.active {
            background-color: #ff6b6b;
            border-color: #ff6b6b;
            color: white;
        }

        /* Product Details Tabs */
        .product-tabs {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 60px;
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
            font-weight: 500;
            color: #666;
            cursor: pointer;
            position: relative;
            transition: all 0.3s;
        }

        .tab-button:hover {
            color: var(--primary-blue);
            background-color: white;
        }

        .tab-button.active {
            color: var(--primary-blue);
            background-color: white;
        }

        .tab-button.active:after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 3px;
            background-color: var(--primary-blue);
        }

        .tab-content {
            padding: 30px;
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
        }

        .specs-table {
            width: 100%;
            border-collapse: collapse;
        }

        .specs-table tr {
            border-bottom: 1px solid #eee;
        }

        .specs-table td {
            padding: 15px 0;
        }

        .specs-table td:first-child {
            font-weight: 500;
            color: var(--dark-gray);
            width: 200px;
        }

        .specs-table td:last-child {
            color: #555;
        }

        /* Reviews Section */
        .reviews-summary {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 1px solid #eee;
        }

        .average-rating {
            text-align: center;
        }

        .rating-score {
            font-size: 3rem;
            font-weight: 700;
            color: var(--dark-gray);
            line-height: 1;
        }

        .rating-stars {
            color: #ffc107;
            font-size: 20px;
            margin: 10px 0;
        }

        .review-count-large {
            color: #666;
        }

        .rating-bars {
            flex: 1;
        }

        .rating-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .bar-label {
            width: 40px;
            font-size: 14px;
            color: #666;
        }

        .bar-container {
            flex: 1;
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            background-color: #ffc107;
        }

        /* Related Products */
        .related-products {
            margin-bottom: 60px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .section-header h2 {
            color: var(--primary-blue);
            font-size: 1.5rem;
            margin: 0;
        }

        .view-all {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .view-all:hover {
            text-decoration: underline;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
        }

        @media (max-width: 992px) {
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
        }

        .product-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .product-image {
            height: 200px;
            overflow: hidden;
            background-color: #f8f9fa;
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

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .product-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background-color: var(--primary-blue);
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .product-info-small {
            padding: 20px;
        }

        .product-category-small {
            color: #666;
            font-size: 12px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .product-title-small {
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 14px;
            line-height: 1.4;
            height: 40px;
            overflow: hidden;
        }

        .product-price-small {
            color: var(--primary-blue);
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 15px;
        }

        .product-actions {
            display: flex;
            gap: 10px;
        }

        .btn-detail-small {
            flex: 1;
            background-color: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            text-align: center;
        }

        .btn-detail-small:hover {
            background-color: #153a6e;
            color: white;
            text-decoration: none;
        }

        .btn-cart-small {
            width: 40px;
            height: 40px;
            background-color: white;
            border: 1px solid var(--primary-blue);
            border-radius: 6px;
            color: var(--primary-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-cart-small:hover {
            background-color: var(--primary-blue);
            color: white;
            text-decoration: none;
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
            
            .product-detail-container {
                padding: 0 15px 20px;
            }
            
            .product-detail-layout {
                gap: 20px;
            }
            
            .product-info {
                padding: 20px;
            }
            
            .product-title {
                font-size: 1.5rem;
            }
            
            .current-price {
                font-size: 1.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-add-cart, .btn-buy-now {
                min-width: 100%;
            }
            
            .tab-header {
                flex-wrap: wrap;
            }
            
            .tab-button {
                padding: 15px;
                flex: 1;
                min-width: 120px;
                text-align: center;
                font-size: 14px;
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
            
            .reviews-summary {
                flex-direction: column;
                text-align: center;
            }
            
            .rating-bars {
                width: 100%;
            }
        }
    </style>
</head>
<body>

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
                    <span class="cart-badge" id="cartCount">0</span>
                </div>
                <span>Cart</span>
            </a>
            
            <div id="userSection">
                <?php if (isLoggedIn()): ?>
                    <div class="user-dropdown">
                        <a href="javascript:void(0);" class="nav-icon">
                            <i class="fas fa-user"></i>
                            <span>Akun</span>
                        </a>
                    </div>
                <?php else: ?>
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

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="index.php">Home</a> &gt;
        <a href="produk.php">Produk</a> &gt;
        <a href="produk.php?category=<?php echo urlencode($product['category']); ?>"><?php echo htmlspecialchars($product['category']); ?></a> &gt;
        <span><?php echo htmlspecialchars($product['name']); ?></span>
    </div>

    <!-- Product Detail Container -->
    <div class="product-detail-container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="product-detail-layout">
            <!-- Product Gallery -->
            <div class="product-gallery">
                <div class="main-image" id="mainImage">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" id="currentImage">
                </div>
                <div class="image-thumbnails">
                    <div class="thumbnail active" data-image="<?php echo htmlspecialchars($product['image_url']); ?>">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    <!-- Additional thumbnails can be added here -->
                </div>
            </div>

            <!-- Product Info -->
            <div class="product-info">
                <div class="product-category">
                    <?php echo htmlspecialchars($product['category']); ?>
                    <?php if ($product['featured']): ?>
                        <span class="badge bg-warning ms-2">Unggulan</span>
                    <?php endif; ?>
                    <?php if ($product['popular']): ?>
                        <span class="badge bg-danger ms-2">Populer</span>
                    <?php endif; ?>
                </div>
                
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
                        <li><i class="fas fa-check"></i> Garansi resmi 1 tahun</li>
                        <li><i class="fas fa-check"></i> Free konsultasi teknis</li>
                        <li><i class="fas fa-check"></i> Dukungan instalasi</li>
                        <li><i class="fas fa-check"></i> Sparepart original</li>
                        <li><i class="fas fa-check"></i> Pengiriman cepat</li>
                    </ul>
                </div>
                
                <form method="POST" class="order-form">
                    <div class="quantity-control">
                        <div class="quantity-label">Jumlah:</div>
                        <div class="qty-input-group">
                            <button type="button" class="qty-btn" id="decreaseQty">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" name="quantity" id="quantity" class="qty-input" value="1" min="1" max="<?php echo $product['stock']; ?>">
                            <button type="button" class="qty-btn" id="increaseQty">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div class="stock-info">
                            <small class="text-muted">Stok tersedia: <?php echo $product['stock']; ?> unit</small>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <button type="submit" name="add_to_cart" class="btn-add-cart" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-cart"></i> Tambah ke Keranjang
                        </button>
                        
                        <a href="checkout.php?product_id=<?php echo $product_id; ?>&quantity=1" class="btn-buy-now" <?php echo $product['stock'] <= 0 ? 'onclick="return false;" style="opacity:0.5; cursor:not-allowed;"' : ''; ?>>
                            <i class="fas fa-bolt"></i> Beli Sekarang
                        </a>
                        
                        <button type="button" class="wishlist-btn" id="wishlistBtn">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Product Details Tabs -->
        <div class="product-tabs">
            <div class="tab-header">
                <button class="tab-button active" data-tab="description">Deskripsi Produk</button>
                <button class="tab-button" data-tab="specifications">Spesifikasi Teknis</button>
                <button class="tab-button" data-tab="reviews">Ulasan (24)</button>
                <button class="tab-button" data-tab="faq">FAQ</button>
            </div>
            
            <div class="tab-content">
                <!-- Description Tab -->
                <div class="tab-pane active" id="description">
                    <div class="product-description-detail">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                        <p><br>Keunggulan produk <?php echo htmlspecialchars($product['name']); ?>:</p>
                        <ul>
                            <li>Material berkualitas tinggi dan tahan lama</li>
                            <li>Desain efisien untuk performa optimal</li>
                            <li>Mudah dalam perawatan dan pemeliharaan</li>
                            <li>Kompatibel dengan berbagai sistem industri</li>
                            <li>Didukung oleh tim teknis berpengalaman</li>
                        </ul>
                        <p><br>Spesifikasi lengkap dan dokumentasi teknis akan disertakan dalam pengiriman.</p>
                    </div>
                </div>
                
                <!-- Specifications Tab -->
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
                            <td>Kapasitas</td>
                            <td>
                                <?php 
                                if ($product['category'] === 'Boiler') {
                                    echo '500 kg/jam';
                                } elseif ($product['category'] === 'FBR Burner') {
                                    echo 'Single Stage, High Efficiency';
                                } elseif ($product['category'] === 'Valve & Instrumentation') {
                                    echo '4 inch, Digital Control';
                                } else {
                                    echo 'Complete Kit';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Material</td>
                            <td>Stainless Steel Grade 304</td>
                        </tr>
                        <tr>
                            <td>Daya</td>
                            <td>220V / 380V, 3 Phase</td>
                        </tr>
                        <tr>
                            <td>Berat</td>
                            <td>85 kg</td>
                        </tr>
                        <tr>
                            <td>Dimensi</td>
                            <td>150 x 80 x 120 cm</td>
                        </tr>
                        <tr>
                            <td>Garansi</td>
                            <td>1 Tahun</td>
                        </tr>
                        <tr>
                            <td>Standar</td>
                            <td>SNI, ISO 9001:2015</td>
                        </tr>
                    </table>
                </div>
                
                <!-- Reviews Tab -->
                <div class="tab-pane" id="reviews">
                    <div class="reviews-summary">
                        <div class="average-rating">
                            <div class="rating-score">4.8</div>
                            <div class="rating-stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <div class="review-count-large">24 ulasan</div>
                        </div>
                        
                        <div class="rating-bars">
                            <div class="rating-bar">
                                <div class="bar-label">5★</div>
                                <div class="bar-container">
                                    <div class="bar-fill" style="width: 85%;"></div>
                                </div>
                                <div class="bar-percentage">85%</div>
                            </div>
                            <div class="rating-bar">
                                <div class="bar-label">4★</div>
                                <div class="bar-container">
                                    <div class="bar-fill" style="width: 12%;"></div>
                                </div>
                                <div class="bar-percentage">12%</div>
                            </div>
                            <div class="rating-bar">
                                <div class="bar-label">3★</div>
                                <div class="bar-container">
                                    <div class="bar-fill" style="width: 2%;"></div>
                                </div>
                                <div class="bar-percentage">2%</div>
                            </div>
                            <div class="rating-bar">
                                <div class="bar-label">2★</div>
                                <div class="bar-container">
                                    <div class="bar-fill" style="width: 1%;"></div>
                                </div>
                                <div class="bar-percentage">1%</div>
                            </div>
                            <div class="rating-bar">
                                <div class="bar-label">1★</div>
                                <div class="bar-container">
                                    <div class="bar-fill" style="width: 0%;"></div>
                                </div>
                                <div class="bar-percentage">0%</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="reviews-list">
                        <!-- Sample Review -->
                        <div class="review-item">
                            <div class="review-header">
                                <div class="reviewer">
                                    <strong>PT. Industri Maju</strong>
                                    <div class="review-date">15 Desember 2023</div>
                                </div>
                                <div class="review-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                            <div class="review-content">
                                <p>Produk berkualitas tinggi, pengiriman tepat waktu, dan dukungan teknis yang sangat membantu. Sangat direkomendasikan!</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- FAQ Tab -->
                <div class="tab-pane" id="faq">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Apakah produk ini ready stock?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Ya, produk ini tersedia dalam stok sebanyak <?php echo $product['stock']; ?> unit. Kami akan segera mengirimkan setelah pembayaran dikonfirmasi.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Berapa lama waktu pengiriman?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Pengiriman untuk area Surabaya 1-2 hari kerja, Jawa Timur 2-3 hari kerja, dan luar Jawa 3-7 hari kerja.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Apakah ada garansi?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Ya, semua produk kami bergaransi resmi 1 tahun untuk kerusakan akibat manufaktur.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Apakah tersedia instalasi?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Ya, kami menyediakan jasa instalasi dengan tim teknis berpengalaman. Biaya instalasi terpisah dan dapat dinegosiasikan.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
            <div class="related-products">
                <div class="section-header">
                    <h2>Produk Terkait</h2>
                    <a href="produk.php?category=<?php echo urlencode($product['category']); ?>" class="view-all">
                        Lihat Semua <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="products-grid">
                    <?php foreach ($related_products as $related): ?>
                        <div class="product-card">
                            <?php if ($related['featured']): ?>
                                <div class="product-badge">Unggulan</div>
                            <?php endif; ?>
                            
                            <div class="product-image">
                                <img src="<?php echo htmlspecialchars($related['image_url']); ?>" alt="<?php echo htmlspecialchars($related['name']); ?>">
                            </div>
                            
                            <div class="product-info-small">
                                <div class="product-category-small"><?php echo htmlspecialchars($related['category']); ?></div>
                                <div class="product-title-small"><?php echo htmlspecialchars($related['name']); ?></div>
                                <div class="product-price-small">Rp <?php echo number_format($related['price'], 0, ',', '.'); ?></div>
                                
                                <div class="product-actions">
                                    <a href="detailproduk.php?id=<?php echo $related['id']; ?>" class="btn-detail-small">
                                        Detail
                                    </a>
                                    <a href="detailproduk.php?id=<?php echo $related['id']; ?>" class="btn-cart-small">
                                        <i class="fas fa-shopping-cart"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Popular Products -->
        <?php if (!empty($popular_products)): ?>
            <div class="related-products">
                <div class="section-header">
                    <h2>Produk Populer Lainnya</h2>
                    <a href="produk.php?popular=1" class="view-all">
                        Lihat Semua <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="products-grid">
                    <?php foreach ($popular_products as $popular): ?>
                        <div class="product-card">
                            <?php if ($popular['featured']): ?>
                                <div class="product-badge">Unggulan</div>
                            <?php endif; ?>
                            
                            <div class="product-image">
                                <img src="<?php echo htmlspecialchars($popular['image_url']); ?>" alt="<?php echo htmlspecialchars($popular['name']); ?>">
                            </div>
                            
                            <div class="product-info-small">
                                <div class="product-category-small"><?php echo htmlspecialchars($popular['category']); ?></div>
                                <div class="product-title-small"><?php echo htmlspecialchars($popular['name']); ?></div>
                                <div class="product-price-small">Rp <?php echo number_format($popular['price'], 0, ',', '.'); ?></div>
                                
                                <div class="product-actions">
                                    <a href="detailproduk.php?id=<?php echo $popular['id']; ?>" class="btn-detail-small">
                                        Detail
                                    </a>
                                    <a href="detailproduk.php?id=<?php echo $popular['id']; ?>" class="btn-cart-small">
                                        <i class="fas fa-shopping-cart"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
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
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    <script>
        // Product Detail Functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Image Thumbnail Selection
            const thumbnails = document.querySelectorAll('.thumbnail');
            const mainImage = document.getElementById('currentImage');
            
            thumbnails.forEach(thumbnail => {
                thumbnail.addEventListener('click', function() {
                    // Remove active class from all thumbnails
                    thumbnails.forEach(t => t.classList.remove('active'));
                    
                    // Add active class to clicked thumbnail
                    this.classList.add('active');
                    
                    // Update main image
                    const newImageSrc = this.getAttribute('data-image');
                    mainImage.src = newImageSrc;
                });
            });
            
            // Quantity Control
            const quantityInput = document.getElementById('quantity');
            const decreaseBtn = document.getElementById('decreaseQty');
            const increaseBtn = document.getElementById('increaseQty');
            const maxStock = <?php echo $product['stock']; ?>;
            
            decreaseBtn.addEventListener('click', function() {
                let currentValue = parseInt(quantityInput.value);
                if (currentValue > 1) {
                    quantityInput.value = currentValue - 1;
                }
            });
            
            increaseBtn.addEventListener('click', function() {
                let currentValue = parseInt(quantityInput.value);
                if (currentValue < maxStock) {
                    quantityInput.value = currentValue + 1;
                } else {
                    alert('Stok maksimum adalah ' + maxStock + ' unit');
                }
            });
            
            quantityInput.addEventListener('change', function() {
                let value = parseInt(this.value);
                if (isNaN(value) || value < 1) {
                    this.value = 1;
                } else if (value > maxStock) {
                    this.value = maxStock;
                    alert('Stok maksimum adalah ' + maxStock + ' unit');
                }
            });
            
            // Tab Switching
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabPanes = document.querySelectorAll('.tab-pane');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Update active tab button
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Update active tab pane
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
                    wishlistIcon.classList.remove('far', 'fa-heart');
                    wishlistIcon.classList.add('fas', 'fa-heart');
                    this.classList.add('active');
                    showToast('Produk ditambahkan ke wishlist');
                } else {
                    wishlistIcon.classList.remove('fas', 'fa-heart');
                    wishlistIcon.classList.add('far', 'fa-heart');
                    this.classList.remove('active');
                    showToast('Produk dihapus dari wishlist');
                }
            });
            
            // Cart Count Update
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
            
            // Toast Notification
            function showToast(message) {
                const toast = document.createElement('div');
                toast.className = 'toast-notification';
                toast.innerHTML = `
                    <div class="toast-content">
                        <i class="fas fa-check-circle"></i>
                        <span>${message}</span>
                    </div>
                `;
                document.body.appendChild(toast);
                
                // Add styles for toast
                toast.style.position = 'fixed';
                toast.style.bottom = '20px';
                toast.style.right = '20px';
                toast.style.backgroundColor = '#4CAF50';
                toast.style.color = 'white';
                toast.style.padding = '15px 20px';
                toast.style.borderRadius = '8px';
                toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
                toast.style.zIndex = '1000';
                toast.style.display = 'flex';
                toast.style.alignItems = 'center';
                toast.style.gap = '10px';
                toast.style.animation = 'fadeIn 0.3s ease-in';
                
                // Remove toast after 3 seconds
                setTimeout(() => {
                    toast.style.animation = 'fadeOut 0.3s ease-out';
                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.parentNode.removeChild(toast);
                        }
                    }, 300);
                }, 3000);
                
                // Add keyframes for animation
                const style = document.createElement('style');
                style.textContent = `
                    @keyframes fadeIn {
                        from { opacity: 0; transform: translateY(20px); }
                        to { opacity: 1; transform: translateY(0); }
                    }
                    @keyframes fadeOut {
                        from { opacity: 1; transform: translateY(0); }
                        to { opacity: 0; transform: translateY(20px); }
                    }
                `;
                document.head.appendChild(style);
            }
            
            // Initialize cart count
            updateCartCount();
            
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
            
            // Update cart count every 30 seconds
            setInterval(updateCartCount, 30000);
        });
    </script>
</body>
</html>