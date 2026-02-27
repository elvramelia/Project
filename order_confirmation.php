<?php
require_once 'config/database.php';
require_once 'config/check_login.php';

if (!isLoggedIn()) {
    header('Location: index.php?login_required=1');
    exit();
}

if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header('Location: index.php');
    exit();
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

// Menghitung jumlah item di keranjang untuk Header
$cart_count = 0;
$cart_count_query = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE user_id = $user_id");
if ($cart_count_query) {
    $cart_count = $cart_count_query->fetch_assoc()['total'] ?? 0;
}

// Fetch order details
$order_query = "
    SELECT o.*, 
           CONCAT(u.first_name, ' ', u.last_name) as customer_name,
           u.email, u.phone_number
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.user_id = ?
";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();

if (!$order) {
    header('Location: index.php');
    exit();
}

// Fetch order items
$items_query = "SELECT * FROM order_items WHERE order_id = ?";
$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pesanan - Hardjadinata Karya Utama</title>
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

        /* ===== STICKY HEADER HKU ===== */
        .sticky-wrapper {
            position: sticky;
            top: 0;
            z-index: 9999;
        }

        .hku-header-top {
            background-color: var(--primary-blue);
            color: white;
            padding: 15px 0;
            position: relative;
            z-index: 9999;
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
            z-index: 9998;
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

        .hku-nav-link:hover, .hku-nav-link.active {
            color: var(--primary-red);
            background-color: #fcfcfc;
            border-bottom-color: var(--primary-red);
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

        .dropdown-menu.show { display: block; }
        .dropdown-item { color: var(--dark-gray); transition: background 0.3s; }
        .dropdown-item:hover { background-color: #f8f9fa; color: var(--primary-blue); }

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

        /* ===== CONFIRMATION STYLES ===== */
        .confirmation-hero {
            background-color: var(--primary-blue);
            color: white;
            padding: 60px 0;
            text-align: center;
            border-bottom: 5px solid var(--primary-red);
        }

        .confirmation-hero h1 { font-weight: 700; margin-bottom: 10px; }
        .confirmation-hero p { opacity: 0.9; }

        .confirmation-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .confirmation-card {
            background-color: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
        }

        .success-icon {
            font-size: 4rem;
            color: #4CAF50;
            margin-bottom: 20px;
        }

        .confirmation-card h2 { color: var(--primary-blue); font-weight: 700; }

        .order-details {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            text-align: left;
            border: 1px solid #eee;
        }

        .order-details h4 { color: var(--primary-blue); font-weight: 600; margin-bottom: 15px; }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .payment-instruction {
            background-color: #f0f7ff;
            border-left: 4px solid var(--primary-blue);
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            text-align: left;
        }
        
        .payment-instruction h4 { color: var(--primary-blue); font-weight: 600; font-size: 1.1rem; }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn-print, .btn-track {
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-print {
            background-color: var(--primary-red);
            color: white;
        }

        .btn-track {
            background-color: var(--primary-blue);
            color: white;
        }

        .btn-print:hover { background-color: #c90000; color: white; }
        .btn-track:hover { background-color: #002266; color: white; }

        .help-link { color: var(--primary-blue); text-decoration: none; font-weight: 500;}
        .help-link:hover { color: var(--primary-red); text-decoration: underline;}

        /* ===== FOOTER HKU ===== */
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

        .footer h5 { color: white; font-weight: 600; margin-bottom: 25px; font-size: 1.1rem; }
        .footer-links { list-style: none; padding: 0; }
        .footer-links li { margin-bottom: 10px; }
        .footer-links a { color: #ccc; text-decoration: none; transition: color 0.3s; }
        .footer-links a:hover { color: white; }

        .social-icons a {
            display: inline-block; width: 36px; height: 36px; background: rgba(255,255,255,0.1);
            color: white; border-radius: 50%; text-align: center; line-height: 36px;
            margin-right: 10px; transition: background 0.3s;
        }
        .social-icons a:hover { background: var(--primary-red); }

        .copyright {
            text-align: center; margin-top: 40px; padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1); color: #aaa; font-size: 14px;
        }

        /* Print Media Query */
        @media print {
            .sticky-wrapper, .hku-header-actions, .footer, .action-buttons, .confirmation-hero {
                display: none !important;
            }
            .confirmation-card {
                box-shadow: none;
                padding: 0;
            }
        }
        /* Responsive */
        @media (max-width: 992px) { .hku-header-actions { display: none; } }
        @media (max-width: 576px) { .action-buttons { flex-direction: column; } }
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
                            <span class="cart-badge" id="cartCount"><?php echo $cart_count; ?></span>
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
                                    <span class="dropdown-item-text" style="display: block; padding: 10px 15px;">
                                        <small>Logged in as:</small><br>
                                        <strong><?php echo htmlspecialchars($_SESSION['user_email']); ?></strong>
                                    </span>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="orders.php" style="display: block; padding: 10px 15px; text-decoration: none;"><i class="fas fa-shopping-bag me-2"></i>My Orders</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="logout.php" style="display: block; padding: 10px 15px; text-decoration: none;">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </a>
                                </div>
                            </div>
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
                <a href="hubungikami.php" class="hku-nav-link">HUBUNGI KAMI</a>
            </div>
        </nav>
    </div>

    <section class="confirmation-hero">
        <div class="container">
            <h1>Pesanan Berhasil!</h1>
            <p>Terima kasih telah berbelanja di Hardjadinata Karya Utama</p>
        </div>
    </section>

    <div class="confirmation-container">
        <div class="confirmation-card">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h2>Pesanan Anda Telah Diterima</h2>
            <p class="text-muted">Nomor Pesanan: <strong style="color: var(--primary-blue);"><?php echo $order['order_number']; ?></strong></p>
            <p>Kami telah mengirimkan detail pesanan ke email: <strong><?php echo $order['email']; ?></strong></p>
            
            <div class="order-details">
                <h4>Detail Pesanan</h4>
                <div class="detail-row">
                    <span>Tanggal Pesanan:</span>
                    <span><?php echo date('d F Y H:i', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="detail-row">
                    <span>Total Pembayaran:</span>
                    <span style="color: var(--primary-red); font-weight: bold; font-size:1.1rem;">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></span>
                </div>
                <div class="detail-row">
                    <span>Metode Pembayaran:</span>
                    <span><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></span>
                </div>
                <div class="detail-row">
                    <span>Status:</span>
                    <span class="badge bg-warning text-dark"><?php echo ucfirst($order['status']); ?></span>
                </div>
            </div>

            <?php if ($order['payment_method'] === 'bank_transfer'): ?>
            <div class="payment-instruction">
                <h4><i class="fas fa-info-circle me-2"></i>Instruksi Pembayaran</h4>
                <p>Silakan lakukan transfer ke:</p>
                <?php
                $bank = $order['bank_name'];
                $bank_accounts = [
                    'bca' => [ 'name' => 'Bank BCA', 'account' => '1234567890' ],
                    'mandiri' => [ 'name' => 'Bank Mandiri', 'account' => '0987654321' ],
                    'bri' => [ 'name' => 'Bank BRI', 'account' => '1122334455' ]
                ];
                $selected_bank = isset($bank_accounts[$bank]) ? $bank_accounts[$bank] : $bank_accounts['bca'];
                ?>
                <p style="font-size: 1.1rem;">
                    <strong><?php echo $selected_bank['name']; ?></strong><br>
                    No. Rekening: <strong style="color: var(--primary-blue);"><?php echo $selected_bank['account']; ?></strong><br>
                    Atas Nama: <strong>Hardjadinata Karya Utama</strong>
                </p>
                <p><strong>Jumlah Transfer:</strong> <span style="color: var(--primary-red); font-weight:bold;">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></span></p>
                <p class="text-muted"><small>Konfirmasi pembayaran akan diproses dalam 1x24 jam setelah transfer dilakukan.</small></p>
            </div>
            
            <?php elseif ($order['payment_method'] === 'qris'): ?>
            <div class="payment-instruction">
                <h4><i class="fas fa-qrcode me-2"></i>Instruksi Pembayaran</h4>
                <p>Silakan selesaikan pembayaran dengan QRIS melalui aplikasi e-wallet atau mobile banking Anda.</p>
                <p class="text-muted"><small>Pembayaran akan diverifikasi secara otomatis oleh sistem kami.</small></p>
            </div>
            
            <?php elseif ($order['payment_method'] === 'cod'): ?>
            <div class="payment-instruction">
                <h4><i class="fas fa-money-bill-wave me-2"></i>Instruksi Pembayaran</h4>
                <p>Anda akan membayar saat barang diterima di alamat:</p>
                <p><strong><?php echo htmlspecialchars($order['shipping_address']); ?></strong></p>
                <p class="text-muted"><small>Kurir kami akan menghubungi Anda 1 jam sebelum pengiriman dilakukan.</small></p>
            </div>
            <?php endif; ?>

            <div class="action-buttons">
                <a href="javascript:window.print()" class="btn-print">
                    <i class="fas fa-print"></i> Cetak Invoice
                </a>
                <a href="orders.php" class="btn-track">
                    <i class="fas fa-shopping-bag"></i> Lihat Pesanan Saya
                </a>
                <a href="produk.php" class="btn-track" style="background-color: white; color: var(--primary-blue); border: 1px solid var(--primary-blue);">
                    <i class="fas fa-shopping-cart"></i> Lanjut Belanja
                </a>
            </div>

            <p class="text-muted mt-5">
                <small>
                    Butuh bantuan? Hubungi <a href="mailto:info@hku.co.id" class="help-link">info@hku.co.id</a> atau 
                    <a href="tel:+623112345678" class="help-link">+62 31 1234 5678</a>
                </small>
            </p>
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

    <script>
        // Dropdown menu functionality
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
            userDropdownMenu.addEventListener('click', function(e) { e.stopPropagation(); });
        }

        // Search logic
        const searchInput = document.querySelector('.search-bar input');
        const searchButton = document.querySelector('.search-bar button');
        const executeSearch = () => {
            const term = searchInput.value.trim();
            if (term !== '') window.location.href = 'produk.php?search=' + encodeURIComponent(term);
        };
        if(searchButton) searchButton.addEventListener('click', executeSearch);
        if(searchInput) searchInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') executeSearch(); });

        // Auto redirect after 5 minutes (300 seconds) of inactivity
        let idleTime = 0;
        const idleInterval = setInterval(timerIncrement, 1000);
        function timerIncrement() {
            idleTime++;
            if (idleTime > 300) { window.location.href = "beranda.php"; }
        }
        document.addEventListener('mousemove', () => idleTime = 0);
        document.addEventListener('keypress', () => idleTime = 0);
    </script>
</body>
</html>