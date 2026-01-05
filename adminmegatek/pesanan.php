<?php
session_start();
require_once '../config/database.php';


// --- BAGIAN 1: LOGIKA PHP UNTUK MENGAMBIL DATA DATABASE ---

// A. Ambil Statistik untuk Kartu Atas
$stats = [
    'total' => 0,
    'pending' => 0,
    'processing' => 0,
    'completed' => 0
];

try {
    // Hitung total semua
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $stats['total'] = $stmt->fetchColumn();

    // Hitung per status
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (isset($stats[$row['status']])) {
            $stats[$row['status']] = $row['count'];
        }
    }
} catch (Exception $e) {
    // Error handling silent
}

// B. Ambil Data Pesanan Lengkap (Join Users dan Order Items)
$final_orders = [];

try {
    // Query Utama: Ambil order + info user
    $query = "SELECT 
                o.id, 
                o.order_number, 
                o.created_at, 
                o.total_amount, 
                o.status, 
                o.payment_method,
                o.shipping_address,
                u.first_name, 
                u.last_name, 
                u.email, 
                u.phone_number,
                u.address as user_address
              FROM orders o 
              JOIN users u ON o.user_id = u.id 
              ORDER BY o.created_at DESC";
    
    $stmt = $pdo->query($query);
    $raw_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($raw_orders as $row) {
        // Ambil items untuk setiap order
        $stmt_items = $pdo->prepare("SELECT product_name, quantity, price, subtotal FROM order_items WHERE order_id = ?");
        $stmt_items->execute([$row['id']]);
        $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

        // Format items untuk Javascript
        $formatted_items = [];
        foreach ($items as $item) {
            $formatted_items[] = [
                'product' => $item['product_name'],
                'category' => 'Industrial Part', // Default category
                'price' => (float)$item['price'],
                'quantity' => (int)$item['quantity'],
                'subtotal' => (float)$item['subtotal']
            ];
        }

        // Susun array sesuai struktur object di Javascript sebelumnya
        $final_orders[] = [
            'id' => (int)$row['id'],
            'orderNumber' => $row['order_number'],
            'customer' => [
                'name' => $row['first_name'] . ' ' . $row['last_name'],
                'email' => $row['email'],
                'phone' => $row['phone_number'] ?? '-',
                'address' => $row['shipping_address'] ? $row['shipping_address'] : ($row['user_address'] ?? '-')
            ],
            'date' => $row['created_at'],
            'items' => $formatted_items,
            'subtotal' => (float)$row['total_amount'], // Simplifikasi (total = subtotal jika tidak ada shipping cost terpisah di db)
            'shipping' => 0, 
            'total' => (float)$row['total_amount'],
            'status' => $row['status'],
            'paymentMethod' => $row['payment_method'],
            'notes' => '-' 
        ];
    }

} catch (Exception $e) {
    echo "Error data: " . $e->getMessage();
}

// Encode ke JSON agar bisa dibaca JavaScript
$json_orders = json_encode($final_orders);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pesanan - Admin PT Megatek Industrial Persada</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #004080;
            --primary-light: #0066cc;
            --secondary: #333333;
            --accent: #e6b800;
            --light: #f5f5f5;
            --danger: #d32f2f;
            --success: #2e7d32;
            --warning: #f57c00;
            --info: #0288d1;
            --gray: #757575;
            --light-gray: #e0e0e0;
            --border-radius: 6px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f9f9f9;
            color: var(--secondary);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background-color: var(--primary);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s;
            box-shadow: var(--box-shadow);
        }

        .logo {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .logo h1 {
            font-size: 22px;
            font-weight: 700;
            color: white;
        }

        .logo h2 {
            font-size: 14px;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 5px;
        }

        .nav-menu {
            list-style: none;
            padding: 0 15px;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: all 0.3s;
        }

        .nav-link:hover, .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-link i {
            margin-right: 12px;
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 20px;
            transition: all 0.3s;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--light-gray);
            margin-bottom: 30px;
        }

        .header h1 {
            color: var(--primary);
            font-size: 28px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info span {
            font-weight: 600;
            color: var(--secondary);
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-light);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
            border-left: 5px solid var(--primary);
        }

        .card.pending { border-left-color: var(--warning); }
        .card.processing { border-left-color: var(--info); }
        .card.completed { border-left-color: var(--success); }
        .card.cancelled { border-left-color: var(--danger); }

        .card-title {
            font-size: 16px;
            color: var(--gray);
            margin-bottom: 10px;
        }

        .card-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .card.pending .card-value { color: var(--warning); }
        .card.processing .card-value { color: var(--info); }
        .card.completed .card-value { color: var(--success); }
        .card.cancelled .card-value { color: var(--danger); }

        .card-change {
            font-size: 14px;
            color: var(--success);
        }

        .card i {
            float: right;
            font-size: 40px;
            color: rgba(0, 64, 128, 0.1);
            margin-top: 10px;
        }

        .card.pending i { color: rgba(245, 124, 0, 0.1); }
        .card.processing i { color: rgba(2, 136, 209, 0.1); }
        .card.completed i { color: rgba(46, 125, 50, 0.1); }
        .card.cancelled i { color: rgba(211, 47, 47, 0.1); }

        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            margin-bottom: 25px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--secondary);
            font-size: 14px;
        }

        .filter-input, .filter-select, .filter-date {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 15px;
            transition: border 0.3s;
        }

        .filter-input:focus, .filter-select:focus, .filter-date:focus {
            outline: none;
            border-color: var(--primary);
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            margin-left: auto;
        }

        /* Orders Section */
        .orders-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .section-title {
            font-size: 22px;
            color: var(--primary);
            font-weight: 600;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            font-size: 15px;
        }

        .btn-primary { background-color: var(--primary); color: white; }
        .btn-primary:hover { background-color: var(--primary-light); }
        .btn-success { background-color: var(--success); color: white; }
        .btn-warning { background-color: var(--warning); color: white; }
        .btn-danger { background-color: var(--danger); color: white; }
        .btn-info { background-color: var(--info); color: white; }
        .btn-outline { background-color: transparent; color: var(--primary); border: 1px solid var(--primary); }
        .btn-outline:hover { background-color: rgba(0, 64, 128, 0.05); }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
            border-radius: var(--border-radius);
            border: 1px solid var(--light-gray);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        thead {
            background-color: var(--primary);
            color: white;
        }

        th {
            padding: 16px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 15px;
        }

        tbody tr {
            border-bottom: 1px solid var(--light-gray);
            transition: background-color 0.2s;
        }

        tbody tr:hover {
            background-color: rgba(0, 64, 128, 0.03);
        }

        td {
            padding: 15px;
            color: var(--secondary);
        }

        .order-id {
            color: var(--primary);
            font-weight: 600;
        }

        .customer-name { font-weight: 600; }
        .customer-email { color: var(--gray); font-size: 14px; }

        .order-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending { background-color: rgba(245, 124, 0, 0.15); color: var(--warning); }
        .status-processing { background-color: rgba(2, 136, 209, 0.15); color: var(--info); }
        .status-completed { background-color: rgba(46, 125, 50, 0.15); color: var(--success); }
        .status-cancelled { background-color: rgba(211, 47, 47, 0.15); color: var(--danger); }

        .order-total { font-weight: 700; color: var(--primary); }
        .order-items { font-size: 14px; color: var(--gray); }

        .actions { display: flex; gap: 8px; }
        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            color: white;
            font-size: 16px;
        }

        .view-btn { background-color: var(--primary); }
        .edit-btn { background-color: var(--warning); }
        .delete-btn { background-color: var(--danger); }
        .print-btn { background-color: var(--info); }
        .action-btn:hover { opacity: 0.9; transform: translateY(-2px); }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--light-gray);
        }

        .page-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 1px solid var(--light-gray);
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }

        .page-btn:hover, .page-btn.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            width: 90%;
            max-width: 900px;
            border-radius: var(--border-radius);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 20px 25px;
            background-color: var(--primary);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title { font-size: 20px; font-weight: 600; }
        .close-modal { background: none; border: none; color: white; font-size: 24px; cursor: pointer; line-height: 1; }
        .modal-body { padding: 25px; }

        .order-detail-section { margin-bottom: 30px; }
        .order-detail-title {
            font-size: 18px;
            color: var(--primary);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--light-gray);
        }

        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-item { margin-bottom: 15px; }
        .info-label { font-weight: 600; color: var(--secondary); margin-bottom: 5px; font-size: 14px; }
        .info-value { color: var(--gray); }

        .items-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .items-table th { background-color: #f5f5f5; color: var(--secondary); padding: 12px 15px; text-align: left; font-weight: 600; font-size: 14px; }
        .items-table td { padding: 12px 15px; border-bottom: 1px solid var(--light-gray); }
        .total-row { font-weight: 700; background-color: #f9f9f9; }

        .modal-footer {
            padding: 20px 25px;
            background-color: #f9f9f9;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            border-top: 1px solid var(--light-gray);
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            color: var(--gray);
            font-size: 14px;
            border-top: 1px solid var(--light-gray);
            margin-top: 20px;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar { width: 80px; }
            .sidebar .logo h1, .sidebar .logo h2, .nav-link span { display: none; }
            .sidebar .logo { text-align: center; padding: 20px 10px; }
            .nav-link i { margin-right: 0; font-size: 22px; }
            .nav-link { justify-content: center; padding: 15px; }
            .main-content { margin-left: 80px; }
        }

        @media (max-width: 768px) {
            .header { flex-direction: column; align-items: flex-start; gap: 15px; }
            .user-info { align-self: flex-end; }
            .stats-cards { grid-template-columns: 1fr; }
            .section-header { flex-direction: column; align-items: flex-start; }
            .filter-section { flex-direction: column; align-items: stretch; }
            .filter-actions { margin-left: 0; justify-content: flex-end; }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fadeIn 0.5s ease-out; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="logo">
            <h1>Megatek</h1>
            <h2>Industrial Persada</h2>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="produk.php" class="nav-link">
                    <i class="fas fa-box"></i>
                    <span>Produk</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="pesanan.php" class="nav-link active">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Pesanan</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="pelanggan.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Pelanggan</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="laporan.php" class="nav-link">
                    <i class="fas fa-chart-bar"></i>
                    <span>Laporan</span>
                </a>
            </li>
             <li class="nav-item">
                <a href="uploadbaner.php" class="nav-link">
                    <i class="fa-solid fa-download" style="color: #ffffff;"></i>
                    <span>Upload Baner</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="header">
            <div>
                <h1>Manajemen Pesanan</h1>
            </div>
            <div class="user-info">
                <span>Admin Megatek</span>
                <div class="avatar">AM</div>
            </div>
        </header>

        <div class="stats-cards">
            <div class="card fade-in">
                <div class="card-title">Total Pesanan</div>
                <div class="card-value"><?php echo $stats['total']; ?></div>
                <div class="card-change">Data Realtime</div>
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="card pending fade-in" style="animation-delay: 0.1s;">
                <div class="card-title">Pending</div>
                <div class="card-value"><?php echo $stats['pending']; ?></div>
                <div class="card-change">Menunggu konfirmasi</div>
                <i class="fas fa-clock"></i>
            </div>
            <div class="card processing fade-in" style="animation-delay: 0.2s;">
                <div class="card-title">Diproses</div>
                <div class="card-value"><?php echo $stats['processing']; ?></div>
                <div class="card-change">Sedang dikemas/dikirim</div>
                <i class="fas fa-shipping-fast"></i>
            </div>
            <div class="card completed fade-in" style="animation-delay: 0.3s;">
                <div class="card-title">Selesai</div>
                <div class="card-value"><?php echo $stats['completed']; ?></div>
                <div class="card-change">Transaksi sukses</div>
                <i class="fas fa-check-circle"></i>
            </div>
        </div>

        <section class="filter-section fade-in">
            <div class="filter-group">
                <label class="filter-label">Cari Pesanan</label>
                <input type="text" class="filter-input" id="searchOrder" placeholder="No. Pesanan, nama pelanggan">
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Status</label>
                <select class="filter-select" id="filterStatus">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Diproses</option>
                    <option value="completed">Selesai</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Tanggal Dari</label>
                <input type="date" class="filter-date" id="filterDateFrom">
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Tanggal Sampai</label>
                <input type="date" class="filter-date" id="filterDateTo">
            </div>
            
            <div class="filter-actions">
                <button class="btn btn-outline" id="resetFilterBtn">
                    <i class="fas fa-redo"></i> Reset
                </button>
            </div>
        </section>

        <section class="orders-section fade-in">
            <div class="section-header">
                <h2 class="section-title">Daftar Pesanan</h2>
                <div>
                    <span style="margin-right: 15px; color: var(--gray); font-size: 14px;">
                        <i class="fas fa-shopping-cart"></i> Total: <strong id="totalOrders">0</strong> pesanan
                    </span>
                    <button class="btn btn-primary" id="exportOrdersBtn">
                        <i class="fas fa-file-export"></i> Ekspor Data
                    </button>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Tanggal</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody">
                        </tbody>
                </table>
            </div>

            <div class="pagination">
                <button class="page-btn disabled"><i class="fas fa-chevron-left"></i></button>
                <button class="page-btn active">1</button>
                <button class="page-btn">2</button>
                <button class="page-btn">3</button>
                <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
            </div>
        </section>

        <footer class="footer">
            <p>&copy; 2025 PT Megatek Industrial Persada - Your Trusted Industrial Partner</p>
        </footer>
    </main>

    <div class="modal" id="orderDetailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="orderDetailTitle">Detail Pesanan</h3>
                <button class="close-modal" id="closeDetailModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="order-detail-section">
                    <h4 class="order-detail-title">Informasi Pesanan</h4>
                    <div class="order-info-grid">
                        <div class="info-item">
                            <div class="info-label">No. Pesanan</div>
                            <div class="info-value" id="detailOrderId"></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Tanggal Pesanan</div>
                            <div class="info-value" id="detailOrderDate"></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Status</div>
                            <div class="info-value">
                                <span class="order-status" id="detailOrderStatus"></span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Metode Pembayaran</div>
                            <div class="info-value" id="detailPaymentMethod"></div>
                        </div>
                    </div>
                </div>

                <div class="order-detail-section">
                    <h4 class="order-detail-title">Informasi Pelanggan</h4>
                    <div class="order-info-grid">
                        <div class="info-item">
                            <div class="info-label">Nama Pelanggan</div>
                            <div class="info-value" id="detailCustomerName"></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value" id="detailCustomerEmail"></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Telepon</div>
                            <div class="info-value" id="detailCustomerPhone"></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Alamat</div>
                            <div class="info-value" id="detailCustomerAddress"></div>
                        </div>
                    </div>
                </div>

                <div class="order-detail-section">
                    <h4 class="order-detail-title">Items Pesanan</h4>
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="orderItemsTable">
                            </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align: right; font-weight: 600;">Subtotal</td>
                                <td id="orderSubtotal"></td>
                            </tr>
                            <tr>
                                <td colspan="4" style="text-align: right; font-weight: 600;">Pengiriman</td>
                                <td id="orderShipping">Rp 0</td>
                            </tr>
                            <tr class="total-row">
                                <td colspan="4" style="text-align: right; font-weight: 600;">Total</td>
                                <td id="orderTotal"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="order-detail-section">
                    <h4 class="order-detail-title">Update Status</h4>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <select class="filter-select" id="updateStatusSelect" style="max-width: 200px;">
                            <option value="pending">Pending</option>
                            <option value="processing">Diproses</option>
                            <option value="completed">Selesai</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                        <button class="btn btn-primary" id="saveStatusBtn">Simpan Status</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="window.print()">
                    <i class="fas fa-print"></i> Cetak Invoice
                </button>
            </div>
        </div>
    </div>

    <script>
        // Menerima data dari PHP
        let orders = <?php echo $json_orders; ?>;
        let currentOrderId = null;

        // DOM Elements
        const ordersTableBody = document.getElementById('ordersTableBody');
        const totalOrdersElement = document.getElementById('totalOrders');
        const orderDetailModal = document.getElementById('orderDetailModal');
        const closeDetailModalBtn = document.getElementById('closeDetailModal');
        
        // Filter Elements
        const searchOrderInput = document.getElementById('searchOrder');
        const filterStatusSelect = document.getElementById('filterStatus');
        const filterDateFrom = document.getElementById('filterDateFrom');
        const filterDateTo = document.getElementById('filterDateTo');
        const resetFilterBtn = document.getElementById('resetFilterBtn');

        // Helpers
        function formatRupiah(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const options = { 
                day: 'numeric', 
                month: 'short', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            return date.toLocaleDateString('id-ID', options);
        }

        function getStatusClass(status) {
            switch(status) {
                case 'pending': return 'status-pending';
                case 'processing': return 'status-processing';
                case 'completed': return 'status-completed';
                case 'cancelled': return 'status-cancelled';
                default: return 'status-pending';
            }
        }

        // Render Table
        function renderOrders(ordersToRender) {
            ordersTableBody.innerHTML = '';
            
            if (ordersToRender.length === 0) {
                ordersTableBody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--gray);">
                            <i class="fas fa-search" style="font-size: 30px; margin-bottom: 10px; opacity: 0.5;"></i>
                            <p>Tidak ada pesanan ditemukan.</p>
                        </td>
                    </tr>
                `;
                totalOrdersElement.textContent = '0';
                return;
            }
            
            ordersToRender.forEach(order => {
                const row = document.createElement('tr');
                const totalItems = order.items.reduce((sum, item) => sum + item.quantity, 0);
                
                row.innerHTML = `
                    <td>
                        <div class="order-id">${order.orderNumber}</div>
                    </td>
                    <td>
                        <div class="customer-name">${order.customer.name}</div>
                        <div class="customer-email">${order.customer.email}</div>
                    </td>
                    <td>${formatDate(order.date)}</td>
                    <td>
                        <div class="order-items">
                            ${totalItems} items
                            <br>
                            <small>${order.items[0] ? order.items[0].product : ''}${order.items.length > 1 ? ` +${order.items.length-1} lainnya` : ''}</small>
                        </div>
                    </td>
                    <td class="order-total">${formatRupiah(order.total)}</td>
                    <td>
                        <span class="order-status ${getStatusClass(order.status)}">
                            ${order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <button class="action-btn view-btn" onclick="viewOrderDetail(${order.id})" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </td>
                `;
                ordersTableBody.appendChild(row);
            });
            
            totalOrdersElement.textContent = ordersToRender.length.toString();
        }

        // Filter Logic
        function filterOrders() {
            const searchTerm = searchOrderInput.value.toLowerCase();
            const statusFilter = filterStatusSelect.value;
            const dateFrom = filterDateFrom.value;
            const dateTo = filterDateTo.value;
            
            const filteredOrders = orders.filter(order => {
                const matchesSearch = searchTerm === '' || 
                    order.orderNumber.toLowerCase().includes(searchTerm) ||
                    order.customer.name.toLowerCase().includes(searchTerm);
                
                const matchesStatus = statusFilter === '' || order.status === statusFilter;
                
                let matchesDate = true;
                if (dateFrom) {
                    const orderDate = new Date(order.date);
                    const fromDate = new Date(dateFrom);
                    matchesDate = matchesDate && orderDate >= fromDate;
                }
                if (dateTo) {
                    const orderDate = new Date(order.date);
                    const toDate = new Date(dateTo);
                    // Set to end of day
                    toDate.setHours(23,59,59);
                    matchesDate = matchesDate && orderDate <= toDate;
                }
                
                return matchesSearch && matchesStatus && matchesDate;
            });
            
            renderOrders(filteredOrders);
        }

        // Event Listeners for Filters
        searchOrderInput.addEventListener('input', filterOrders);
        filterStatusSelect.addEventListener('change', filterOrders);
        filterDateFrom.addEventListener('change', filterOrders);
        filterDateTo.addEventListener('change', filterOrders);
        resetFilterBtn.addEventListener('click', () => {
            searchOrderInput.value = '';
            filterStatusSelect.value = '';
            filterDateFrom.value = '';
            filterDateTo.value = '';
            renderOrders(orders);
        });

        // View Detail Modal
        window.viewOrderDetail = function(id) {
            const order = orders.find(o => o.id === id);
            if (!order) return;
            
            currentOrderId = id;
            
            // Populate Data
            document.getElementById('orderDetailTitle').textContent = `Detail Pesanan ${order.orderNumber}`;
            document.getElementById('detailOrderId').textContent = order.orderNumber;
            document.getElementById('detailOrderDate').textContent = formatDate(order.date);
            
            const statusSpan = document.getElementById('detailOrderStatus');
            statusSpan.textContent = order.status.charAt(0).toUpperCase() + order.status.slice(1);
            statusSpan.className = `order-status ${getStatusClass(order.status)}`;
            
            document.getElementById('detailPaymentMethod').textContent = order.paymentMethod;
            
            document.getElementById('detailCustomerName').textContent = order.customer.name;
            document.getElementById('detailCustomerEmail').textContent = order.customer.email;
            document.getElementById('detailCustomerPhone').textContent = order.customer.phone;
            document.getElementById('detailCustomerAddress').textContent = order.customer.address;
            
            // Populate Items
            const itemsTable = document.getElementById('orderItemsTable');
            itemsTable.innerHTML = '';
            order.items.forEach(item => {
                itemsTable.innerHTML += `
                    <tr>
                        <td>${item.product}</td>
                        <td>${item.category}</td>
                        <td>${formatRupiah(item.price)}</td>
                        <td>${item.quantity}</td>
                        <td>${formatRupiah(item.subtotal)}</td>
                    </tr>
                `;
            });
            
            document.getElementById('orderSubtotal').textContent = formatRupiah(order.subtotal);
            document.getElementById('orderShipping').textContent = formatRupiah(order.shipping);
            document.getElementById('orderTotal').textContent = formatRupiah(order.total);
            
            // Set Update Status Select
            document.getElementById('updateStatusSelect').value = order.status;
            
            orderDetailModal.style.display = 'flex';
        }

        // Handle Update Status
        document.getElementById('saveStatusBtn').addEventListener('click', function() {
            if(!currentOrderId) return;
            
            const newStatus = document.getElementById('updateStatusSelect').value;
            const btn = this;
            btn.textContent = 'Menyimpan...';
            btn.disabled = true;

            // AJAX Request
            fetch('update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${currentOrderId}&status=${newStatus}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Status berhasil diperbarui!');
                    // Update local data
                    const orderIndex = orders.findIndex(o => o.id === currentOrderId);
                    if(orderIndex !== -1) {
                        orders[orderIndex].status = newStatus;
                    }
                    renderOrders(orders); // Re-render table
                    viewOrderDetail(currentOrderId); // Refresh modal view
                } else {
                    alert('Gagal memperbarui status: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan koneksi.');
            })
            .finally(() => {
                btn.textContent = 'Simpan Status';
                btn.disabled = false;
            });
        });

        // Close Modal Handlers
        closeDetailModalBtn.addEventListener('click', () => orderDetailModal.style.display = 'none');
        window.onclick = function(event) {
            if (event.target == orderDetailModal) {
                orderDetailModal.style.display = 'none';
            }
        }

        // Initialize
        renderOrders(orders);
    </script>
</body>
</html>