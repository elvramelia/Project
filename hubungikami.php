<?php
require_once 'config/database.php';
require_once 'config/check_login.php';

// Handle contact form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Nama harus diisi';
    }
    
    if (empty($email)) {
        $errors[] = 'Email harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email tidak valid';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subjek harus diisi';
    }
    
    if (empty($message)) {
        $errors[] = 'Pesan harus diisi';
    }
    
    if (empty($errors)) {
        try {
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
            
            if ($stmt->execute()) {
                $success_message = 'Pesan Anda telah berhasil dikirim. Kami akan menghubungi Anda dalam waktu 1-2 hari kerja.';
                
                // Clear form
                $name = $email = $phone = $subject = $message = '';
                
                // Optionally send email notification
                // mail('admin@megatek.co.id', 'New Contact Message: ' . $subject, $message);
            } else {
                $error_message = 'Terjadi kesalahan saat mengirim pesan. Silakan coba lagi.';
            }
        } catch (Exception $e) {
            $error_message = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hubungi Kami - Megatek Industrial Persada</title>
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

        /* Contact Hero Section */
        .contact-hero {
            background-color: var(--primary-blue);
            color: white;
            padding: 40px 0;
            text-align: center;
        }

        .contact-hero h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .contact-hero p {
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
        }

        /* Contact Container */
        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 15px;
        }

        /* Contact Grid */
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        /* Contact Info Card */
        .contact-info-card {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            height: 100%;
        }

        .contact-info-card h3 {
            color: var(--primary-blue);
            margin-bottom: 25px;
            font-size: 1.5rem;
            position: relative;
            padding-bottom: 10px;
        }

        .contact-info-card h3:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary-blue);
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 25px;
            transition: transform 0.3s;
        }

        .contact-item:hover {
            transform: translateX(5px);
        }

        .contact-icon {
            width: 40px;
            height: 40px;
            background-color: #f0f7ff;
            color: var(--primary-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .contact-details h4 {
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: var(--dark-gray);
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
        }

        .contact-details a:hover {
            color: #153a6e;
            text-decoration: underline;
        }

        /* Contact Form */
        .contact-form-container {
            background-color: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .contact-form-container h3 {
            color: var(--primary-blue);
            margin-bottom: 25px;
            font-size: 1.5rem;
            position: relative;
            padding-bottom: 10px;
        }

        .contact-form-container h3:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary-blue);
        }

        .contact-form-container p {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-gray);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        .form-control:focus {
            border-color: var(--primary-blue);
            outline: none;
            box-shadow: 0 0 0 2px rgba(26, 75, 140, 0.2);
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }

        .btn-submit {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 16px;
            display: inline-block;
        }

        .btn-submit:hover {
            background-color: #153a6e;
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

        /* Map Section */
        .map-section {
            margin-top: 50px;
        }

        .map-container {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            height: 400px;
        }

        .map-placeholder {
            width: 100%;
            height: 100%;
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #666;
        }

        .map-placeholder i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #ddd;
        }

        /* FAQ Section */
        .faq-section {
            margin-top: 60px;
        }

        .faq-title {
            text-align: center;
            margin-bottom: 40px;
            color: var(--primary-blue);
        }

        .faq-title h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .faq-title p {
            color: #666;
            max-width: 700px;
            margin: 0 auto;
        }

        .faq-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .faq-item {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }

        .faq-item:hover {
            transform: translateY(-5px);
        }

        .faq-item h4 {
            color: var(--primary-blue);
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .faq-item p {
            color: #666;
            line-height: 1.6;
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
            
            .contact-grid {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .contact-form-container {
                padding: 25px;
            }
            
            .contact-info-card {
                padding: 25px;
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
            
            .contact-hero h1 {
                font-size: 1.8rem;
            }
            
            .contact-container {
                padding: 30px 15px;
            }
            
            .faq-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- Main Navbar -->
    <nav class="navbar d-flex align-items-center">
        <a class="navbar-brand mx-2" href="beranda.php">
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
            <a href="beranda.php" class="menu-category <?php echo basename($_SERVER['PHP_SELF']) == 'produk.php' ? 'active' : ''; ?>">
                <span>Beranda</span>
            </a>
            <a href="tentangkami.php" class="menu-category">Tentang Kami</a>
            <a href="produk.php" class="menu-category">Produk</a>
            <a href="hubungikami.php" class="menu-category">Hubungi Kami</a>
            <a href="promo.php" class="menu-category">Promo</a>
        </div>
    </div>


    <!-- Contact Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <h1>Hubungi Kami</h1>
            <p>Kami siap membantu Anda. Hubungi tim kami untuk pertanyaan, dukungan, atau informasi lebih lanjut tentang produk dan layanan kami.</p>
        </div>
    </section>

    <!-- Contact Container -->
    <div class="contact-container">
        <!-- Contact Grid -->
        <div class="contact-grid">
            <!-- Contact Info -->
            <div class="contact-info-card">
                <h3>Informasi Kontak</h3>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-details">
                        <h4>Alamat Kantor</h4>
                        <p>PT. Megatek Industrial Persada</p>
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
                        <p>Senin - Jumat: 08:00 - 17:00 WIB</p>
                        <p>Sabtu: 08:00 - 12:00 WIB</p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-details">
                        <h4>Email</h4>
                        <p><a href="mailto:info@megatek.co.id">info@megatek.co.id</a></p>
                        <p><a href="mailto:sales@megatek.co.id">sales@megatek.co.id</a></p>
                        <p><a href="mailto:support@megatek.co.id">support@megatek.co.id</a></p>
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
            
            <!-- Contact Form -->
            <div class="contact-form-container">
                <h3>Kirim Pesan</h3>
                <p>Isi formulir di bawah ini dan kami akan menghubungi Anda secepatnya.</p>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nama Lengkap *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Nomor Telepon</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subjek *</label>
                        <select class="form-control" id="subject" name="subject" required>
                            <option value="">Pilih Subjek</option>
                            <option value="Pertanyaan Produk" <?php echo ($subject ?? '') == 'Pertanyaan Produk' ? 'selected' : ''; ?>>Pertanyaan Produk</option>
                            <option value="Penawaran Harga" <?php echo ($subject ?? '') == 'Penawaran Harga' ? 'selected' : ''; ?>>Penawaran Harga</option>
                            <option value="Dukungan Teknis" <?php echo ($subject ?? '') == 'Dukungan Teknis' ? 'selected' : ''; ?>>Dukungan Teknis</option>
                            <option value="Keluhan" <?php echo ($subject ?? '') == 'Keluhan' ? 'selected' : ''; ?>>Keluhan</option>
                            <option value="Kemitraan" <?php echo ($subject ?? '') == 'Kemitraan' ? 'selected' : ''; ?>>Kemitraan</option>
                            <option value="Lainnya" <?php echo ($subject ?? '') == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Pesan *</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Kirim Pesan
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Map Section -->
        <div class="map-section">
            <h3 style="color: var(--primary-blue); margin-bottom: 20px;">Lokasi Kami</h3>
            <div class="map-container">
                <div class="map-placeholder">
                    <i class="fas fa-map-marked-alt"></i>
                    <h4>PT. Megatek Industrial Persada</h4>
                    <p>Jl. Raya Industri No. 123, Surabaya, Jawa Timur</p>
                    <!-- In a real implementation, you would embed Google Maps here -->
                   <iframe src="https://maps.app.goo.gl/MWNDLGMnQFRaah966?g_st=ipc" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
        
        <!-- FAQ Section -->
        <div class="faq-section">
            <div class="faq-title">
                <h2>Pertanyaan yang Sering Diajukan</h2>
                <p>Temukan jawaban untuk pertanyaan umum tentang produk dan layanan kami</p>
            </div>
            
            <div class="faq-container">
                <div class="faq-item">
                    <h4><i class="fas fa-question-circle me-2"></i> Bagaimana cara memesan produk?</h4>
                    <p>Anda dapat memesan produk melalui website dengan menambahkan produk ke keranjang dan melakukan checkout. Atau, hubungi tim sales kami untuk pemesanan langsung.</p>
                </div>
                
                <div class="faq-item">
                    <h4><i class="fas fa-question-circle me-2"></i> Apa saja metode pembayaran yang tersedia?</h4>
                    <p>Kami menerima transfer bank, kartu kredit, dan virtual account melalui berbagai bank di Indonesia. Info lengkap tersedia di halaman checkout.</p>
                </div>
                
                <div class="faq-item">
                    <h4><i class="fas fa-question-circle me-2"></i> Berapa lama waktu pengiriman?</h4>
                    <p>Waktu pengiriman bervariasi tergantung lokasi. Untuk area Surabaya: 1-2 hari kerja, Jawa Timur: 2-3 hari kerja, Luar Jawa: 3-7 hari kerja.</p>
                </div>
                
                <div class="faq-item">
                    <h4><i class="fas fa-question-circle me-2"></i> Apakah tersedia dukungan teknis setelah pembelian?</h4>
                    <p>Ya, kami menyediakan dukungan teknis gratis untuk semua produk yang dibeli melalui kami. Hubungi tim support kami untuk bantuan teknis.</p>
                </div>
                
                <div class="faq-item">
                    <h4><i class="fas fa-question-circle me-2"></i> Bagaimana kebijakan pengembalian produk?</h4>
                    <p>Produk dapat dikembalikan dalam waktu 7 hari setelah diterima dengan kondisi belum digunakan dan dalam kemasan asli. Syarat dan ketentuan berlaku.</p>
                </div>
                
                <div class="faq-item">
                    <h4><i class="fas fa-question-circle me-2"></i> Apakah saya bisa mendapatkan katalog produk lengkap?</h4>
                    <p>Ya, Anda dapat mengunduh katalog produk lengkap kami atau meminta melalui email ke sales@megatek.co.id</p>
                </div>
            </div>
        </div>
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
            const currentPage = 'contact.php';
            
            menuItems.forEach(item => {
                if (item.textContent.includes('Contact') || 
                    item.getAttribute('href')?.includes('contact')) {
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
            
            // Highlight Contact Us in menu
            const menuLinks = document.querySelectorAll('.menu-category');
            menuLinks.forEach(link => {
                if (link.textContent.includes('Contact') || link.getAttribute('href') === 'contact.php') {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>