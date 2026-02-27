<?php
require_once 'config/database.php';
require_once 'config/check_login.php';

if (!isLoggedIn()) {
    header('Location: index.php?login_required=1');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id === 0) {
    header('Location: orders.php');
    exit();
}

// Menghitung jumlah item di keranjang untuk Header
$cart_count = 0;
$cart_count_query = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE user_id = $user_id");
if ($cart_count_query) {
    $cart_count = $cart_count_query->fetch_assoc()['total'] ?? 0;
}

// Fetch order details
$order_query = "
    SELECT 
        o.id,
        o.order_number,
        o.total_amount,
        o.status,
        o.payment_method,
        o.shipping_address,
        o.created_at,
        o.updated_at
    FROM orders o
    WHERE o.id = ? AND o.user_id = ?
";

$stmt = $conn->prepare($order_query);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    header('Location: orders.php');
    exit();
}

$order = $order_result->fetch_assoc();

// Fetch order items
$items_query = "
    SELECT oi.*, p.image_url 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
";
$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);

// Generate tracking number from order number
$tracking_number = 'TRK-' . str_replace('ORD-', '', $order['order_number']) . '-ID';

// Status labels
$status_labels = [
    'pending' => 'Menunggu Pembayaran',
    'processing' => 'Diproses',
    'shipped' => 'Dikirim',
    'delivered' => 'Selesai',
    'cancelled' => 'Dibatalkan'
];

// Courier labels
$courier_labels = [
    'jne' => 'JNE',
    'tiki' => 'TIKI',
    'pos' => 'POS Indonesia',
    'jnt' => 'J&T Express',
    'sicepat' => 'SiCepat',
    'ninja' => 'Ninja Xpress'
];

// Default values
$shipping_courier = 'jne';
$estimated_delivery = date('Y-m-d', strtotime($order['created_at'] . ' +5 days'));

// Tracking history based on order status
$tracking_history = [];

switch ($order['status']) {
    case 'pending':
        $tracking_history = [
            ['status' => 'order_created', 'description' => 'Pesanan dibuat', 'date' => $order['created_at'], 'location' => 'Sistem']
        ];
        break;
        
    case 'processing':
        $tracking_history = [
            ['status' => 'order_created', 'description' => 'Pesanan dibuat', 'date' => $order['created_at'], 'location' => 'Sistem'],
            ['status' => 'processing', 'description' => 'Pesanan diproses', 'date' => $order['updated_at'] ?: date('Y-m-d H:i:s', strtotime($order['created_at'] . ' +1 day')), 'location' => 'Surabaya']
        ];
        break;
        
    case 'shipped':
        $tracking_history = [
            ['status' => 'order_created', 'description' => 'Pesanan dibuat', 'date' => $order['created_at'], 'location' => 'Sistem'],
            ['status' => 'processing', 'description' => 'Pesanan diproses', 'date' => date('Y-m-d H:i:s', strtotime($order['created_at'] . ' +1 day')), 'location' => 'Surabaya'],
            ['status' => 'shipped', 'description' => 'Pesanan dikirim', 'date' => $order['updated_at'] ?: date('Y-m-d H:i:s', strtotime($order['created_at'] . ' +2 days')), 'location' => 'Surabaya'],
            ['status' => 'estimated', 'description' => 'Estimasi pengiriman', 'date' => $estimated_delivery . ' 17:00:00', 'location' => 'Tujuan']
        ];
        break;
        
    case 'delivered':
        $tracking_history = [
            ['status' => 'order_created', 'description' => 'Pesanan dibuat', 'date' => $order['created_at'], 'location' => 'Sistem'],
            ['status' => 'processing', 'description' => 'Pesanan diproses', 'date' => date('Y-m-d H:i:s', strtotime($order['created_at'] . ' +1 day')), 'location' => 'Surabaya'],
            ['status' => 'shipped', 'description' => 'Pesanan dikirim', 'date' => date('Y-m-d H:i:s', strtotime($order['created_at'] . ' +2 days')), 'location' => 'Surabaya'],
            ['status' => 'delivered', 'description' => 'Pesanan selesai', 'date' => $order['updated_at'] ?: date('Y-m-d H:i:s', strtotime($order['created_at'] . ' +5 days')), 'location' => 'Tujuan']
        ];
        break;
        
    default:
        $tracking_history = [
            ['status' => 'order_created', 'description' => 'Pesanan dibuat', 'date' => $order['created_at'], 'location' => 'Sistem']
        ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Pesanan - Hardjadinata Karya Utama</title>
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

        /* ===== TRACKING SPECIFIC STYLES ===== */
        .tracking-hero {
            background-color: var(--primary-blue);
            color: white;
            padding: 40px 0;
            text-align: center;
            border-bottom: 5px solid var(--primary-red);
        }

        .tracking-hero h1 { font-size: 2.2rem; font-weight: 700; margin-bottom: 10px; }
        .tracking-hero p { font-size: 1.1rem; max-width: 700px; margin: 0 auto; opacity: 0.9; }

        .tracking-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 15px;
        }

        /* Back Button */
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
            padding: 10px 15px;
            border-radius: 8px;
            background-color: white;
            border: 1px solid #e0e0e0;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background-color: var(--primary-red);
            color: white;
            border-color: var(--primary-red);
        }

        /* Tracking Card */
        .tracking-card {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        /* Order Summary */
        .order-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
            border: 1px solid #eee;
        }

        .order-info h3 { color: var(--primary-blue); margin-bottom: 5px; font-weight: 700; font-size: 1.3rem;}
        .order-info p { color: #666; margin: 0; font-size: 14px;}

        .status-badge {
            padding: 8px 16px; border-radius: 6px; font-size: 14px; font-weight: 600; text-transform: uppercase;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-processing { background-color: #cce5ff; color: #004085; }
        .status-shipped { background-color: #d1ecf1; color: #0c5460; }
        .status-delivered { background-color: #d4edda; color: #155724; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }

        /* Shipping Info */
        .shipping-info {
            background-color: #f0f7ff;
            border-left: 4px solid var(--primary-blue);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .shipping-info h4 { color: var(--primary-blue); font-weight: 600; font-size: 1.1rem; }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }

        .info-item { display: flex; flex-direction: column; }
        .info-label { font-size: 13px; color: #666; margin-bottom: 5px; }
        .info-value { font-weight: 600; color: var(--dark-gray); }

        /* Timeline */
        .timeline-section { margin: 40px 0; }
        .section-title {
            font-size: 1.3rem; color: var(--primary-blue); margin-bottom: 25px;
            padding-bottom: 15px; border-bottom: 2px solid #e0e0e0; font-weight: 700;
        }

        .timeline { position: relative; padding-left: 30px; max-width: 800px; margin: 0 auto; }
        .timeline:before {
            content: ''; position: absolute; left: 15px; top: 0; bottom: 0; width: 2px; background-color: #e0e0e0;
        }

        .timeline-item { position: relative; margin-bottom: 30px; padding-left: 20px; }
        .timeline-item:last-child { margin-bottom: 0; }
        .timeline-item:before {
            content: ''; position: absolute; left: -25px; top: 5px; width: 16px; height: 16px;
            border-radius: 50%; background-color: #ddd; border: 3px solid white; z-index: 2;
        }

        .timeline-item.completed:before { background-color: #28a745; }
        .timeline-item.active:before {
            background-color: var(--primary-blue); animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(0, 56, 147, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(0, 56, 147, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 56, 147, 0); }
        }

        .timeline-icon {
            position: absolute; left: -30px; top: 0; background-color: white; width: 30px; height: 30px;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            color: #666; z-index: 3;
        }
        .timeline-item.completed .timeline-icon { color: #28a745; }
        .timeline-item.active .timeline-icon { color: var(--primary-blue); }

        .timeline-content {
            background-color: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 15px; transition: 0.3s;
        }
        .timeline-item.active .timeline-content {
            border-color: var(--primary-blue); background-color: #f8fbff;
        }

        .timeline-status { font-weight: 600; color: var(--dark-gray); margin-bottom: 5px; }
        .timeline-date { font-size: 13px; color: #666; margin-bottom: 5px; }
        .timeline-location { font-size: 13px; color: #666; }

        /* Order Items Table */
        .order-items-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .order-items-table th {
            background-color: #f8f9fa; padding: 12px 15px; text-align: left;
            color: #666; font-weight: 600; border-bottom: 1px solid #e0e0e0;
        }
        .order-items-table td { padding: 15px; border-bottom: 1px solid #e0e0e0; vertical-align: middle; }

        .item-image { width: 60px; height: 60px; border-radius: 8px; overflow: hidden; border: 1px solid #eee; }
        .item-image img { width: 100%; height: 100%; object-fit: contain; }
        .item-name { font-weight: 500; color: var(--dark-gray); }
        .item-price { font-weight: 600; color: var(--primary-blue); }

        /* Action Buttons */
        .action-buttons { display: flex; gap: 15px; margin-top: 30px; flex-wrap: wrap; }
        .btn-action {
            padding: 12px 24px; border-radius: 6px; font-size: 14px; font-weight: 600;
            text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s;
        }
        .btn-primary {
            background-color: var(--primary-blue); color: white; border: 1px solid var(--primary-blue);
        }
        .btn-primary:hover { background-color: var(--primary-red); border-color: var(--primary-red); color: white; }

        /* ===== FOOTER HKU ===== */
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
            color: white; border-radius: 50%; text-align: center; line-height: 36px;
            margin-right: 10px; transition: background 0.3s;
        }
        .social-icons a:hover { background: var(--primary-red); }
        .copyright {
            text-align: center; margin-top: 40px; padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1); color: #aaa; font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 992px) { .hku-header-actions { display: none; } }
        @media (max-width: 768px) {
            .order-summary { flex-direction: column; align-items: flex-start; }
            .timeline { padding-left: 20px; }
            .timeline:before { left: 10px; }
            .timeline-item:before { left: -15px; }
            .timeline-icon { left: -20px; }
            .action-buttons { flex-direction: column; }
            .btn-action { width: 100%; justify-content: center; }
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

    <section class="tracking-hero">
        <div class="container">
            <h1>Lacak Pesanan</h1>
            <p>Pantau status pengiriman pesanan Anda di Hardjadinata Karya Utama</p>
        </div>
    </section>

    <div class="tracking-container">
        <a href="orders.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Pesanan
        </a>

        <div class="tracking-card">
            <div class="order-summary">
                <div class="order-info">
                    <h3>Pesanan #<?php echo htmlspecialchars($order['order_number']); ?></h3>
                    <p>Tanggal Pesanan: <?php echo date('d F Y, H:i', strtotime($order['created_at'])); ?></p>
                </div>
                <div class="order-status">
                    <span class="status-badge status-<?php echo $order['status']; ?>">
                        <?php echo $status_labels[$order['status']] ?? $order['status']; ?>
                    </span>
                </div>
            </div>

            <div class="shipping-info">
                <h4><i class="fas fa-truck me-2"></i>Informasi Pengiriman</h4>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Kurir</span>
                        <span class="info-value">
                            <?php echo $courier_labels[$shipping_courier] ?? strtoupper($shipping_courier); ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Nomor Resi</span>
                        <span class="info-value" style="color: var(--primary-blue);"><?php echo $tracking_number; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Alamat Pengiriman</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['shipping_address'] ?? 'Alamat belum diisi'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Estimasi Pengiriman Tiba</span>
                        <span class="info-value" style="color: var(--primary-red);">
                            <?php echo date('d F Y', strtotime($estimated_delivery)); ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="timeline-section">
                <h3 class="section-title"><i class="fas fa-route me-2"></i>Status Pengiriman</h3>
                
                <div class="timeline">
                    <?php 
                    $total_steps = count($tracking_history);
                    foreach ($tracking_history as $index => $track): 
                        $is_last = $index === $total_steps - 1;
                        $is_completed = !$is_last;
                        $is_active = $is_last;
                    ?>
                        <div class="timeline-item <?php echo $is_completed ? 'completed' : ($is_active ? 'active' : ''); ?>">
                            <div class="timeline-icon">
                                <?php 
                                $icon_map = [
                                    'order_created' => 'fa-receipt',
                                    'processing' => 'fa-cog',
                                    'shipped' => 'fa-shipping-fast',
                                    'estimated' => 'fa-calendar-check',
                                    'delivered' => 'fa-check-circle'
                                ];
                                $icon = $icon_map[$track['status']] ?? 'fa-circle';
                                ?>
                                <i class="fas <?php echo $icon; ?>"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-status"><?php echo htmlspecialchars($track['description']); ?></div>
                                <div class="timeline-date">
                                    <i class="far fa-clock me-1"></i>
                                    <?php echo date('d F Y, H:i', strtotime($track['date'])); ?>
                                </div>
                                <?php if (!empty($track['location'])): ?>
                                    <div class="timeline-location">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?php echo htmlspecialchars($track['location']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="order-items-section">
                <h3 class="section-title"><i class="fas fa-box-open me-2"></i>Produk dalam Pesanan</h3>
                
                <div style="overflow-x: auto;">
                    <table class="order-items-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Kuantitas</th>
                                <th>Harga Satuan</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_items = 0;
                            foreach ($order_items as $item): 
                                $total_items += $item['quantity'];
                            ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 15px;">
                                            <div class="item-image">
                                                <img src="uploads/<?php echo htmlspecialchars($item['image_url'] ?? 'img/produk-sample.png'); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                                     onerror="this.src='img/produk-sample.png'">
                                            </div>
                                            <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                        </div>
                                    </td>
                                    <td style="font-weight: 600;"><?php echo $item['quantity']; ?></td>
                                    <td class="item-price">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                    <td class="item-price">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="2" style="text-align: right; font-weight: 600; padding-top:20px;">
                                    Total Barang: <?php echo $total_items; ?>
                                </td>
                                <td style="text-align: right; font-weight: 600; padding-top:20px;">Total Pesanan:</td>
                                <td class="item-price" style="font-size: 1.2rem; color: var(--primary-red); padding-top:20px;">
                                    Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="action-buttons">
                <a href="https://cekresi.com/?noresi=<?php echo urlencode($tracking_number); ?>" 
                   target="_blank" 
                   class="btn-action btn-primary">
                    <i class="fas fa-external-link-alt"></i> Lacak Langsung di Website Kurir
                </a>
            </div>
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
            userDropdownMenu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }

        // Global Search functionality
        const searchInput = document.querySelector('.search-bar input');
        const searchButton = document.querySelector('.search-bar button');
        
        function performSearch() {
            const searchTerm = searchInput.value.trim();
            if (searchTerm) {
                window.location.href = 'produk.php?search=' + encodeURIComponent(searchTerm);
            }
        }
        
        if (searchButton) {
            searchButton.addEventListener('click', performSearch);
        }
        
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });
        }
    </script>
</body>
</html>