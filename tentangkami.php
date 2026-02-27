<?php
require_once 'config/database.php';
require_once 'config/check_login.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Hardjadinata Karya Utama</title>
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

        /* User dropdown & Cart Badge */
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

        /* --- ABOUT US SPECIFIC STYLES --- */
        .about-hero {
            background-color: var(--primary-blue);
            color: white;
            padding: 60px 0;
            text-align: center;
            border-bottom: 5px solid var(--primary-red);
        }

        .about-hero h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .about-hero p {
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
            line-height: 1.6;
        }

        .company-overview {
            padding: 80px 0;
            background-color: white;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title h2 {
            color: var(--primary-blue);
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
            text-transform: uppercase;
        }

        .section-title h2:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background-color: var(--primary-red);
        }

        .company-content {
            display: flex;
            align-items: center;
            gap: 50px;
            margin-bottom: 50px;
        }

        .company-logo {
            flex: 0 0 350px;
            text-align: center;
        }

        .company-logo img {
            width: 100%;
            max-width: 300px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 56, 147, 0.15);
            padding: 20px;
            background: white;
            border: 1px solid #eee;
        }

        .company-text {
            flex: 1;
        }

        .company-text h3 {
            color: var(--primary-blue);
            margin-bottom: 20px;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .company-text p {
            line-height: 1.8;
            color: #555;
            margin-bottom: 15px;
            font-size: 1.05rem;
        }

        .vision-mission {
            padding: 80px 0;
            background-color: var(--light-gray);
        }

        .vm-container {
            display: flex;
            gap: 30px;
            margin-top: 40px;
        }

        .vm-card {
            flex: 1;
            background: white;
            padding: 50px 40px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            text-align: center;
            transition: transform 0.3s;
            border-top: 5px solid transparent;
        }

        .vm-card:hover {
            transform: translateY(-10px);
            border-top-color: var(--primary-red);
        }

        .vm-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-blue);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 2rem;
            box-shadow: 0 5px 15px rgba(0, 56, 147, 0.3);
        }

        .vm-card h3 {
            color: var(--primary-blue);
            margin-bottom: 20px;
            font-size: 1.6rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .vm-card p {
            color: #555;
            line-height: 1.8;
            font-size: 1.05rem;
        }

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
            background: var(--light-gray);
            padding: 40px 30px;
            border-radius: 10px;
            text-align: center;
            border-bottom: 4px solid var(--primary-blue);
            transition: all 0.3s;
        }

        .value-card:hover {
            background: var(--primary-blue);
            color: white;
            transform: translateY(-5px);
            border-bottom-color: var(--primary-red);
        }

        .value-card:hover h4,
        .value-card:hover p,
        .value-card:hover .value-icon {
            color: white;
        }

        .value-icon {
            font-size: 2.5rem;
            color: var(--primary-red);
            margin-bottom: 20px;
            transition: color 0.3s;
        }

        .value-card h4 {
            color: var(--primary-blue);
            margin-bottom: 15px;
            font-size: 1.3rem;
            font-weight: 700;
            transition: color 0.3s;
        }

        .value-card p {
            line-height: 1.6;
            color: #555;
            transition: color 0.3s;
        }

        .stats-section {
            padding: 70px 0;
            background-color: var(--primary-blue);
            color: white;
            border-top: 5px solid var(--primary-red);
            border-bottom: 5px solid var(--primary-red);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            text-align: center;
        }

        .stat-item h3 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 10px;
            color: white;
        }

        .stat-item p {
            font-size: 1.1rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
            margin: 0;
        }

        .cta-section {
            padding: 90px 0;
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
            font-size: 2.2rem;
            font-weight: 800;
        }

        .cta-content p {
            color: #555;
            margin-bottom: 35px;
            line-height: 1.8;
            font-size: 1.1rem;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary-hku {
            background-color: var(--primary-red);
            color: white;
            padding: 12px 35px;
            border-radius: 5px;
            font-weight: 700;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            text-transform: uppercase;
            border: 2px solid var(--primary-red);
        }

        .btn-primary-hku:hover {
            background-color: #c00510;
            border-color: #c00510;
            color: white;
        }

        .btn-secondary-hku {
            background-color: transparent;
            color: var(--primary-blue);
            padding: 12px 35px;
            border-radius: 5px;
            font-weight: 700;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            text-transform: uppercase;
            border: 2px solid var(--primary-blue);
        }

        .btn-secondary-hku:hover {
            background-color: var(--primary-blue);
            color: white;
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
            margin-top: 0; /* Menghilangkan gap karena CTA putih */
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

        /* Responsive */
        @media (max-width: 992px) {
            .hku-header-actions { display: none; }
            .hku-brand-text h1 { font-size: 20px; }
            .hku-nav-link { padding: 10px 15px; font-size: 13px; }
            .company-content { flex-direction: column; text-align: center; }
            .vm-container { flex-direction: column; }
        }
        @media (max-width: 576px) {
            .hku-brand-section img { height: 45px; }
            .hku-brand-text h1 { font-size: 16px; }
            .hku-nav-container { flex-direction: column; width: 100%; }
            .hku-nav-link { width: 100%; text-align: center; border-bottom: 1px solid #eee; }
            .hku-nav-link.active { border-left: 4px solid var(--primary-red); border-bottom: none; }
            .about-hero h1 { font-size: 2rem; }
            .section-title h2 { font-size: 1.8rem; }
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
            <a href="tentangkami.php" class="hku-nav-link active">TENTANG KAMI</a>
            <a href="produk.php" class="hku-nav-link">PRODUK</a>
            <a href="hubungikami.php" class="hku-nav-link">HUBUNGI KAMI</a>
        </div>
    </nav>
                    </div>
    <section class="about-hero">
        <div class="container">
            <h1>Tentang Kami</h1>
            <p>Sebagai perusahaan terdepan dalam penyediaan solusi industri, kami berkomitmen untuk memberikan produk dan layanan berkualitas tinggi untuk memajukan bisnis Anda.</p>
        </div>
    </section>

    <section id="company-overview" class="company-overview">
        <div class="container">
            <div class="section-title">
                <h2>Profil Perusahaan</h2>
                <p class="text-muted">Mengenal lebih dekat dengan Hardjadinata Karya Utama</p>
            </div>
            
            <div class="company-content">
                <div class="company-logo">
                    <img src="uploads/logoHKU.png" alt="HKU Logo">
                </div>
                <div class="company-text">
                    <h3>PT. Hardjadinata Karya Utama</h3>
                    <p>Didirikan di Surabaya, Indonesia, <strong>Hardjadinata Karya Utama (HKU)</strong> telah tumbuh menjadi salah satu pemain utama dalam industri penyediaan peralatan dan komponen industri di wilayah Jawa Timur dan sekitarnya.</p>
                    <p>Kami mengkhususkan diri dalam penyediaan produk-produk berkualitas tinggi untuk berbagai kebutuhan industri, mulai dari spare part mesin, burner series, boiler, hingga valve dan instrumentation.</p>
                    <p>Dengan dedikasi yang tinggi, kami telah membangun reputasi yang solid sebagai mitra bisnis yang dapat diandalkan, dengan fokus pada kualitas produk, layanan pelanggan yang prima, dan solusi yang inovatif.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="vision-mission">
        <div class="container">
            <div class="section-title">
                <h2>Visi & Misi</h2>
                <p class="text-muted">Arah dan tujuan perusahaan kami</p>
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
                    <p class="text-start">
                        1. Menyediakan produk industri berkualitas tinggi dengan standar internasional.<br><br>
                        2. Memberikan solusi yang inovatif dan efisien untuk kebutuhan industri.<br><br>
                        3. Membangun hubungan jangka panjang dengan pelanggan melalui layanan yang unggul.<br><br>
                        4. Berkontribusi pada pengembangan industri nasional yang berkelanjutan.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="company-values">
        <div class="container">
            <div class="section-title">
                <h2>Nilai-nilai Perusahaan</h2>
                <p class="text-muted">Prinsip yang kami pegang teguh dalam setiap aspek bisnis</p>
            </div>
            
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-medal"></i></div>
                    <h4>Kualitas</h4>
                    <p>Kami hanya menyediakan produk dengan standar kualitas tertinggi untuk memastikan kepuasan dan keamanan pelanggan.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-handshake"></i></div>
                    <h4>Integritas</h4>
                    <p>Kejujuran dan transparansi dalam setiap transaksi adalah landasan utama hubungan bisnis kami.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-users"></i></div>
                    <h4>Kolaborasi</h4>
                    <p>Kami percaya bahwa kerja sama tim dan kemitraan yang solid adalah kunci keberhasilan bersama.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-lightbulb"></i></div>
                    <h4>Inovasi</h4>
                    <p>Terus berinovasi dalam produk dan layanan untuk memenuhi tantangan industri yang terus berkembang.</p>
                </div>
            </div>
        </div>
    </section>

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

    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Siap Bermitra dengan Kami?</h2>
                <p>Dapatkan solusi industri terbaik untuk kebutuhan bisnis Anda. Hubungi kami sekarang untuk konsultasi gratis dan penawaran produk yang sesuai dengan spesifikasi Anda.</p>
                <div class="cta-buttons">
                    <a href="hubungikami.php" class="btn-primary-hku">Hubungi Kami</a>
                    <a href="produk.php" class="btn-secondary-hku">Lihat Produk</a>
                </div>
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

        // Update Cart 
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