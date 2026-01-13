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
    <title>Lacak Pesanan - Megatek Industrial Persada</title>
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
        }

        /* Navbar Styling */
        .navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 10px 20px;
        }

        .navbar-brand img {
            height: 40px;
        }

        .search-bar {
            flex-grow: 1;
            max-width: 500px;
            margin: 0 20px;
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
        }

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

        /* Main Menu */
        .main-menu {
            background-color: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 10px 0;
        }

        .menu-container {
            display: flex;
            justify-content: center;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .menu-category {
            color: var(--dark-gray);
            text-decoration: none;
            font-weight: 500;
            padding: 5px 0;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .menu-category:hover,
        .menu-category.active {
            color: var(--primary-blue);
            border-bottom-color: var(--primary-blue);
        }

        /* Hero Section */
        .tracking-hero {
            background-color: var(--primary-blue);
            color: white;
            padding: 40px 0;
            text-align: center;
        }

        .tracking-hero h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .tracking-hero p {
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
        }

        /* Tracking Container */
        .tracking-container {
            max-width: 1200px;
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
            background-color: #f8f9fa;
            border-color: var(--primary-blue);
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
        }

        .order-info h3 {
            color: var(--primary-blue);
            margin-bottom: 5px;
        }

        .order-info p {
            color: #666;
            margin: 0;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-processing { background-color: #cce5ff; color: #004085; }
        .status-shipped { background-color: #d1ecf1; color: #0c5460; }
        .status-delivered { background-color: #d4edda; color: #155724; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }

        /* Shipping Info */
        .shipping-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .info-value {
            font-weight: 500;
            color: var(--dark-gray);
        }

        /* Timeline */
        .timeline-section {
            margin: 40px 0;
        }

        .section-title {
            font-size: 1.3rem;
            color: var(--primary-blue);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
            max-width: 800px;
            margin: 0 auto;
        }

        .timeline:before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #e0e0e0;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 30px;
            padding-left: 20px;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-item:before {
            content: '';
            position: absolute;
            left: -25px;
            top: 5px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background-color: #ddd;
            border: 3px solid white;
            z-index: 2;
        }

        .timeline-item.completed:before {
            background-color: #28a745;
        }

        .timeline-item.active:before {
            background-color: var(--primary-blue);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(26, 75, 140, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(26, 75, 140, 0); }
            100% { box-shadow: 0 0 0 0 rgba(26, 75, 140, 0); }
        }

        .timeline-icon {
            position: absolute;
            left: -30px;
            top: 0;
            background-color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            z-index: 3;
        }

        .timeline-item.completed .timeline-icon {
            color: #28a745;
        }

        .timeline-item.active .timeline-icon {
            color: var(--primary-blue);
        }

        .timeline-content {
            background-color: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
        }

        .timeline-status {
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 5px;
        }

        .timeline-date {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .timeline-location {
            font-size: 14px;
            color: #666;
        }

        /* Order Items Table */
        .order-items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .order-items-table th {
            background-color: #f8f9fa;
            padding: 12px 15px;
            text-align: left;
            color: #666;
            font-weight: 500;
            border-bottom: 1px solid #e0e0e0;
        }

        .order-items-table td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: middle;
        }

        .item-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            overflow: hidden;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .item-name {
            font-weight: 500;
            color: var(--dark-gray);
        }

        .item-price {
            font-weight: 600;
            color: var(--primary-blue);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: var(--primary-blue);
            color: white;
            border: 1px solid var(--primary-blue);
        }

        .btn-primary:hover {
            background-color: #153a6e;
            color: white;
        }

        .btn-secondary {
            background-color: white;
            color: var(--primary-blue);
            border: 1px solid var(--primary-blue);
        }

        .btn-secondary:hover {
            background-color: #f8f9fa;
            color: var(--primary-blue);
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
        }

        .footer-links a:hover {
            color: white;
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
            .search-bar {
                max-width: 200px;
            }
            
            .nav-icon span {
                display: none;
            }
            
            .menu-container {
                gap: 15px;
                justify-content: space-around;
            }
            
            .menu-category {
                font-size: 14px;
            }
            
            .order-summary {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .timeline {
                padding-left: 20px;
            }
            
            .timeline:before {
                left: 10px;
            }
            
            .timeline-item:before {
                left: -15px;
            }
            
            .timeline-icon {
                left: -20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-action {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Main Navbar -->
    <nav class="navbar d-flex align-items-center">
        <a class="navbar-brand mx-2" href="index.php">
            <img src="uploads/LOGO.png" alt="Megatek Logo">
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
                           
                            <a class="dropdown-item" href="orders.php"><i class="fas fa-shopping-bag me-2"></i>My Orders</a>
                
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

    <!-- Main Menu -->
    <div class="main-menu">
        <div class="menu-container">
            <a href="beranda.php" class="menu-category">Beranda</a>
            <a href="tentangkami.php" class="menu-category">Tentang Kami</a>
            <a href="produk.php" class="menu-category">Produk</a>
            <a href="hubungikami.php" class="menu-category">Hubungi Kami</a>
        </div>
    </div>

    <!-- Tracking Hero Section -->
    <section class="tracking-hero">
        <div class="container">
            <h1>Lacak Pesanan</h1>
            <p>Pantau status pengiriman pesanan Anda di Megatek Industrial Persada</p>
        </div>
    </section>

    <!-- Tracking Container -->
    <div class="tracking-container">
        <a href="orders.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Pesanan
        </a>

        <div class="tracking-card">
            <!-- Order Summary -->
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

            <!-- Shipping Information -->
            <div class="shipping-info">
                <h4>Informasi Pengiriman</h4>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Kurir</span>
                        <span class="info-value">
                            <?php echo $courier_labels[$shipping_courier] ?? $shipping_courier; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Nomor Resi</span>
                        <span class="info-value"><?php echo $tracking_number; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Alamat Pengiriman</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['shipping_address'] ?? 'Alamat belum diisi'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Estimasi Pengiriman</span>
                        <span class="info-value">
                            <?php echo date('d F Y', strtotime($estimated_delivery)); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Tracking Timeline -->
            <div class="timeline-section">
                <h3 class="section-title">Status Pengiriman</h3>
                
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

            <!-- Order Items -->
            <div class="order-items-section">
                <h3 class="section-title">Produk dalam Pesanan</h3>
                
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
                                <td><?php echo $item['quantity']; ?></td>
                                <td class="item-price">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                <td class="item-price">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="2" style="text-align: right; font-weight: 600;">
                                Total Barang: <?php echo $total_items; ?>
                            </td>
                            <td style="text-align: right; font-weight: 600;">Total Pesanan:</td>
                            <td class="item-price" style="font-size: 1.1rem;">
                                Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="https://cekresi.com/?noresi=<?php echo urlencode($tracking_number); ?>" 
                   target="_blank" 
                   class="btn-action btn-primary">
                    <i class="fas fa-external-link-alt"></i> Cek di Website Kurir
                </a>
            </div>
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
        // Update cart count
        function updateCartCount() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_cart_count.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            const cartBadge = document.querySelector('.cart-badge');
                            if (cartBadge) {
                                cartBadge.textContent = response.count;
                            }
                        }
                    } catch (e) {
                        console.error('Error updating cart count:', e);
                    }
                }
            };
            xhr.send();
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
            
            // Search functionality
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
            
            // Auto-update cart count
            setInterval(updateCartCount, 30000);
        });
    </script>
</body>
</html>