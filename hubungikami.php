<?php
require_once 'config/database.php';
require_once 'config/check_login.php';
// Kode pemrosesan form dihapus karena halaman ini hanya informasi
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hubungi Kami - Hardjadinata Karya Utama</title>
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

        /* --- CONTACT SPECIFIC STYLES --- */
        .contact-hero {
            background-color: var(--primary-blue);
            color: white;
            padding: 50px 0;
            text-align: center;
            border-bottom: 5px solid var(--primary-red);
        }

        .contact-hero h1 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .contact-hero p {
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
        }

        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 50px 15px;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .contact-info-card {
            background-color: white;
            border-radius: 10px;
            padding: 35px 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            height: 100%;
            border-bottom: 4px solid transparent;
            transition: all 0.3s ease;
        }

        .contact-info-card:hover {
            transform: translateY(-5px);
            border-bottom-color: var(--primary-red);
            box-shadow: 0 8px 25px rgba(0, 56, 147, 0.1);
        }

        .contact-info-card h3 {
            color: var(--primary-blue);
            margin-bottom: 30px;
            font-size: 1.5rem;
            font-weight: 700;
            position: relative;
            padding-bottom: 10px;
            text-transform: uppercase;
        }

        .contact-info-card h3:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: var(--primary-red);
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 25px;
        }

        .contact-icon {
            width: 45px;
            height: 45px;
            background-color: #f0f4f8;
            color: var(--primary-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
            font-size: 1.2rem;
            transition: all 0.3s;
        }

        .contact-item:hover .contact-icon {
            background-color: var(--primary-blue);
            color: white;
        }

        .contact-details h4 {
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: var(--dark-gray);
            font-weight: 600;
        }

        .contact-details p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 3px;
        }

        .contact-details a {
            color: var(--primary-blue);
            text-decoration: none;
            transition: color 0.3s;
            font-weight: 500;
        }

        .contact-details a:hover {
            color: var(--primary-red);
            text-decoration: underline;
        }

        /* Map Section */
        .map-section {
            margin-top: 50px;
        }

        .map-section h3 {
            color: var(--primary-blue);
            margin-bottom: 20px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .map-container {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            height: 450px;
            border: 1px solid #eee;
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

        /* Responsive */
        @media (max-width: 992px) {
            .hku-header-actions { display: none; }
            .hku-brand-text h1 { font-size: 20px; }
            .hku-nav-link { padding: 10px 15px; font-size: 13px; }
        }
        @media (max-width: 768px) {
            .contact-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 576px) {
            .hku-brand-section img { height: 45px; }
            .hku-brand-text h1 { font-size: 16px; }
            .hku-nav-container { flex-direction: column; width: 100%; }
            .hku-nav-link { width: 100%; text-align: center; border-bottom: 1px solid #eee; }
            .hku-nav-link.active { border-left: 4px solid var(--primary-red); border-bottom: none; }
            .contact-hero h1 { font-size: 1.8rem; }
            .contact-container { padding: 30px 15px; }
            .map-container { height: 300px; }
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
            <a href="produk.php" class="hku-nav-link">PRODUK</a>
            <a href="hubungikami.php" class="hku-nav-link active">HUBUNGI KAMI</a>
        </div>
    </nav>

    <section class="contact-hero">
        <div class="container">
            <h1>Hubungi Kami</h1>
            <p>Kami siap membantu Anda. Hubungi tim kami untuk pertanyaan, dukungan, atau informasi lebih lanjut tentang produk dan layanan kami.</p>
        </div>
    </section>

    <div class="contact-container">
        <div class="contact-grid">
            
            <div class="contact-info-card">
                <h3>Lokasi & Kontak</h3>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-details">
                        <h4>Alamat Kantor</h4>
                        <p>PT. Hardjadinata Karya Utama</p>
                        <p>Jl. Raya Industri No. 123</p>
                        <p>Surabaya, Jawa Timur 60293</p>
                        <p>Indonesia</p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="contact-details">
                        <h4>Telepon & WhatsApp</h4>
                        <p>+62 31 1234 5678 (Office)</p>
                        <p>+62 812 3456 7890 (WhatsApp)</p>
                    </div>
                </div>
            </div>
            
            <div class="contact-info-card">
                <h3>Layanan Pelanggan</h3>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-details">
                        <h4>Email</h4>
                        <p><a href="mailto:info@hku.co.id">info@hku.co.id</a></p>
                        <p><a href="mailto:sales@hku.co.id">sales@hku.co.id</a></p>
                        <p><a href="mailto:support@hku.co.id">support@hku.co.id</a></p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="contact-details">
                        <h4>Jam Operasional</h4>
                        <p>Senin - Jumat: 08:00 - 17:00 WIB</p>
                        <p>Sabtu: 08:00 - 12:00 WIB</p>
                        <p>Minggu & Hari Libur: Tutup</p>
                    </div>
                </div>
            </div>
            
        </div> 

        <div class="map-section">
            <h3>Peta Lokasi</h3>
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126646.20963363365!2d112.63004552467385!3d-7.275614138138769!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7fbf8381ac47f%3A0x3027a76e352be40!2sSurabaya%2C%20Surabaya%20City%2C%20East%20Java!5e0!3m2!1sen!2sid!4v1700000000000!5m2!1sen!2sid" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
        
    </div>

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
                        <li><a href="hubungikami.php">Hubungi Kami</a></li>
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

        // Cart link
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