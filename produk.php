<?php
require_once 'config/database.php';
require_once 'config/check_login.php';

// Ambil parameter filter
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$featured = isset($_GET['featured']) ? $_GET['featured'] : '';
$popular = isset($_GET['popular']) ? $_GET['popular'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Query produk dengan filter
$where_conditions = [];
$params = [];
$types = '';

if ($category) {
    $where_conditions[] = "category = ?";
    $params[] = $category;
    $types .= 's';
}

if ($search) {
    $where_conditions[] = "(name LIKE ? OR description LIKE ? OR category LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'sss';
}

if ($featured) {
    $where_conditions[] = "featured = 1";
}

if ($popular) {
    $where_conditions[] = "popular = 1";
}

// Build query
$where_sql = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Sorting
$order_by = '';
switch ($sort) {
    case 'price_low':
        $order_by = 'ORDER BY price ASC';
        break;
    case 'price_high':
        $order_by = 'ORDER BY price DESC';
        break;
    case 'name':
        $order_by = 'ORDER BY name ASC';
        break;
    default:
        $order_by = 'ORDER BY created_at DESC';
        break;
}

try {
    // Get total products for pagination
    $count_sql = "SELECT COUNT(*) as total FROM products $where_sql";
    $stmt = $conn->prepare($count_sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $count_result = $stmt->get_result();
    $total_products = $count_result->fetch_assoc()['total'];
    $total_pages = ceil($total_products / $limit);
    
    // Get products
    $products_sql = "SELECT * FROM products $where_sql $order_by LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($products_sql);
    
    if (!empty($params)) {
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt->bind_param('ii', $limit, $offset);
    }
    
    $stmt->execute();
    $products_result = $stmt->get_result();
    $products = $products_result->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    error_log("Error fetching products: " . $e->getMessage());
    $products = [];
    $total_products = 0;
    $total_pages = 1;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - Hardjadinata Karya Utama</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
         /* ===== STICKY HEADER FIX ===== */
.sticky-wrapper {
    position: sticky;
    top: 0;
    z-index: 9999;
}

.hku-header-top {
    position: relative;
    z-index: 9999;
}

.hku-main-nav {
    position: relative;
    z-index: 9998;
}
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

        /* Products Hero Section */
        .products-hero {
            background-color: var(--primary-blue);
            color: white;
            padding: 50px 0;
            text-align: center;
            border-bottom: 5px solid var(--primary-red);
        }

        .products-hero h1 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .products-hero p {
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
        }

        /* Products Container */
        .products-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 15px;
        }

        /* Filter Section */
        .filter-section {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border: 1px solid #eee;
        }

        .filter-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .filter-label {
            font-weight: 700;
            color: var(--primary-blue);
        }

        .filter-select {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: white;
            color: var(--dark-gray);
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: border-color 0.3s;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary-blue);
        }

        .category-tags {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .category-tag {
            padding: 6px 18px;
            background-color: #f0f4f8;
            color: var(--primary-blue);
            border-radius: 20px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
            border: 1px solid transparent;
        }

        .category-tag:hover {
            background-color: var(--primary-blue);
            color: white;
            text-decoration: none;
        }

        .category-tag.active {
            background-color: var(--primary-red);
            color: white;
            box-shadow: 0 2px 8px rgba(227, 6, 19, 0.3);
        }

        /* Results Info */
        .results-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }

        .results-count {
            font-size: 1rem;
            color: #666;
            font-weight: 500;
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .product-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid #eee;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
            border-color: var(--primary-blue);
        }

        .product-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 5px 12px;
            color: white;
            font-size: 11px;
            font-weight: 700;
            border-radius: 4px;
            z-index: 1;
            text-transform: uppercase;
        }

        .product-badge.featured {
            background-color: var(--primary-blue);
        }

        .product-badge.popular {
            background-color: var(--primary-red);
        }

        .product-image {
            height: 200px;
            overflow: hidden;
            position: relative;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .product-card:hover .product-image img {
            transform: scale(1.08);
        }

        .product-info {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-category {
            font-size: 12px;
            color: #888;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .product-name {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: var(--dark-gray);
            line-height: 1.4;
        }

        .product-name a {
            color: inherit;
            text-decoration: none;
            transition: color 0.3s;
        }

        .product-name a:hover {
            color: var(--primary-red);
        }

        .product-description {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 15px;
            flex-grow: 1;
        }

        .product-price {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--primary-red);
            margin-bottom: 15px;
        }

        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: auto;
        }

        .btn-detail {
            background-color: transparent;
            color: var(--primary-blue);
            border: 2px solid var(--primary-blue);
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            flex: 1;
            text-transform: uppercase;
        }

        .btn-detail:hover {
            background-color: var(--primary-blue);
            color: white;
            text-decoration: none;
        }

        .btn-add-cart {
            background-color: var(--primary-red);
            color: white;
            border: 2px solid var(--primary-red);
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            flex: 1;
            text-transform: uppercase;
        }

        .btn-add-cart:hover {
            background-color: #c00510;
            border-color: #c00510;
            color: white;
            text-decoration: none;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
            margin-top: 40px;
        }

        .page-link {
            display: block;
            padding: 8px 16px;
            background-color: white;
            border: 1px solid #ddd;
            color: var(--dark-gray);
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
            font-weight: 600;
        }

        .page-link:hover {
            background-color: var(--primary-blue);
            color: white;
            border-color: var(--primary-blue);
        }

        .page-item.active .page-link {
            background-color: var(--primary-red);
            color: white;
            border-color: var(--primary-red);
            box-shadow: 0 2px 5px rgba(227, 6, 19, 0.3);
        }

        .page-item.disabled .page-link {
            background-color: #f8f9fa;
            color: #aaa;
            cursor: not-allowed;
            border-color: #eee;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border: 1px solid #eee;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--primary-blue);
            opacity: 0.3;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: var(--primary-blue);
            margin-bottom: 10px;
            font-weight: 700;
        }

        /* Modal & Form Styles */
        .login-modal, .register-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 34, 85, 0.7);
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

        .btn-login:hover, .btn-register:hover {
            background-color: #002266;
        }

        /* Footer */
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

        .social-icons a:hover {
            background: var(--primary-red);
        }

        .copyright {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: #aaa;
            font-size: 14px;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .hku-header-actions { display: none; }
            .hku-brand-text h1 { font-size: 20px; }
            .hku-nav-link { padding: 10px 15px; font-size: 13px; }
        }
        @media (max-width: 768px) {
            .filter-row { flex-direction: column; align-items: flex-start; }
            .products-grid { grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; }
            .product-actions { flex-direction: column; }
            .btn-detail, .btn-add-cart { width: 100%; }
        }
        @media (max-width: 576px) {
            .hku-brand-section img { height: 45px; }
            .hku-brand-text h1 { font-size: 16px; }
            .hku-nav-container { flex-direction: column; width: 100%; }
            .hku-nav-link { width: 100%; text-align: center; border-bottom: 1px solid #eee; }
            .hku-nav-link.active { border-left: 4px solid var(--primary-red); border-bottom: none; }
            .products-hero h1 { font-size: 1.8rem; }
            .products-grid { grid-template-columns: 1fr; }
            .pagination { flex-wrap: wrap; }
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
</div>
    <section class="products-hero">
        <div class="container">
            <h1>
                <?php 
                if ($category) {
                    echo "Produk " . htmlspecialchars($category);
                } elseif ($search) {
                    echo "Hasil Pencarian: " . htmlspecialchars($search);
                } elseif ($featured) {
                    echo "Produk Unggulan";
                } elseif ($popular) {
                    echo "Produk Populer";
                } else {
                    echo "Semua Produk";
                }
                ?>
            </h1>
            <p>
                <?php 
                if ($category) {
                    echo "Temukan berbagai produk " . htmlspecialchars($category) . " berkualitas untuk kebutuhan industri Anda";
                } elseif ($search) {
                    echo "Menampilkan produk yang sesuai dengan pencarian Anda";
                } else {
                    echo "Temukan berbagai produk industri berkualitas untuk kebutuhan bisnis Anda";
                }
                ?>
            </p>
        </div>
    </section>

    <div class="products-container">
        <div class="filter-section">
            <div class="filter-row">
                <div class="filter-group">
                    <span class="filter-label"><i class="fas fa-filter me-1"></i> Filter:</span>
                    <select class="filter-select" id="categoryFilter" onchange="if(this.value !== '') { window.location.href='produk.php?category=' + encodeURIComponent(this.value) } else { window.location.href='produk.php' }">
                        <option value="">Semua Kategori</option>
                        <option value="FBR Burner" <?php echo $category == 'FBR Burner' ? 'selected' : ''; ?>>FBR Burner</option>
                        <option value="Boiler" <?php echo $category == 'Boiler' ? 'selected' : ''; ?>>Boiler</option>
                        <option value="Valve & Instrumentation" <?php echo $category == 'Valve & Instrumentation' ? 'selected' : ''; ?>>Valve & Instrumentation</option>
                        <option value="Sparepart" <?php echo $category == 'Sparepart' ? 'selected' : ''; ?>>Spare Part</option>
                    </select>
                    
                    <span class="filter-label ms-md-3"><i class="fas fa-sort me-1"></i> Urutkan:</span>
                    <select class="filter-select" id="sortFilter" onchange="updateSort(this.value)">
                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                        <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Harga: Rendah ke Tinggi</option>
                        <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Harga: Tinggi ke Rendah</option>
                        <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Nama: A-Z</option>
                    </select>
                </div>
                
                <a href="produk.php" class="btn-detail" style="flex: 0 1 auto; max-width: 150px; border-color: var(--primary-red); color: var(--primary-red);">
                    <i class="fas fa-redo"></i> Reset Filter
                </a>
            </div>
            
            <?php if (!$category && !$search && !$featured && !$popular): ?>
            <div class="category-tags">
                <a href="produk.php?featured=1" class="category-tag <?php echo $featured ? 'active' : ''; ?>">
                    <i class="fas fa-star me-1"></i> Unggulan
                </a>
                <a href="produk.php?popular=1" class="category-tag <?php echo $popular ? 'active' : ''; ?>">
                    <i class="fas fa-fire me-1"></i> Populer
                </a>
                <a href="produk.php?category=FBR Burner" class="category-tag">
                    <i class="fas fa-fire me-1"></i> FBR Burner
                </a>
                <a href="produk.php?category=Boiler" class="category-tag">
                    <i class="fas fa-industry me-1"></i> Boiler
                </a>
                <a href="produk.php?category=<?php echo urlencode('Valve & Instrumentation'); ?>" class="category-tag">
                    <i class="fas fa-gauge-high me-1"></i> Valve & Inst.
                </a>
                <a href="produk.php?category=Sparepart" class="category-tag">
                    <i class="fas fa-gear me-1"></i> Spare Part
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="results-info">
            <div class="results-count">
                Menampilkan <strong><?php echo count($products); ?></strong> dari <strong><?php echo $total_products; ?></strong> produk
                <?php if ($search): ?>
                    untuk "<strong><span style="color: var(--primary-red);"><?php echo htmlspecialchars($search); ?></span></strong>"
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($products)): ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <?php if ($product['featured']): ?>
                            <span class="product-badge featured">Unggulan</span>
                        <?php elseif ($product['popular']): ?>
                            <span class="product-badge popular">Populer</span>
                        <?php endif; ?>
                        
                        <div class="product-image">
                            <img src="uploads/<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                        
                        <div class="product-info">
                            <div class="product-category"><?php echo htmlspecialchars($product['category']); ?></div>
                            <h3 class="product-name">
                                <a href="detailproduk.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a>
                            </h3>
                            <p class="product-description"><?php echo mb_strimwidth(htmlspecialchars($product['description']), 0, 90, '...'); ?></p>
                            
                            <div class="product-price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></div>
                            
                            <div class="product-actions">
                                <a href="detailproduk.php?id=<?php echo $product['id']; ?>" class="btn-detail">Detail</a>
                                <a href="javascript:void(0);" class="btn-add-cart add-to-cart" data-product-id="<?php echo $product['id']; ?>" data-product-name="<?php echo htmlspecialchars($product['name']); ?>">
                                    <i class="fas fa-cart-plus"></i> Keranjang
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($total_pages > 1): ?>
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo buildPageUrl($page - 1); ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link"><i class="fas fa-chevron-left"></i></span>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo buildPageUrl($i); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo buildPageUrl($page + 1); ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link"><i class="fas fa-chevron-right"></i></span>
                        </li>
                    <?php endif; ?>
                </ul>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>Tidak ada produk ditemukan</h3>
                <p>Silakan coba dengan kata kunci atau kategori yang berbeda</p>
                <a href="produk.php" class="btn-add-cart" style="display: inline-block; width: auto; padding: 10px 30px;">
                    Lihat Semua Produk
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php 
    // Helper function to build pagination URLs
    function buildPageUrl($page_num) {
        $params = $_GET;
        $params['page'] = $page_num;
        return 'produk.php?' . http_build_query($params);
    }
    ?>

    <div class="login-modal" id="loginModal">
        <div class="login-content">
            <button class="close-btn" id="closeLogin" style="position: absolute; top: 15px; right: 15px; border: none; background: transparent; font-size: 20px;">&times;</button>
            <div class="text-center mb-4">
                <h3 style="color: var(--primary-blue); font-weight: 700;">HKU</h3>
                <p class="text-muted">Surabaya</p>
            </div>
            
            <?php if (isset($_GET['login_required'])): ?>
                <div class="alert alert-info">Please login to continue to cart</div>
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
        // Modal & Auth Logic
        const userLogin = document.getElementById('userLogin');
        const loginModal = document.getElementById('loginModal');
        const registerModal = document.getElementById('registerModal');
        const closeLogin = document.getElementById('closeLogin');
        const closeRegister = document.getElementById('closeRegister');
        
        if (userLogin) userLogin.addEventListener('click', () => loginModal.style.display = 'flex');
        closeLogin.addEventListener('click', () => loginModal.style.display = 'none');
        closeRegister.addEventListener('click', () => registerModal.style.display = 'none');
        
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
        searchButton.addEventListener('click', executeSearch);
        searchInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') executeSearch(); });

        // Update sort parameter
        function updateSort(sortValue) {
            const url = new URL(window.location.href);
            url.searchParams.set('sort', sortValue);
            url.searchParams.set('page', 1); // Reset to first page when sorting
            window.location.href = url.toString();
        }

        // Add to cart functionality
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.getAttribute('data-product-id');
                const productName = this.getAttribute('data-product-name');
                
                <?php if (isLoggedIn()): ?>
                    // AJAX request to add to cart
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
                    // User not logged in, show login modal
                    loginModal.style.display = 'flex';
                <?php endif; ?>
            });
        });

        // Cart link click handler
        document.getElementById('cartLink').addEventListener('click', function(e) {
            e.preventDefault();
            <?php if (isLoggedIn()): ?>
                window.location.href = 'cart.php';
            <?php else: ?>
                loginModal.style.display = 'flex';
            <?php endif; ?>
        });

        // Update Cart Count
        function updateCartCount() {
            <?php if (isLoggedIn()): ?>
                const xhr = new XMLHttpRequest();
                xhr.open('GET', 'get_cart_count.php', true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            const res = JSON.parse(xhr.responseText);
                            if (res.success) document.getElementById('cartCount').textContent = res.count;
                        } catch (e) {}
                    }
                };
                xhr.send();
            <?php endif; ?>
        }

        setInterval(updateCartCount, 30000);
        document.addEventListener('DOMContentLoaded', updateCartCount);
    </script>
</body>
</html>