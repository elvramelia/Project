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
    <title>Megatek Industrial Persada</title>
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
            flex-wrap: wrap;
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

        /* Banner */
        .banner-master {
          /* Container pembungkus banner */
.banner-container {
    width: 100%;           /* Lebar penuh */
    max-width: 1200px;     /* Maksimal lebar agar tidak terlalu lebar di layar besar */
    margin: 20px auto;     /* Posisi tengah (center) */
    overflow: hidden;      /* Mencegah gambar keluar batas */
    border-radius: 8px;    /* Opsional: Membuat sudut melengkung supaya cantik */
    box-shadow: 0 4px 6px rgba(0,0,0,0.1); /* Opsional: Bayangan halus */
}

/* Gambar banner itu sendiri */
.banner-image {
    width: 100%;           /* Memaksa lebar gambar mengikuti container */
    height: auto;          /* Tinggi menyesuaikan proporsi (agar tidak gepeng) */
    display: block;        /* Menghilangkan celah putih kecil di bawah gambar */
    object-fit: cover;     /* Memastikan gambar mengisi area dengan rapi */
}
        }

        /* Category Section */
        .category-section {
            text-align: center;
            padding: 40px 0;
            background-color: #f8f9fb;
        }

        .category-section h5 {
            font-weight: 600;
            margin-bottom: 25px;
        }

        .category-list {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 25px;
        }

        .category-box {
            width: 100px;
            height: 100px;
            background-color: #e9ecef;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--primary-blue);
            font-size: 13px;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
        }

        .category-box:hover {
            background-color: var(--primary-blue);
            color: white;
            transform: translateY(-3px);
            text-decoration: none;
        }

        /* Featured Section */
        .featured-section {
            background-color: var(--primary-blue);
            color: white;
            text-align: center;
            padding: 50px 0;
        }

        .featured-section h5 {
            font-weight: 600;
            margin-bottom: 35px;
        }

        .featured-card {
            background: #fff;
            color: #000;
            border-radius: 10px;
            padding: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            display: block;
        }

        .featured-card:hover {
            text-decoration: none;
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .featured-card img {
            width: 100%;
            border-radius: 8px;
            height: 180px;
            object-fit: cover;
        }

        /* Popular Section */
        .popular-section {
            padding: 60px 0;
            text-align: center;
        }

        .popular-section h5 {
            font-weight: 600;
            margin-bottom: 30px;
        }

        .popular-card {
            background-color: white;
            border-radius: 10px;
            padding: 15px;
            transition: 0.3s;
            cursor: pointer;
            text-decoration: none;
            display: block;
            color: var(--dark-gray);
        }

        .popular-card:hover {
            text-decoration: none;
            color: var(--dark-gray);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-5px);
        }

        .popular-card img {
            width: 100%;
            border-radius: 10px;
            margin-bottom: 10px;
            height: 200px;
            object-fit: cover;
        }

        .product-price {
            color: var(--primary-blue);
            font-weight: 600;
            font-size: 1.1rem;
            margin-top: 10px;
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

        .alert {
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .name-row {
            display: flex;
            gap: 15px;
        }

        .name-row .form-group {
            flex: 1;
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
        @media (max-width: 768px) {
            .top-bar {
                display: none;
            }
            
            .main-menu {
                display: none;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .search-bar {
                max-width: 200px;
            }
            
            .category-list {
                gap: 15px;
            }
            
            .category-box {
                width: 85px;
                height: 85px;
                font-size: 12px;
            }
            
            .nav-icon span {
                display: none;
            }
            
            .nav-icon {
                min-width: auto;
            }
        }

        @media (max-width: 576px) {
            .nav-icons {
                gap: 10px;
            }
            
            .search-bar {
                max-width: 150px;
            }
            
            .banner-master {
                height: 180px;
                font-size: 1.2rem;
            }
            
            .navbar-brand img {
                height: 30px;
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
                <span>Keranjang</span>
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
           <a href="beranda.php" class="menu-category">Beranda</a>
            <a href="tentangkami.php" class="menu-category">Tentang Kami</a>
            <a href="produk.php" class="menu-category">Produk</a>
            <a href="hubungikami.php" class="menu-category">Hubungi Kami</a>
            
        </div>
    </div>

    <!-- Banner -->
    <section class="banner-master">
        <div class="banner-container">
            <?php 
            $banner_query = $conn->query("SELECT image_url FROM banners WHERE active = 1 ORDER BY created_at DESC LIMIT 1");
            $banner = $banner_query->fetch_assoc();
            
            if ($banner && !empty($banner['image_url'])): ?>
                <img src="adminmegatek/uploads/<?php echo htmlspecialchars($banner['image_url']); ?>" 
                     alt="Banner" 
                     class="banner-image">
            <?php else: ?>
                <div class="banner-placeholder">
                    <h2>Megatek Industrial Persada</h2>
                    <p>Your Trusted Industrial Partner</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Category -->
    <section class="category-section">
        <h5>Category Product</h5>
        <div class="category-list">
            <a href="produk.php?category=Sparepart" class="category-box">
                <i class="fa-solid fa-gear fa-2x mb-2"></i>
                <span>Sparepart</span>
            </a>
            <a href="produk.php?category=FBR Burner" class="category-box">
                <i class="fa-solid fa-fire fa-2x mb-2"></i>
                <span>FBR Burner</span>
            </a>
            <a href="produk.php?category=Boiler" class="category-box">
                <i class="fa-solid fa-industry fa-2x mb-2"></i>
                <span>Boiler</span>
            </a>
            <a href="produk.php?category=Valve & Instrumentation" class="category-box">
                <i class="fa-solid fa-gauge-high fa-2x mb-2"></i>
                <span>Valve & Instrumentation</span>
            </a>
        </div>
    </section>

    <!-- Featured -->
    <section class="featured-section">
        <h5>Featured Product</h5>
        <div class="container">
            <div class="row justify-content-center">
                <?php if (!empty($featured_products)): ?>
                    <?php foreach ($featured_products as $product): ?>
                        <div class="col-md-3 col-sm-6 mb-4">
                            <a href="detailproduk.php?id=<?php echo $product['id']; ?>" class="featured-card">
                                <img src="uploads/<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <p class="mt-2"><?php echo htmlspecialchars($product['name']); ?></p>
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

    <!-- Popular -->
    <section class="popular-section">
        <h5>Popular Product</h5>
        <div class="container">
            <div class="row">
                <?php if (!empty($popular_products)): ?>
                    <?php foreach ($popular_products as $product): ?>
                        <div class="col-md-4 col-sm-6 mb-4">
                            <a href="detailproduk.php?id=<?php echo $product['id']; ?>" class="popular-card">
                                <img src="uploads/<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <p><?php echo htmlspecialchars($product['name']); ?></p>
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

    <!-- Login Modal -->
    <div class="login-modal" id="loginModal">
        <div class="login-content">
            <button class="close-btn" id="closeLogin">&times;</button>
            <div class="login-header">
                <h3>Megatek Industrial Persada</h3>
                <p>Surabaya</p>
            </div>
            
            <?php if (isset($_GET['login_required'])): ?>
                <div class="alert alert-info">
                    Please login to continue to cart
                </div>
            <?php endif; ?>
            
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
                    <img src="gambar/LOGO.png" alt="Megatek Logo" class="footer-logo">
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

        // Check if login required message should be shown
        <?php if (isset($_GET['login_required'])): ?>
            loginModal.style.display = 'flex';
        <?php endif; ?>

        // Login Form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            // Create AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'login.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        
                        if (response.success) {
                            // Login successful
                            alert('Login successful! Welcome ' + response.user.first_name);
                            loginModal.style.display = 'none';
                            // Reload page to update UI
                            location.reload();
                        } else {
                            // Login failed
                            alert('Login failed: ' + response.message);
                        }
                    } catch (e) {
                        alert('Error parsing response: ' + e.message);
                    }
                } else {
                    alert('Server error: ' + xhr.status);
                }
            };
            
            xhr.onerror = function() {
                alert('Network error occurred');
            };
            
            // Send the request
            xhr.send('email=' + encodeURIComponent(email) + '&password=' + encodeURIComponent(password));
        });
        
        // Register Form submission
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('reg_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Check if passwords match
            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return;
            }
            
            if (password.length < 6) {
                alert('Password must be at least 6 characters long!');
                return;
            }
            
            // Get form data
            const formData = new FormData(document.getElementById('registerForm'));
            
            // Create AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'register.php', true);
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        
                        if (response.success) {
                            // Registration successful
                            alert('Registration successful! Welcome ' + response.user.first_name);
                            registerModal.style.display = 'none';
                            // Reload page to update UI
                            location.reload();
                        } else {
                            // Registration failed
                            alert('Registration failed: ' + response.message);
                        }
                    } catch (e) {
                        alert('Error parsing response: ' + e.message);
                    }
                } else {
                    alert('Server error: ' + xhr.status);
                }
            };
            
            xhr.onerror = function() {
                alert('Network error occurred');
            };
            
            // Send the request
            xhr.send(formData);
        });

        // Cart link click handler
        document.getElementById('cartLink').addEventListener('click', function(e) {
            e.preventDefault();
            
            <?php if (isLoggedIn()): ?>
                // User is logged in, redirect to cart
                window.location.href = 'cart.php';
            <?php else: ?>
                // User is not logged in, show login modal
                loginModal.style.display = 'flex';
            <?php endif; ?>
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

        // Update cart count periodically
        function updateCartCount() {
            <?php if (isLoggedIn()): ?>
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
            <?php endif; ?>
        }

        // Set active menu based on current page
        function setActiveMenu() {
            const currentUrl = window.location.href;
            const menuItems = document.querySelectorAll('.menu-category');
            
            menuItems.forEach(item => {
                const href = item.getAttribute('href');
                if (href && currentUrl.includes(href)) {
                    item.classList.add('active');
                }
            });
        }

        // Update cart count every 30 seconds
        setInterval(updateCartCount, 30000);

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
            setActiveMenu();
        });
    </script>
</body>
</html>