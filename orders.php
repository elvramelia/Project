<?php
require_once 'config/database.php';
require_once 'config/check_login.php';

if (!isLoggedIn()) {
    header('Location: index.php?login_required=1');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $order_id = intval($_POST['order_id']);
    
    // Check if order belongs to user and is cancellable
    $check_query = "SELECT status FROM orders WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        
        // Only allow cancellation for pending or processing orders
        if (in_array($order['status'], ['pending', 'processing'])) {
            $cancel_query = "UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($cancel_query);
            $stmt->bind_param("i", $order_id);
            
            if ($stmt->execute()) {
                // Restore product stock
                $restore_query = "
                    UPDATE products p
                    JOIN order_items oi ON p.id = oi.product_id
                    SET p.stock = p.stock + oi.quantity
                    WHERE oi.order_id = ?
                ";
                $stmt = $conn->prepare($restore_query);
                $stmt->bind_param("i", $order_id);
                $stmt->execute();
                
                $message = 'Pesanan berhasil dibatalkan';
                $message_type = 'success';
            } else {
                $message = 'Gagal membatalkan pesanan';
                $message_type = 'error';
            }
        } else {
            $message = 'Pesanan tidak dapat dibatalkan';
            $message_type = 'error';
        }
    }
}

// Fetch all orders for the user
$orders_query = "
    SELECT 
        o.id,
        o.order_number,
        o.total_amount,
        o.status,
        o.payment_method,
        o.payment_status,
        o.shipping_address,
        o.created_at,
        o.updated_at,
        COUNT(oi.id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
";

$stmt = $conn->prepare($orders_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders_result = $stmt->get_result();
$orders = $orders_result->fetch_all(MYSQLI_ASSOC);

// Get order counts for filter tabs
$all_count = count($orders);
$pending_count = 0;
$processing_count = 0;
$shipped_count = 0;
$delivered_count = 0;
$cancelled_count = 0;

foreach ($orders as $order) {
    switch ($order['status']) {
        case 'pending':
            $pending_count++;
            break;
        case 'processing':
            $processing_count++;
            break;
        case 'shipped':
            $shipped_count++;
            break;
        case 'delivered':
            $delivered_count++;
            break;
        case 'cancelled':
            $cancelled_count++;
            break;
    }
}

// Filter orders by status if specified
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$filtered_orders = $orders;

if ($filter !== 'all') {
    $filtered_orders = array_filter($orders, function($order) use ($filter) {
        return $order['status'] === $filter;
    });
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Megatek Industrial Persada</title>
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

        /* Reuse all common styles from cart.php */
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

        /* Orders Hero Section */
        .orders-hero {
            background-color: var(--primary-blue);
            color: white;
            padding: 40px 0;
            text-align: center;
        }

        .orders-hero h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .orders-hero p {
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
        }

        /* Orders Container */
        .orders-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 15px;
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

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
        }

        /* Order Tabs */
        .order-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .order-tab {
            padding: 10px 20px;
            background-color: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            text-decoration: none;
            color: #666;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .order-tab:hover {
            background-color: #f8f9fa;
            color: var(--primary-blue);
            border-color: var(--primary-blue);
            text-decoration: none;
        }

        .order-tab.active {
            background-color: var(--primary-blue);
            color: white;
            border-color: var(--primary-blue);
        }

        .tab-badge {
            background-color: #e9ecef;
            color: #495057;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
        }

        .order-tab.active .tab-badge {
            background-color: rgba(255, 255, 255, 0.3);
            color: white;
        }

        /* Orders Section */
        .orders-section {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .section-header h2 {
            color: var(--primary-blue);
            font-size: 1.5rem;
            margin: 0;
        }

        .search-orders {
            position: relative;
            max-width: 300px;
        }

        .search-orders input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }

        .search-orders i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        /* Empty Orders */
        .empty-orders {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-orders i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-orders h3 {
            color: #666;
            margin-bottom: 15px;
        }

        .empty-orders p {
            color: #999;
            margin-bottom: 30px;
        }

        .btn-shopping {
            background-color: var(--primary-blue);
            color: white;
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
            transition: background-color 0.3s;
        }

        .btn-shopping:hover {
            background-color: #153a6e;
            color: white;
            text-decoration: none;
        }

        /* Order List */
        .order-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .order-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            transition: box-shadow 0.3s;
        }

        .order-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .order-header {
            background-color: #f8f9fa;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .order-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .order-number {
            font-weight: 600;
            color: var(--dark-gray);
            font-size: 16px;
        }

        .order-date {
            color: #666;
            font-size: 14px;
        }

        .order-status {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-shipped {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-delivered {
            background-color: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .payment-method {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
            font-size: 14px;
        }

        .order-body {
            padding: 20px;
        }

        .order-items {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 20px;
        }

        .order-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }

        .item-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .item-quantity {
            color: #666;
            font-size: 14px;
        }

        .item-price {
            font-weight: 600;
            color: var(--primary-blue);
        }

        .order-footer {
            padding: 20px;
            background-color: #f8f9fa;
            border-top: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .order-total {
            font-weight: 700;
            color: var(--dark-gray);
            font-size: 18px;
        }

        .order-actions {
            display: flex;
            gap: 10px;
        }

        .btn-action {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }

        .btn-view {
            background-color: var(--primary-blue);
            color: white;
            border: 1px solid var(--primary-blue);
        }

        .btn-view:hover {
            background-color: #153a6e;
            color: white;
            text-decoration: none;
        }

        .btn-cancel {
            background-color: white;
            color: #dc3545;
            border: 1px solid #dc3545;
        }

        .btn-cancel:hover {
            background-color: #dc3545;
            color: white;
            text-decoration: none;
        }

        .btn-invoice {
            background-color: white;
            color: #28a745;
            border: 1px solid #28a745;
        }

        .btn-invoice:hover {
            background-color: #28a745;
            color: white;
            text-decoration: none;
        }

        .btn-track {
            background-color: white;
            color: #ffc107;
            border: 1px solid #ffc107;
        }

        .btn-track:hover {
            background-color: #ffc107;
            color: white;
            text-decoration: none;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 5px;
        }

        .page-link {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            color: #666;
            text-decoration: none;
            transition: all 0.3s;
        }

        .page-link:hover {
            background-color: #f8f9fa;
            color: var(--primary-blue);
            text-decoration: none;
        }

        .page-link.active {
            background-color: var(--primary-blue);
            color: white;
            border-color: var(--primary-blue);
        }

        /* Status Timeline */
        .status-timeline {
            margin-top: 20px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }

        .timeline-title {
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--dark-gray);
        }

        .timeline-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
        }

        .timeline-steps:before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #ddd;
            z-index: 1;
        }

        .timeline-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            flex: 1;
        }

        .step-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #ddd;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }

        .timeline-step.active .step-icon {
            background-color: var(--primary-blue);
        }

        .timeline-step.completed .step-icon {
            background-color: #28a745;
        }

        .step-label {
            font-size: 12px;
            color: #666;
            text-align: center;
        }

        .timeline-step.active .step-label {
            color: var(--primary-blue);
            font-weight: 500;
        }

        .step-date {
            font-size: 11px;
            color: #999;
            margin-top: 3px;
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

        /* Modal for order details */
        .order-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            border-radius: 10px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 20px;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            color: #666;
            cursor: pointer;
        }

        /* Responsive */
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
            
            .orders-hero h1 {
                font-size: 1.8rem;
            }
            
            .orders-container {
                padding: 30px 15px;
            }
            
            .orders-section {
                padding: 20px;
            }
            
            .order-header, .order-footer {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .order-actions {
                width: 100%;
                justify-content: flex-start;
            }
            
            .btn-action {
                flex: 1;
                justify-content: center;
            }
            
            .timeline-steps {
                flex-wrap: wrap;
                gap: 20px;
            }
            
            .timeline-step {
                flex: none;
                width: calc(50% - 10px);
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
            
            .order-tabs {
                overflow-x: auto;
                flex-wrap: nowrap;
                justify-content: flex-start;
                padding-bottom: 10px;
            }
            
            .order-tab {
                white-space: nowrap;
            }
            
            .order-item {
                flex-direction: column;
                text-align: center;
            }
            
            .item-image {
                width: 80px;
                height: 80px;
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
            <a href="keranjang.php" class="nav-icon">
                <div style="position: relative;">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-badge">0</span>
                </div>
                <span>Cart</span>
            </a>
            
            <div id="userSection">
                <?php if (isLoggedIn()): ?>
                    <div class="user-dropdown">
                        <a href="javascript:void(0);" class="nav-icon active">
                            <i class="fas fa-user"></i>
                            <span>Akun</span>
                        </a>
                    </div>
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

    <!-- Orders Hero Section -->
    <section class="orders-hero">
        <div class="container">
            <h1>Pesanan Saya</h1>
            <p>Kelola dan lacak pesanan Anda di Megatek Industrial Persada</p>
        </div>
    </section>

    <!-- Orders Container -->
    <div class="orders-container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Order Tabs -->
        <div class="order-tabs">
            <a href="?filter=all" class="order-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-bag"></i> Semua
                <span class="tab-badge"><?php echo $all_count; ?></span>
            </a>
            <a href="?filter=pending" class="order-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i> Pending
                <span class="tab-badge"><?php echo $pending_count; ?></span>
            </a>
            <a href="?filter=processing" class="order-tab <?php echo $filter === 'processing' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> Diproses
                <span class="tab-badge"><?php echo $processing_count; ?></span>
            </a>
            <a href="?filter=shipped" class="order-tab <?php echo $filter === 'shipped' ? 'active' : ''; ?>">
                <i class="fas fa-shipping-fast"></i> Dikirim
                <span class="tab-badge"><?php echo $shipped_count; ?></span>
            </a>
            <a href="?filter=delivered" class="order-tab <?php echo $filter === 'delivered' ? 'active' : ''; ?>">
                <i class="fas fa-check-circle"></i> Selesai
                <span class="tab-badge"><?php echo $delivered_count; ?></span>
            </a>
            <a href="?filter=cancelled" class="order-tab <?php echo $filter === 'cancelled' ? 'active' : ''; ?>">
                <i class="fas fa-times-circle"></i> Dibatalkan
                <span class="tab-badge"><?php echo $cancelled_count; ?></span>
            </a>
        </div>

        <div class="orders-section">
            <div class="section-header">
                <h2>Daftar Pesanan</h2>
                <div class="search-orders">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Cari pesanan..." id="searchOrders">
                </div>
            </div>

            <?php if (empty($filtered_orders)): ?>
                <!-- Empty Orders -->
                <div class="empty-orders">
                    <i class="fas fa-shopping-bag"></i>
                    <h3>Tidak ada pesanan</h3>
                    <p>Anda belum memiliki pesanan dengan status ini</p>
                    <a href="produk.php" class="btn-shopping">
                        <i class="fas fa-shopping-bag me-2"></i>Mulai Belanja
                    </a>
                </div>
            <?php else: ?>
                <!-- Order List -->
                <div class="order-list">
                    <?php foreach ($filtered_orders as $order): ?>
                        <?php 
                        // Fetch order items for this order
                        $items_query = "
                            SELECT oi.*, p.image_url 
                            FROM order_items oi 
                            LEFT JOIN products p ON oi.product_id = p.id 
                            WHERE oi.order_id = ? 
                            LIMIT 3
                        ";
                        $stmt = $conn->prepare($items_query);
                        $stmt->bind_param("i", $order['id']);
                        $stmt->execute();
                        $items_result = $stmt->get_result();
                        $order_items = $items_result->fetch_all(MYSQLI_ASSOC);
                        
                        // Get status badge class
                        $status_class = 'status-' . $order['status'];
                        ?>
                        
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <div class="order-number">
                                        Pesanan #<?php echo $order['order_number']; ?>
                                    </div>
                                    <div class="order-date">
                                        <?php echo date('d F Y, H:i', strtotime($order['created_at'])); ?>
                                    </div>
                                </div>
                                
                                <div class="order-status">
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php 
                                        $status_labels = [
                                            'pending' => 'Menunggu Pembayaran',
                                            'processing' => 'Diproses',
                                            'shipped' => 'Dikirim',
                                            'delivered' => 'Selesai',
                                            'cancelled' => 'Dibatalkan'
                                        ];
                                        echo $status_labels[$order['status']] ?? $order['status'];
                                        ?>
                                    </span>
                                    
                                    <div class="payment-method">
                                        <i class="fas fa-credit-card"></i>
                                        <span>
                                            <?php 
                                            $payment_labels = [
                                                'bank_transfer' => 'Transfer Bank',
                                                'qris' => 'QRIS',
                                                'cod' => 'COD'
                                            ];
                                            echo $payment_labels[$order['payment_method']] ?? $order['payment_method'];
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="order-body">
                                <div class="order-items">
                                    <?php foreach ($order_items as $item): ?>
                                        <div class="order-item">
                                            <div class="item-image">
                                                <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'img/produk-sample.png'); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                            </div>
                                            <div class="item-details">
                                                <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                                <div class="item-quantity"><?php echo $item['quantity']; ?> barang</div>
                                            </div>
                                            <div class="item-price">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <?php if ($order['item_count'] > 3): ?>
                                        <div class="text-center">
                                            <small class="text-muted">+ <?php echo $order['item_count'] - 3; ?> produk lainnya</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Status Timeline -->
                                <div class="status-timeline">
                                    <div class="timeline-title">Status Pesanan</div>
                                    <div class="timeline-steps">
                                        <div class="timeline-step <?php echo in_array($order['status'], ['pending', 'processing', 'shipped', 'delivered']) ? 'completed' : ($order['status'] === 'pending' ? 'active' : ''); ?>">
                                            <div class="step-icon">
                                                <i class="fas fa-receipt"></i>
                                            </div>
                                            <div class="step-label">Pesanan Dibuat</div>
                                            <div class="step-date"><?php echo date('d M', strtotime($order['created_at'])); ?></div>
                                        </div>
                                        
                                        <div class="timeline-step <?php echo in_array($order['status'], ['processing', 'shipped', 'delivered']) ? 'completed' : ($order['status'] === 'processing' ? 'active' : ''); ?>">
                                            <div class="step-icon">
                                                <i class="fas fa-cog"></i>
                                            </div>
                                            <div class="step-label">Diproses</div>
                                            <div class="step-date">
                                                <?php if (in_array($order['status'], ['processing', 'shipped', 'delivered'])): ?>
                                                    <?php echo date('d M', strtotime($order['updated_at'])); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="timeline-step <?php echo in_array($order['status'], ['shipped', 'delivered']) ? 'completed' : ($order['status'] === 'shipped' ? 'active' : ''); ?>">
                                            <div class="step-icon">
                                                <i class="fas fa-shipping-fast"></i>
                                            </div>
                                            <div class="step-label">Dikirim</div>
                                            <div class="step-date">
                                                <?php if (in_array($order['status'], ['shipped', 'delivered'])): ?>
                                                    <?php echo date('d M', strtotime($order['updated_at'])); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="timeline-step <?php echo $order['status'] === 'delivered' ? 'completed' : ''; ?>">
                                            <div class="step-icon">
                                                <i class="fas fa-check"></i>
                                            </div>
                                            <div class="step-label">Selesai</div>
                                            <div class="step-date">
                                                <?php if ($order['status'] === 'delivered'): ?>
                                                    <?php echo date('d M', strtotime($order['updated_at'])); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="order-footer">
                                <div class="order-total">
                                    Total: Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                                </div>
                                
                                <div class="order-actions">
                                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn-action btn-view">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    
                                    <?php if ($order['status'] === 'pending' || $order['status'] === 'processing'): ?>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <button type="submit" name="cancel_order" class="btn-action btn-cancel">
                                                <i class="fas fa-times"></i> Batalkan
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <a href="invoice.php?order_id=<?php echo $order['id']; ?>" class="btn-action btn-invoice" target="_blank">
                                        <i class="fas fa-file-invoice"></i> Invoice
                                    </a>
                                    
                                    <?php if ($order['status'] === 'shipped'): ?>
                                        <a href="track_order.php?id=<?php echo $order['id']; ?>" class="btn-action btn-track">
                                            <i class="fas fa-truck"></i> Lacak
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <div class="pagination">
                    <a href="#" class="page-link active">1</a>
                    <a href="#" class="page-link">2</a>
                    <a href="#" class="page-link">3</a>
                    <a href="#" class="page-link">Next</a>
                </div>
            <?php endif; ?>
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
        // Search functionality
        const searchInput = document.getElementById('searchOrders');
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const orderCards = document.querySelectorAll('.order-card');
            
            orderCards.forEach(card => {
                const orderNumber = card.querySelector('.order-number').textContent.toLowerCase();
                const productNames = Array.from(card.querySelectorAll('.item-name')).map(el => el.textContent.toLowerCase());
                const matches = orderNumber.includes(searchTerm) || productNames.some(name => name.includes(searchTerm));
                card.style.display = matches ? 'block' : 'none';
            });
        });

        // Filter orders by tab
        const orderTabs = document.querySelectorAll('.order-tab');
        orderTabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                const filter = this.getAttribute('href').split('=')[1];
                window.location.href = 'orders.php?filter=' + filter;
            });
        });

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
            
            // Highlight active tab in menu
            const menuLinks = document.querySelectorAll('.menu-category');
            menuLinks.forEach(link => {
                if (link.getAttribute('href') === 'orders.php' || link.textContent.includes('Pesanan')) {
                    link.classList.add('active');
                }
            });
            
            // Auto-update cart count every 30 seconds
            setInterval(updateCartCount, 30000);
        });

        // Cancel order confirmation
        const cancelForms = document.querySelectorAll('form[onsubmit]');
        cancelForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) {
                    e.preventDefault();
                }
            });
        });

        // Search bar functionality (from cart.php)
        const mainSearchInput = document.querySelector('.search-bar input');
        const mainSearchButton = document.querySelector('.search-bar button');
        
        if (mainSearchButton) {
            mainSearchButton.addEventListener('click', function() {
                const searchTerm = mainSearchInput.value.trim();
                if (searchTerm !== '') {
                    window.location.href = 'produk.php?search=' + encodeURIComponent(searchTerm);
                }
            });
        }
        
        if (mainSearchInput) {
            mainSearchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const searchTerm = this.value.trim();
                    if (searchTerm !== '') {
                        window.location.href = 'produk.php?search=' + encodeURIComponent(searchTerm);
                    }
                }
            });
        }
    </script>
</body>
</html>