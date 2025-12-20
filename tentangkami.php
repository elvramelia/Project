<?php
require_once 'config/database.php';
require_once 'config/check_login.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Megatek Industrial Persada</title>
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

        /* About Us Hero Section */
        .about-hero {
            background-color: var(--primary-blue);
            height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.4rem;
            margin-top: 0;
        }

        .about-hero h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .about-hero p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 30px;
            opacity: 0.9;
        }

        /* Company Overview */
        .company-overview {
            padding: 80px 0;
            background-color: white;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
            color: var(--primary-blue);
        }

        .section-title h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }

        .section-title h2:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background-color: var(--primary-blue);
        }

        .company-content {
            display: flex;
            align-items: center;
            gap: 50px;
            margin-bottom: 50px;
        }

        .company-logo {
            flex: 0 0 300px;
        }

        .company-logo img {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .company-text {
            flex: 1;
        }

        .company-text h3 {
            color: var(--primary-blue);
            margin-bottom: 20px;
            font-size: 1.8rem;
        }

        .company-text p {
            line-height: 1.8;
            color: #555;
            margin-bottom: 20px;
        }

        /* Vision Mission */
        .vision-mission {
            padding: 80px 0;
            background-color: #f8f9fb;
        }

        .vm-container {
            display: flex;
            gap: 30px;
            margin-top: 40px;
        }

        .vm-card {
            flex: 1;
            background: white;
            padding: 40px 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            text-align: center;
            transition: transform 0.3s;
        }

        .vm-card:hover {
            transform: translateY(-10px);
        }

        .vm-icon {
            width: 70px;
            height: 70px;
            background: var(--primary-blue);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 1.8rem;
        }

        .vm-card h3 {
            color: var(--primary-blue);
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .vm-card p {
            color: #555;
            line-height: 1.7;
        }

        /* Values */
        .company-values {
            padding: 80px 0;
            background-color: white;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .value-card {
            background: #f8f9fb;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            border-left: 4px solid var(--primary-blue);
            transition: all 0.3s;
        }

        .value-card:hover {
            background: var(--primary-blue);
            color: white;
            transform: translateY(-5px);
        }

        .value-card:hover h4,
        .value-card:hover .value-icon {
            color: white;
        }

        .value-icon {
            font-size: 2rem;
            color: var(--primary-blue);
            margin-bottom: 20px;
        }

        .value-card h4 {
            color: var(--primary-blue);
            margin-bottom: 15px;
            font-size: 1.3rem;
        }

        .value-card p {
            line-height: 1.6;
        }

        /* Team Section */
        .team-section {
            padding: 80px 0;
            background-color: #f8f9fb;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .team-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }

        .team-card:hover {
            transform: translateY(-10px);
        }

        .team-img {
            height: 250px;
            overflow: hidden;
        }

        .team-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .team-card:hover .team-img img {
            transform: scale(1.1);
        }

        .team-info {
            padding: 25px;
            text-align: center;
        }

        .team-info h4 {
            color: var(--primary-blue);
            margin-bottom: 5px;
            font-size: 1.3rem;
        }

        .team-info .position {
            color: #666;
            font-weight: 500;
            margin-bottom: 15px;
        }

        .team-info p {
            color: #555;
            line-height: 1.6;
            font-size: 0.95rem;
        }

        /* Stats Section */
        .stats-section {
            padding: 80px 0;
            background-color: var(--primary-blue);
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            text-align: center;
        }

        .stat-item h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .stat-item p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* CTA Section */
        .cta-section {
            padding: 80px 0;
            background-color: white;
            text-align: center;
        }

        .cta-content {
            max-width: 700px;
            margin: 0 auto;
        }

        .cta-content h2 {
            color: var(--primary-blue);
            margin-bottom: 20px;
            font-size: 2rem;
        }

        .cta-content p {
            color: #555;
            margin-bottom: 30px;
            line-height: 1.7;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary, .btn-secondary {
            padding: 12px 30px;
            border-radius: 5px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: var(--primary-blue);
            color: white;
            border: 2px solid var(--primary-blue);
        }

        .btn-primary:hover {
            background-color: #153a6e;
            border-color: #153a6e;
            color: white;
            text-decoration: none;
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--primary-blue);
            border: 2px solid var(--primary-blue);
        }

        .btn-secondary:hover {
            background-color: var(--primary-blue);
            color: white;
            text-decoration: none;
        }

        /* Modal Styles (same as beranda.php) */
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
            
            .search-bar {
                max-width: 200px;
            }
            
            .nav-icon span {
                display: none;
            }
            
            .nav-icon {
                min-width: auto;
            }
            
            .company-content {
                flex-direction: column;
                text-align: center;
            }
            
            .company-logo {
                flex: 0 0 auto;
                max-width: 300px;
                margin: 0 auto;
            }
            
            .vm-container {
                flex-direction: column;
            }
            
            .values-grid {
                grid-template-columns: 1fr;
            }
            
            .team-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
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
            
            .about-hero h1 {
                font-size: 2rem;
            }
            
            .section-title h2 {
                font-size: 1.8rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
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


    <!-- About Us Hero Section -->
    <section class="about-hero">
        <div class="container">
            <h1>Tentang Kami</h1>
            <p>Sebagai perusahaan terdepan dalam penyediaan solusi industri, kami berkomitmen untuk memberikan produk dan layanan berkualitas tinggi sejak 2010.</p>
    
        </div>
    </section>

    <!-- Company Overview -->
    <section id="company-overview" class="company-overview">
        <div class="container">
            <div class="section-title">
                <h2>Profil Perusahaan</h2>
                <p>Mengenal lebih dekat dengan Megatek Industrial Persada</p>
            </div>
            
            <div class="company-content">
                <div class="company-logo">
                    <img src="gambar/LOGO.png" alt="Megatek Logo">
                </div>
                <div class="company-text">
                    <h3>PT. Megatek Industrial Persada</h3>
                    <p>Didirikan pada tahun 2010 di Surabaya, Indonesia, Megatek Industrial Persada telah tumbuh menjadi salah satu pemain utama dalam industri penyediaan peralatan dan komponen industri di wilayah Jawa Timur dan sekitarnya.</p>
                    <p>Kami mengkhususkan diri dalam penyediaan produk-produk berkualitas tinggi untuk berbagai kebutuhan industri, mulai dari spare part mesin, burner series, boiler, hingga valve dan instrumentation.</p>
                    <p>Dengan pengalaman lebih dari satu dekade, kami telah membangun reputasi yang solid sebagai mitra bisnis yang dapat diandalkan, dengan fokus pada kualitas produk, layanan pelanggan yang prima, dan solusi yang inovatif.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Vision & Mission -->
    <section class="vision-mission">
        <div class="container">
            <div class="section-title">
                <h2>Visi & Misi</h2>
                <p>Arah dan tujuan perusahaan kami</p>
            </div>
            
            <div class="vm-container">
                <div class="vm-card">
                    <div class="vm-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3>Visi</h3>
                    <p>Menjadi perusahaan penyedia solusi industri terkemuka di Indonesia dengan produk berkualitas internasional dan layanan yang terpercaya, mendukung pertumbuhan industri nasional.</p>
                </div>
                
                <div class="vm-card">
                    <div class="vm-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3>Misi</h3>
                    <p>1. Menyediakan produk industri berkualitas tinggi dengan standar internasional<br>
                       2. Memberikan solusi yang inovatif dan efisien untuk kebutuhan industri<br>
                       3. Membangun hubungan jangka panjang dengan pelanggan melalui layanan yang unggul<br>
                       4. Berkontribusi pada pengembangan industri nasional yang berkelanjutan</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Company Values -->
    <section class="company-values">
        <div class="container">
            <div class="section-title">
                <h2>Nilai-nilai Perusahaan</h2>
                <p>Prinsip yang kami pegang teguh dalam setiap aspek bisnis</p>
            </div>
            
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-medal"></i>
                    </div>
                    <h4>Kualitas</h4>
                    <p>Kami hanya menyediakan produk dengan standar kualitas tertinggi untuk memastikan kepuasan dan keamanan pelanggan.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h4>Integritas</h4>
                    <p>Kejujuran dan transparansi dalam setiap transaksi adalah landasan utama hubungan bisnis kami dengan mitra dan pelanggan.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4>Kolaborasi</h4>
                    <p>Kami percaya bahwa kerja sama tim dan kemitraan yang solid adalah kunci keberhasilan bersama dengan seluruh stakeholder.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h4>Inovasi</h4>
                    <p>Terus berinovasi dalam produk dan layanan untuk memenuhi tantangan industri yang terus berkembang.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <div class="section-title">
                <h2>Tim Kami</h2>
                <p>Orang-orang di balik kesuksesan Megatek</p>
            </div>
            
            <div class="team-grid">
                <div class="team-card">
                    <div class="team-img">
                        <img src="uploads/team/team1.jpg" alt="CEO Megatek">
                    </div>
                    <div class="team-info">
                        <h4>Budi Santoso</h4>
                        <div class="position">CEO & Founder</div>
                        <p>Memiliki pengalaman lebih dari 20 tahun di industri permesinan dan engineering. Memimpin perusahaan dengan visi strategis untuk ekspansi pasar.</p>
                    </div>
                </div>
                
                <div class="team-card">
                    <div class="team-img">
                        <img src="uploads/team/team2.jpg" alt="Technical Director">
                    </div>
                    <div class="team-info">
                        <h4>Agus Wijaya</h4>
                        <div class="position">Technical Director</div>
                        <p>Spesialis dalam bidang boiler dan burner dengan sertifikasi internasional. Bertanggung jawab atas kualitas teknis semua produk.</p>
                    </div>
                </div>
                
                <div class="team-card">
                    <div class="team-img">
                        <img src="uploads/team/team3.jpg" alt="Sales Manager">
                    </div>
                    <div class="team-info">
                        <h4>Sari Dewi</h4>
                        <div class="position">Sales Manager</div>
                        <p>Memimpin tim penjualan dengan fokus pada pembangunan hubungan jangka panjang dengan klien korporat dan industri.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <h3>13+</h3>
                    <p>Tahun Pengalaman</p>
                </div>
                
                <div class="stat-item">
                    <h3>500+</h3>
                    <p>Klien Industri</p>
                </div>
                
                <div class="stat-item">
                    <h3>2000+</h3>
                    <p>Produk Tersedia</p>
                </div>
                
                <div class="stat-item">
                    <h3>50+</h3>
                    <p>Tim Profesional</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Siap Bermitra dengan Kami?</h2>
                <p>Dapatkan solusi industri terbaik untuk kebutuhan bisnis Anda. Hubungi kami sekarang untuk konsultasi gratis dan penawaran produk yang sesuai dengan kebutuhan spesifik Anda.</p>
                <div class="cta-buttons">
                    <a href="contact.php" class="btn-primary">Hubungi Kami</a>
                    <a href="produk.php" class="btn-secondary">Lihat Produk</a>
                </div>
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

        // Set active menu
        function setActiveMenu() {
            const menuItems = document.querySelectorAll('.menu-category');
            const currentPage = 'aboutus.php';
            
            menuItems.forEach(item => {
                const href = item.getAttribute('href');
                if (href && href.includes(currentPage)) {
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
            
            // Highlight About Us in menu
            const menuLinks = document.querySelectorAll('.menu-category');
            menuLinks.forEach(link => {
                if (link.textContent.includes('About') || link.getAttribute('href') === 'aboutus.php') {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>