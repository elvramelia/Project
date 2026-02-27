<?php
require_once 'config/database.php';
require_once 'config/check_login.php';

// Ambil data produk featured dan popular
$featured_products = [];
$popular_products = [];

try {
    // Featured products
    $featured_query = $conn->query("SELECT * FROM products WHERE featured = 1 LIMIT 4");
    $featured_products = $featured_query->fetch_all(MYSQLI_ASSOC);
    
    // Popular products
    $popular_query = $conn->query("SELECT * FROM products WHERE popular = 1 LIMIT 6");
    $popular_products = $popular_query->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    // Handle error
    error_log("Error fetching products: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hardjadinata Karya Utama</title>
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

        /* --- NEW HEADER TEMA HKU --- */
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

        /* Banner Slider */
        .carousel-item img {
            height: 450px;
            object-fit: cover;
            width: 100%;
        }

        /* Category Section */
        .category-section {
            text-align: center;
            padding: 50px 0;
            background-color: var(--light-gray);
        }

        .category-section h5 {
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 30px;
            text-transform: uppercase;
        }

        .category-list {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 25px;
        }

        .category-box {
            width: 120px;
            height: 120px;
            background-color: white;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--primary-blue);
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid #eee;
        }

        .category-box:hover {
            background-color: var(--primary-blue);
            color: white;
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,56,147,0.2);
        }

        /* Featured Section */
        .featured-section {
            background-color: var(--primary-blue);
            color: white;
            text-align: center;
            padding: 60px 0;
            border-top: 5px solid var(--primary-red);
        }

        .featured-section h5 {
            font-weight: 700;
            margin-bottom: 40px;
            text-transform: uppercase;
        }

        .featured-card {
            background: #fff;
            color: var(--dark-gray);
            border-radius: 8px;
            padding: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            display: block;
        }

        .featured-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }

        .featured-card img {
            width: 100%;
            border-radius: 6px;
            height: 180px;
            object-fit: cover;
        }

        /* Popular Section */
        .popular-section {
            padding: 60px 0;
            text-align: center;
        }

        .popular-section h5 {
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 40px;
            text-transform: uppercase;
        }

        .popular-card {
            background-color: white;
            border-radius: 8px;
            padding: 15px;
            transition: 0.3s;
            cursor: pointer;
            text-decoration: none;
            display: block;
            color: var(--dark-gray);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border: 1px solid #eee;
        }

        .popular-card:hover {
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            transform: translateY(-5px);
            border-color: var(--primary-blue);
        }

        .popular-card img {
            width: 100%;
            border-radius: 6px;
            margin-bottom: 15px;
            height: 200px;
            object-fit: cover;
        }

        .product-price {
            color: var(--primary-red);
            font-weight: 700;
            font-size: 1.1rem;
            margin-top: 10px;
        }

        /* Modal & Form Styles (Tetap dipertahankan dengan update warna) */
        .login-modal, .register-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 34, 85, 0.7); /* Modal background diubah agar nyambung */
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

        /* Footer - Update Warna Selaras */
        .footer {
            background-color: #001f55; /* Biru sangat gelap */
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
            .hku-header-actions {
                display: none; /* Sembunyikan actions di mobile untuk menu hamburger nanti */
            }
            .hku-brand-text h1 { font-size: 20px; }
            .hku-brand-text p { font-size: 12px; }
            .hku-nav-link { padding: 10px 15px; font-size: 13px; }
        }
        @media (max-width: 576px) {
            .hku-brand-section img { height: 45px; }
            .hku-brand-text h1 { font-size: 16px; }
            .hku-nav-container { flex-direction: column; width: 100%; }
            .hku-nav-link { width: 100%; text-align: center; border-bottom: 1px solid #eee; }
            .hku-nav-link.active { border-left: 4px solid var(--primary-red); border-bottom: none; }
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
                                <a class="dropdown-item" href="orders.php"><i class="fas fa-shopping-bag me-2"></i>My Orders</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="logout.php">
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
            <a href="beranda.php" class="hku-nav-link active">BERANDA</a>
            <a href="tentangkami.php" class="hku-nav-link">TENTANG KAMI</a>
            <a href="produk.php" class="hku-nav-link">PRODUK</a>
            <a href="hubungikami.php" class="hku-nav-link">HUBUNGI KAMI</a>
        </div>
    </nav>
</div>
    <section class="banner-section">
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
            <?php 
            $banner_query = $conn->query("SELECT image_url FROM banners WHERE active = 1 ORDER BY created_at DESC LIMIT 3");
            $banners = [];
            if ($banner_query) {
                $banners = $banner_query->fetch_all(MYSQLI_ASSOC);
            }
            ?>
            
            <?php if (!empty($banners)): ?>
                <div class="carousel-indicators">
                    <?php foreach($banners as $index => $banner): ?>
                        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?php echo $index; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>" aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>"></button>
                    <?php endforeach; ?>
                </div>
                <div class="carousel-inner">
                    <?php foreach($banners as $index => $banner): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="adminmegatek/uploads/<?php echo htmlspecialchars($banner['image_url']); ?>" class="d-block w-100" alt="Banner <?php echo $index + 1; ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($banners) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                <?php endif; ?>
            <?php else: ?>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <div class="d-flex align-items-center justify-content-center text-white" style="height: 450px; background-color: var(--primary-blue);">
                            <div class="text-center">
                                <h2>Hardjadinata Karya Utama</h2>
                                <p>Your Trusted Industrial Partner</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="category-section">
        <h5>Kategori Produk</h5>
        <div class="category-list">
            <a href="produk.php?category=Sparepart" class="category-box">
                <i class="fa-solid fa-gear fa-2x mb-3"></i>
                <span>Sparepart</span>
            </a>
            <a href="produk.php?category=FBR Burner" class="category-box">
                <i class="fa-solid fa-fire fa-2x mb-3"></i>
                <span>FBR Burner</span>
            </a>
            <a href="produk.php?category=Boiler" class="category-box">
                <i class="fa-solid fa-industry fa-2x mb-3"></i>
                <span>Boiler</span>
            </a>
            <a href="produk.php?category=<?php echo urlencode('Valve & Instrumentation'); ?>" class="category-box text-center">
                <i class="fa-solid fa-gauge-high fa-2x mb-3"></i>
                <span>Valve &<br>Instrumentation</span>
            </a>
        </div>
    </section>

    <section class="featured-section">
        <h5>Produk Unggulan</h5>
        <div class="container">
            <div class="row justify-content-center">
                <?php if (!empty($featured_products)): ?>
                    <?php foreach ($featured_products as $product): ?>
                        <div class="col-md-3 col-sm-6 mb-4">
                            <a href="detailproduk.php?id=<?php echo $product['id']; ?>" class="featured-card">
                                <img src="uploads/<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <p class="mt-3 mb-1 fw-bold"><?php echo htmlspecialchars($product['name']); ?></p>
                                <div class="product-price">
                                    Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p>No featured products available</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="popular-section">
        <h5>Produk Terpopuler</h5>
        <div class="container">
            <div class="row">
                <?php if (!empty($popular_products)): ?>
                    <?php foreach ($popular_products as $product): ?>
                        <div class="col-md-4 col-sm-6 mb-4">
                            <a href="detailproduk.php?id=<?php echo $product['id']; ?>" class="popular-card">
                                <img src="uploads/<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <p class="fw-bold mb-1 mt-2"><?php echo htmlspecialchars($product['name']); ?></p>
                                <div class="product-price">
                                    Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p>No popular products available</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <div class="login-modal" id="loginModal">
        <div class="login-content">
            <button class="close-btn" id="closeLogin" style="position: absolute; top: 15px; right: 15px; border: none; background: transparent; font-size: 20px;">&times;</button>
            <div class="text-center mb-4">
                <h3 style="color: var(--primary-blue); font-weight: 700;">HKU</h3>
                <p class="text-muted">Surabaya</p>
            </div>
            
            <?php if (isset($_GET['login_required'])): ?>
                <div class="alert alert-info">Please login to continue</div>
            <?php endif; ?>
            
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
        const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');

    if (status === 'success_reg') {
        Swal.fire('Berhasil!', 'Akun Anda telah dibuat. Silakan login.', 'success');
    } else if (status === 'email_taken') {
        Swal.fire('Gagal', 'Email sudah terdaftar.', 'error');
    } else if (status === 'password_mismatch') {
        Swal.fire('Gagal', 'Konfirmasi password tidak cocok.', 'warning');
    } else if (status === 'error') {
        Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
    }
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

        // Update Cart & Active Menu
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
        
        function setActiveMenu() {
            const currentUrl = window.location.href;
            document.querySelectorAll('.hku-nav-link').forEach(item => {
                const href = item.getAttribute('href');
                if (href && currentUrl.includes(href)) item.classList.add('active');
            });
        }

        setInterval(updateCartCount, 30000);
        document.addEventListener('DOMContentLoaded', () => {
            updateCartCount();
            setActiveMenu();
        });
    </script>
</body>
</html>