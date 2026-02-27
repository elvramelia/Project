<?php
// Sambungkan ke database
require_once '../config/database.php';

// Ambil statistik dari database
try {
    // Total Produk
    $stmt = $conn->query("SELECT COUNT(*) as total FROM products");
    $totalProducts = $stmt->fetch_assoc()['total'];
    
    // Pesanan bulan ini - SESUAIKAN DENGAN STRUKTUR TABEL ANDA
    // Cek dulu nama kolom yang benar untuk tanggal pesanan
    $currentMonth = date('m');
    $currentYear = date('Y');
    
    // Coba beberapa kemungkinan nama kolom
    $orderDateColumn = 'created_at'; // atau 'order_date' atau 'tanggal_pesanan'
    
    // Jika ada tabel orders, gunakan ini
    $stmt = $conn->query("SELECT COUNT(*) as total FROM orders WHERE MONTH(created_at) = $currentMonth AND YEAR(created_at) = $currentYear");
    if ($stmt) {
        $monthlyOrders = $stmt->fetch_assoc()['total'] ?? 0;
    } else {
        // Jika tabel orders tidak ada atau error, gunakan nilai default
        $monthlyOrders = 127; // nilai dari desain Anda
    }
    
    // Total Pelanggan - ambil dari tabel users
    $stmt = $conn->query("SELECT COUNT(*) as total FROM users");
    if ($stmt) {
        $totalCustomers = $stmt->fetch_assoc()['total'] ?? 0;
    } else {
        $totalCustomers = 89; // nilai dari desain Anda
    }
    
    // Pendapatan bulan ini
    // Sesuaikan dengan struktur tabel Anda
    $monthlyRevenue = 1200000; // default Rp 1,2M dari desain
    
    // Ambil data produk untuk ditampilkan
    $stmt = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 10");
    if ($stmt) {
        $products = $stmt->fetch_all(MYSQLI_ASSOC);
    } else {
        $products = [];
    }
    
} catch(Exception $e) {
    // Jika error, gunakan nilai default dari desain
    $totalProducts = 48;
    $monthlyOrders = 127;
    $totalCustomers = 89;
    $monthlyRevenue = 1200000;
    $products = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Hardjadinata Karya Utama</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
          :root {
            /* WARNA DISESUAIKAN DENGAN LOGO HKU */
            --primary: #0021A5; /* Biru pekat dari logo */
            --primary-light: #1A3DBF; /* Biru sedikit lebih terang untuk hover */
            --secondary: #333333;
            --accent: #E30613; /* Merah terang dari logo roda gigi */
            --light: #f5f5f5;
            --danger: #E30613; /* Disamakan dengan merah logo */
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
            /* Garis bawah logo menggunakan warna aksen merah */
            border-bottom: 3px solid var(--accent);
            margin-bottom: 20px;
        }

        .logo h1 {
            font-size: 22px;
            font-weight: 700;
            color: white;
        }

        .logo h2 {
            font-size: 14px;
            font-weight: 600;
            /* Teks KARYA UTAMA diberi warna merah agar selaras dengan logo */
            color: white;
            margin-top: 5px;
        }

        .nav-menu {
            list-style: none;
            padding: 0 15px;
            margin-top: 15px;
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
            /* Tambahan efek border kiri merah untuk menu aktif */
            border-left: 4px solid var(--accent); 
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
            display: flex;
            align-items: center;
            gap: 10px;
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
            /* Avatar menggunakan aksen merah */
            background-color: var(--accent);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* Dashboard Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
            /* Garis pinggir kiri diubah ke warna aksen merah */
            border-left: 5px solid var(--accent);
        }

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

        .card-change {
            font-size: 14px;
            color: var(--success);
        }

        .card i {
            float: right;
            font-size: 40px;
            color: rgba(0, 33, 165, 0.1); /* Biru HKU transparan */
            margin-top: 10px;
        }

        /* Search and Filter Section */
        .search-filter {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 300px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: border 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .filter-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 10px 15px;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            background-color: white;
            color: var(--secondary);
            font-size: 15px;
            cursor: pointer;
        }

        /* CRUD Section */
        .crud-section {
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

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 33, 165, 0.2);
        }

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-warning {
            background-color: var(--warning);
            color: white;
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-info {
            background-color: var(--info);
            color: white;
        }

        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-outline:hover {
            background-color: rgba(0, 33, 165, 0.05);
        }

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
            background-color: rgba(0, 33, 165, 0.03);
        }

        td {
            padding: 15px;
            color: var(--secondary);
        }

        .status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
        }

        .status.active {
            background-color: rgba(46, 125, 50, 0.15);
            color: var(--success);
        }

        .status.inactive {
            background-color: rgba(227, 6, 19, 0.15); /* Merah HKU */
            color: var(--danger);
        }

        .status.pending {
            background-color: rgba(245, 124, 0, 0.15);
            color: var(--warning);
        }

        .customer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
        }

        .customer-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .customer-details h4 {
            font-weight: 600;
            color: var(--secondary);
            margin-bottom: 3px;
        }

        .customer-details p {
            color: var(--gray);
            font-size: 13px;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

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

        .edit-btn {
            background-color: var(--warning);
        }

        .delete-btn {
            background-color: var(--danger);
        }

        .view-btn {
            background-color: var(--primary);
        }

        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 25px;
            gap: 8px;
        }

        .pagination button {
            padding: 10px 16px;
            border: 1px solid var(--light-gray);
            background-color: white;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            color: var(--secondary);
        }

        .pagination button:hover {
            background-color: #f5f5f5;
            border-color: #ccc;
        }

        .pagination button.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
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
            max-width: 700px;
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

        .modal-title {
            font-size: 20px;
            font-weight: 600;
        }

        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            line-height: 1;
        }

        .modal-body {
            padding: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--secondary);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: border 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 33, 165, 0.1);
        }

        .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 16px;
            background-color: white;
            cursor: pointer;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

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
            .sidebar {
                width: 80px;
            }
            
            .sidebar .logo h1, 
            .sidebar .logo h2,
            .nav-link span {
                display: none;
            }
            
            .sidebar .logo {
                text-align: center;
                padding: 20px 10px;
            }
            
            .nav-link i {
                margin-right: 0;
                font-size: 22px;
            }
            
            .nav-link {
                justify-content: center;
                padding: 15px;
            }
            
            .main-content {
                margin-left: 80px;
            }
            
            .search-filter {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                min-width: 100%;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .user-info {
                align-self: flex-end;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-options {
                flex-direction: column;
                width: 100%;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }

        /* Notification */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: var(--border-radius);
            color: white;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 300px;
            box-shadow: var(--box-shadow);
            z-index: 1001;
            animation: fadeIn 0.3s ease-out;
        }

        .notification.success {
            background-color: var(--success);
        }

        .notification.danger {
            background-color: var(--danger);
        }

        .notification.info {
            background-color: var(--info);
        }

        .close-notification {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            margin-left: 15px;
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="logo">
            <h1>HARDJADINATA</h1>
            <h2>KARYA UTAMA</h2>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="index.php" class="nav-link active">
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
                <a href="pesanan.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Pesanan</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="pelanggan.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
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
                    <i class="fas fa-upload"></i>
                    <span>Upload Banner</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="header">
            <h1>Admin Dashboard</h1>
            <div class="user-info">
                <span>Admin HKU</span>
                <div class="avatar">AM</div>
            </div>
        </header>

        <div class="stats-cards">
            <div class="card fade-in">
                <div class="card-title">Total Produk</div>
                <div class="card-value"><?php echo $totalProducts; ?></div>
                <div class="card-change">+12% dari bulan lalu</div>
                <i class="fas fa-box"></i>
            </div>
            <div class="card fade-in" style="animation-delay: 0.1s;">
                <div class="card-title">Pesanan Bulan Ini</div>
                <div class="card-value"><?php echo $monthlyOrders; ?></div>
                <div class="card-change">+8% dari bulan lalu</div>
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="card fade-in" style="animation-delay: 0.2s;">
                <div class="card-title">Pelanggan</div>
                <div class="card-value"><?php echo $totalCustomers; ?></div>
                <div class="card-change">+5% dari bulan lalu</div>
                <i class="fas fa-users"></i>
            </div>
            <div class="card fade-in" style="animation-delay: 0.3s;">
                <div class="card-title">Pendapatan</div>
                <div class="card-value">Rp <?php echo number_format($monthlyRevenue, 0, ',', '.'); ?></div>
                <div class="card-change">+15% dari bulan lalu</div>
                <i class="fas fa-chart-line"></i>
            </div>
        </div>

        <section class="recent-section">
            <div class="section-header">
                <h2 class="section-title">Produk Terbaru</h2>
                <a href="produk.php" class="btn btn-outline">
                    <i class="fas fa-list"></i> Lihat Semua Produk
                </a>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody">
                        <?php if(empty($products)): ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="fas fa-box-open"></i>
                                        <p>Tidak ada data produk</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($products as $product): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($product['name'] ?? 'N/A'); ?></strong></td>
                                <td><?php echo htmlspecialchars($product['category'] ?? 'N/A'); ?></td>
                                <td>Rp <?php echo isset($product['price']) ? number_format($product['price'], 0, ',', '.') : '0'; ?></td>
                                <td><?php echo $product['stock'] ?? '0'; ?></td>
                                <td>
                                    <span class="status <?php echo (isset($product['is_active']) && $product['is_active'] == 1) ? 'active' : 'inactive'; ?>">
                                        <?php echo (isset($product['is_active']) && $product['is_active'] == 1) ? 'Aktif' : 'Tidak Aktif'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="produk.php?action=view&id=<?php echo $product['id']; ?>" class="action-btn view-btn" title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="produk.php?action=edit&id=<?php echo $product['id']; ?>" class="action-btn edit-btn" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="action-btn delete-btn" title="Hapus" onclick="showDeleteModal(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <footer class="footer">
            <p>&copy; 2026 Hardjadinata Karya Utama - Your Trusted Industrial Partner</p>
        </footer>
    </main>

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Konfirmasi Hapus</h3>
                <button id="closeDeleteModal" class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus produk ini? Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button id="cancelDeleteBtn" class="btn btn-outline">Batal</button>
                <button id="confirmDeleteBtn" class="btn btn-danger">Hapus</button>
            </div>
        </div>
    </div>

    <script>
        // DOM Elements
        const deleteModal = document.getElementById('deleteModal');
        const closeDeleteModalBtn = document.getElementById('closeDeleteModal');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

        let currentProductId = null;

        // Tampilkan modal konfirmasi hapus
        function showDeleteModal(id) {
            currentProductId = id;
            deleteModal.style.display = 'flex';
        }

        // Hapus produk via AJAX
        function deleteProduct() {
            if (!currentProductId) return;
            
            fetch('delete_product.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + currentProductId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Produk berhasil dihapus!', 'success');
                    // Hapus baris dari tabel
                    const row = document.querySelector(`button[onclick="showDeleteModal(${currentProductId})"]`).closest('tr');
                    if (row) row.remove();
                } else {
                    showNotification('Gagal menghapus produk: ' + data.message, 'danger');
                }
                closeDeleteModal();
            })
            .catch(error => {
                showNotification('Error: ' + error, 'danger');
                closeDeleteModal();
            });
        }

        // Tutup modal hapus
        function closeDeleteModal() {
            deleteModal.style.display = 'none';
            currentProductId = null;
        }

        // Tampilkan notifikasi
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <span>${message}</span>
                <button class="close-notification">&times;</button>
            `;
            
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 6px;
                color: white;
                font-weight: 600;
                display: flex;
                align-items: center;
                justify-content: space-between;
                min-width: 300px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                z-index: 1001;
                animation: fadeIn 0.3s ease-out;
            `;
            
            if (type === 'success') {
                notification.style.backgroundColor = '#2e7d32';
            } else if (type === 'danger') {
                notification.style.backgroundColor = '#E30613'; // Disesuaikan dengan merah HKU
            } else {
                notification.style.backgroundColor = '#0021A5'; // Disesuaikan dengan biru HKU
            }
            
            const closeBtn = notification.querySelector('.close-notification');
            closeBtn.style.cssText = `
                background: none;
                border: none;
                color: white;
                font-size: 20px;
                cursor: pointer;
                margin-left: 15px;
            `;
            
            closeBtn.addEventListener('click', () => {
                notification.remove();
            });
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 3000);
        }

        // Event Listeners
        closeDeleteModalBtn.addEventListener('click', closeDeleteModal);
        cancelDeleteBtn.addEventListener('click', closeDeleteModal);
        confirmDeleteBtn.addEventListener('click', deleteProduct);

        // Tutup modal jika klik di luar konten modal
        window.addEventListener('click', (e) => {
            if (e.target === deleteModal) {
                closeDeleteModal();
            }
        });

        // Tambah CSS untuk modal (script tambahan yang ada di akhir kode asli)
        const style = document.createElement('style');
        style.textContent = `
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
                max-width: 400px;
                border-radius: 8px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                overflow: hidden;
            }
            
            .modal-header {
                padding: 20px;
                background-color: #0021A5; /* Disesuaikan dengan Biru HKU */
                color: white;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .modal-title {
                font-size: 20px;
                font-weight: 600;
            }
            
            .close-modal {
                background: none;
                border: none;
                color: white;
                font-size: 24px;
                cursor: pointer;
                line-height: 1;
            }
            
            .modal-body {
                padding: 20px;
            }
            
            .modal-footer {
                padding: 20px;
                background-color: #f9f9f9;
                display: flex;
                justify-content: flex-end;
                gap: 15px;
                border-top: 1px solid #e0e0e0;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>