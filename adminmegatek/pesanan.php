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

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--gray);
            font-size: 14px;
            margin-top: 5px;
        }

        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .breadcrumb i {
            font-size: 12px;
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

        .card.pending {
            border-left-color: var(--warning);
        }

        .card.processing {
            border-left-color: var(--info);
        }

        .card.completed {
            border-left-color: var(--success);
        }

        .card.cancelled {
            border-left-color: var(--danger);
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

        .card.pending .card-value {
            color: var(--warning);
        }

        .card.processing .card-value {
            color: var(--info);
        }

        .card.completed .card-value {
            color: var(--success);
        }

        .card.cancelled .card-value {
            color: var(--danger);
        }

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

        .card.pending i {
            color: rgba(245, 124, 0, 0.1);
        }

        .card.processing i {
            color: rgba(2, 136, 209, 0.1);
        }

        .card.completed i {
            color: rgba(46, 125, 50, 0.1);
        }

        .card.cancelled i {
            color: rgba(211, 47, 47, 0.1);
        }

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

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-light);
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
            background-color: rgba(0, 64, 128, 0.05);
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

        .customer-name {
            font-weight: 600;
        }

        .customer-email {
            color: var(--gray);
            font-size: 14px;
        }

        .order-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending {
            background-color: rgba(245, 124, 0, 0.15);
            color: var(--warning);
        }

        .status-processing {
            background-color: rgba(2, 136, 209, 0.15);
            color: var(--info);
        }

        .status-completed {
            background-color: rgba(46, 125, 50, 0.15);
            color: var(--success);
        }

        .status-cancelled {
            background-color: rgba(211, 47, 47, 0.15);
            color: var(--danger);
        }

        .order-total {
            font-weight: 700;
            color: var(--primary);
        }

        .order-items {
            font-size: 14px;
            color: var(--gray);
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

        .view-btn {
            background-color: var(--primary);
        }

        .edit-btn {
            background-color: var(--warning);
        }

        .delete-btn {
            background-color: var(--danger);
        }

        .print-btn {
            background-color: var(--info);
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

        .page-btn:hover {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .page-btn.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .page-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .page-btn.disabled:hover {
            background-color: white;
            color: inherit;
            border-color: var(--light-gray);
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

        .order-detail-section {
            margin-bottom: 30px;
        }

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

        .info-item {
            margin-bottom: 15px;
        }

        .info-label {
            font-weight: 600;
            color: var(--secondary);
            margin-bottom: 5px;
            font-size: 14px;
        }

        .info-value {
            color: var(--gray);
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .items-table th {
            background-color: #f5f5f5;
            color: var(--secondary);
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .items-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--light-gray);
        }

        .total-row {
            font-weight: 700;
            background-color: #f9f9f9;
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

        .form-control, .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: border 0.3s;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
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
            
            .filter-section {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-actions {
                margin-left: 0;
                justify-content: flex-end;
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

        /* Modal Ekspor Data */
        .export-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .export-option {
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .export-option:hover {
            border-color: var(--primary);
            background-color: rgba(0, 64, 128, 0.05);
        }

        .export-option.selected {
            border-color: var(--primary);
            background-color: rgba(0, 64, 128, 0.1);
        }

        .export-option i {
            font-size: 24px;
            color: var(--primary);
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
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

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <div>
                <h1>Manajemen Pesanan</h1>
                 </div>
            <div class="user-info">
                <span>Admin Megatek</span>
                <div class="avatar">AM</div>
            </div>
        </header>

        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="card fade-in">
                <div class="card-title">Total Pesanan</div>
                <div class="card-value">127</div>
                <div class="card-change">+8% dari bulan lalu</div>
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="card pending fade-in" style="animation-delay: 0.1s;">
                <div class="card-title">Pending</div>
                <div class="card-value">24</div>
                <div class="card-change">3 menunggu konfirmasi</div>
                <i class="fas fa-clock"></i>
            </div>
            <div class="card processing fade-in" style="animation-delay: 0.2s;">
                <div class="card-title">Diproses</div>
                <div class="card-value">18</div>
                <div class="card-change">5 sedang dikemas</div>
                <i class="fas fa-shipping-fast"></i>
            </div>
            <div class="card completed fade-in" style="animation-delay: 0.3s;">
                <div class="card-title">Selesai</div>
                <div class="card-value">82</div>
                <div class="card-change">+12% dari bulan lalu</div>
                <i class="fas fa-check-circle"></i>
            </div>
        </div>

        <!-- Filter Section -->
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
                <button class="btn btn-primary" id="applyFilterBtn">
                    <i class="fas fa-filter"></i> Terapkan Filter
                </button>
            </div>
        </section>

        <!-- Orders Section -->
        <section class="orders-section fade-in">
            <div class="section-header">
                <h2 class="section-title">Daftar Pesanan</h2>
                <div>
                    <span style="margin-right: 15px; color: var(--gray); font-size: 14px;">
                        <i class="fas fa-shopping-cart"></i> Total: <strong id="totalOrders">24</strong> pesanan
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
                        <!-- Data pesanan akan dimuat di sini melalui JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <button class="page-btn disabled" id="prevPage">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="page-btn active">1</button>
                <button class="page-btn">2</button>
                <button class="page-btn">3</button>
                <span style="padding: 0 10px; color: var(--gray);">...</span>
                <button class="page-btn">5</button>
                <button class="page-btn" id="nextPage">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <p>&copy; 2025 PT Megatek Industrial Persada - Your Trusted Industrial Partner</p>
        </footer>
    </main>

    <!-- Modal Detail Pesanan -->
    <div class="modal" id="orderDetailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="orderDetailTitle">Detail Pesanan #ORD-001</h3>
                <button class="close-modal" id="closeDetailModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="order-detail-section">
                    <h4 class="order-detail-title">Informasi Pesanan</h4>
                    <div class="order-info-grid">
                        <div class="info-item">
                            <div class="info-label">No. Pesanan</div>
                            <div class="info-value" id="detailOrderId">#ORD-001</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Tanggal Pesanan</div>
                            <div class="info-value" id="detailOrderDate">18 Des 2025, 10:30</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Status</div>
                            <div class="info-value">
                                <span class="order-status" id="detailOrderStatus">Pending</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Metode Pembayaran</div>
                            <div class="info-value" id="detailPaymentMethod">Transfer Bank</div>
                        </div>
                    </div>
                </div>

                <div class="order-detail-section">
                    <h4 class="order-detail-title">Informasi Pelanggan</h4>
                    <div class="order-info-grid">
                        <div class="info-item">
                            <div class="info-label">Nama Pelanggan</div>
                            <div class="info-value" id="detailCustomerName">PT Industri Maju Jaya</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value" id="detailCustomerEmail">contact@industrimajujaya.com</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Telepon</div>
                            <div class="info-value" id="detailCustomerPhone">0812-3456-7890</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Alamat</div>
                            <div class="info-value" id="detailCustomerAddress">Jl. Industri Raya No. 123, Jakarta</div>
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
                            <!-- Items akan dimuat di sini -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align: right; font-weight: 600;">Subtotal</td>
                                <td id="orderSubtotal">Rp 12.500.000</td>
                            </tr>
                            <tr>
                                <td colspan="4" style="text-align: right; font-weight: 600;">Pengiriman</td>
                                <td id="orderShipping">Rp 150.000</td>
                            </tr>
                            <tr class="total-row">
                                <td colspan="4" style="text-align: right; font-weight: 600;">Total</td>
                                <td id="orderTotal">Rp 12.650.000</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="order-detail-section">
                    <h4 class="order-detail-title">Catatan Pesanan</h4>
                    <div class="info-item">
                        <div class="info-value" id="orderNotes">Mohon dikirim secepatnya, produk dibutuhkan untuk proyek yang sedang berjalan.</div>
                    </div>
                </div>

                <div class="order-detail-section">
                    <h4 class="order-detail-title">Update Status</h4>
                    <div class="form-group">
                        <select class="form-select" id="updateStatusSelect">
                            <option value="pending">Pending</option>
                            <option value="processing">Diproses</option>
                            <option value="completed">Selesai</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="printInvoiceBtn">
                    <i class="fas fa-print"></i> Cetak Invoice
                </button>
                <button class="btn btn-primary" id="updateStatusBtn">
                    <i class="fas fa-save"></i> Update Status
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Edit Pesanan -->
    <div class="modal" id="editOrderModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Pesanan</h3>
                <button class="close-modal" id="closeEditModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editOrderForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Status Pesanan</label>
                            <select class="form-select" id="editOrderStatus">
                                <option value="pending">Pending</option>
                                <option value="processing">Diproses</option>
                                <option value="completed">Selesai</option>
                                <option value="cancelled">Dibatalkan</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Metode Pembayaran</label>
                            <select class="form-select" id="editPaymentMethod">
                                <option value="transfer">Transfer Bank</option>
                                <option value="credit_card">Kartu Kredit</option>
                                <option value="cash">Tunai</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Catatan Admin</label>
                        <textarea class="form-control" id="editAdminNotes" rows="3" placeholder="Tambahkan catatan internal..."></textarea>
                    </div>
                    
                    <input type="hidden" id="editOrderId">
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelEditBtn">Batal</button>
                <button class="btn btn-primary" id="saveEditBtn">Simpan Perubahan</button>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Konfirmasi Hapus</h3>
                <button class="close-modal" id="closeDeleteModal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus pesanan ini? Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelDeleteBtn">Batal</button>
                <button class="btn btn-danger" id="confirmDeleteBtn">Hapus Pesanan</button>
            </div>
        </div>
    </div>

    <!-- Modal Ekspor Data -->
    <div class="modal" id="exportModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Ekspor Data Pesanan</h3>
                <button class="close-modal" id="closeExportModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Format Ekspor</label>
                    <div class="export-options">
                        <div class="export-option selected" data-format="csv">
                            <i class="fas fa-file-csv"></i>
                            <div><strong>CSV</strong></div>
                            <small>Format spreadsheet sederhana</small>
                        </div>
                        <div class="export-option" data-format="excel">
                            <i class="fas fa-file-excel"></i>
                            <div><strong>Excel</strong></div>
                            <small>Format Microsoft Excel</small>
                        </div>
                        <div class="export-option" data-format="pdf">
                            <i class="fas fa-file-pdf"></i>
                            <div><strong>PDF</strong></div>
                            <small>Format dokumen portabel</small>
                        </div>
                        <div class="export-option" data-format="json">
                            <i class="fas fa-file-code"></i>
                            <div><strong>JSON</strong></div>
                            <small>Format data terstruktur</small>
                        </div>
                    </div>
                    <input type="hidden" id="exportFormat" value="csv">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Rentang Tanggal</label>
                    <div class="form-row">
                        <div class="form-group">
                            <input type="date" class="form-control" id="exportDateFrom" value="">
                        </div>
                        <div class="form-group">
                            <input type="date" class="form-control" id="exportDateTo" value="">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status Pesanan</label>
                    <select class="form-select" id="exportStatus">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Diproses</option>
                        <option value="completed">Selesai</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <div class="info-item">
                        <div class="info-label">Ringkasan Ekspor</div>
                        <div class="info-value" id="exportSummary">
                            Menyiapkan data untuk diekspor...
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelExportBtn">Batal</button>
                <button class="btn btn-primary" id="confirmExportBtn">
                    <i class="fas fa-download"></i> Unduh Data
                </button>
            </div>
        </div>
    </div>

    <script>
        // Data pesanan contoh
        let orders = [
            {
                id: 1,
                orderNumber: "ORD-2025-001",
                customer: {
                    name: "PT Industri Maju Jaya",
                    email: "contact@industrimajujaya.com",
                    phone: "0812-3456-7890",
                    address: "Jl. Industri Raya No. 123, Jakarta"
                },
                date: "2025-12-18 10:30",
                items: [
                    { product: "Sporeport Pro X200", category: "Sporeport", price: 12500000, quantity: 1 },
                    { product: "Control Valve AV100", category: "Valve & Instrumentation", price: 3500000, quantity: 2 }
                ],
                subtotal: 19500000,
                shipping: 150000,
                total: 19650000,
                status: "pending",
                paymentMethod: "transfer",
                notes: "Mohon dikirim secepatnya, produk dibutuhkan untuk proyek yang sedang berjalan."
            },
            {
                id: 2,
                orderNumber: "ORD-2025-002",
                customer: {
                    name: "CV Teknik Mandiri",
                    email: "info@teknikmandiri.co.id",
                    phone: "0813-9876-5432",
                    address: "Jl. Teknik No. 45, Bandung"
                },
                date: "2025-12-17 14:20",
                items: [
                    { product: "FBR Burner Eco Series", category: "FBR Burner", price: 8500000, quantity: 1 },
                    { product: "Pressure Valve PV300", category: "Valve & Instrumentation", price: 4200000, quantity: 1 }
                ],
                subtotal: 12700000,
                shipping: 120000,
                total: 12820000,
                status: "processing",
                paymentMethod: "credit_card",
                notes: "Sudah konfirmasi pembayaran via kartu kredit."
            },
            {
                id: 3,
                orderNumber: "ORD-2025-003",
                customer: {
                    name: "PT Pabrik Kimia Nusantara",
                    email: "purchasing@kimianusantara.com",
                    phone: "0821-1122-3344",
                    address: "Kawasan Industri Cikarang, Bekasi"
                },
                date: "2025-12-16 09:15",
                items: [
                    { product: "Boiler SteamMaster 500", category: "Boiler", price: 185000000, quantity: 1 }
                ],
                subtotal: 185000000,
                shipping: 500000,
                total: 185500000,
                status: "completed",
                paymentMethod: "transfer",
                notes: "Pesanan khusus dengan spesifikasi tambahan."
            },
            {
                id: 4,
                orderNumber: "ORD-2025-004",
                customer: {
                    name: "UD Sinar Abadi",
                    email: "ud.sinarabadi@gmail.com",
                    phone: "0856-7788-9900",
                    address: "Jl. Raya Surabaya No. 88, Gresik"
                },
                date: "2025-12-15 16:45",
                items: [
                    { product: "FBR Burner Heavy Duty", category: "FBR Burner", price: 12000000, quantity: 2 },
                    { product: "Sporeport Mini S50", category: "Sporeport", price: 7500000, quantity: 1 }
                ],
                subtotal: 31500000,
                shipping: 200000,
                total: 31700000,
                status: "pending",
                paymentMethod: "cash",
                notes: "Akan mengambil langsung di gudang."
            },
            {
                id: 5,
                orderNumber: "ORD-2025-005",
                customer: {
                    name: "PT Energi Terbarukan Indonesia",
                    email: "order@energiterbarukan.id",
                    phone: "0815-6677-8899",
                    address: "Jl. Gatot Subroto No. 12, Jakarta Selatan"
                },
                date: "2025-12-14 11:30",
                items: [
                    { product: "Boiler Compact 200", category: "Boiler", price: 95000000, quantity: 1 },
                    { product: "Flow Meter FM200", category: "Valve & Instrumentation", price: 2800000, quantity: 3 }
                ],
                subtotal: 103400000,
                shipping: 300000,
                total: 103700000,
                status: "completed",
                paymentMethod: "transfer",
                notes: ""
            },
            {
                id: 6,
                orderNumber: "ORD-2025-006",
                customer: {
                    name: "CV Mekanika Presisi",
                    email: "sales@mekanikapresisi.com",
                    phone: "0878-1234-5678",
                    address: "Jl. Industri No. 33, Tangerang"
                },
                date: "2025-12-13 13:20",
                items: [
                    { product: "Sporeport Advanced A500", category: "Sporeport", price: 18500000, quantity: 1 },
                    { product: "Control Valve AV100", category: "Valve & Instrumentation", price: 3500000, quantity: 4 }
                ],
                subtotal: 32500000,
                shipping: 180000,
                total: 32680000,
                status: "cancelled",
                paymentMethod: "credit_card",
                notes: "Dibatalkan oleh pelanggan karena perubahan proyek."
            },
            {
                id: 7,
                orderNumber: "ORD-2025-007",
                customer: {
                    name: "PT Manufaktur Cerdas",
                    email: "procurement@manufakturcerdas.co.id",
                    phone: "0823-4455-6677",
                    address: "Jl. Sudirman No. 45, Medan"
                },
                date: "2025-12-12 15:10",
                items: [
                    { product: "FBR Burner Compact", category: "FBR Burner", price: 6500000, quantity: 3 },
                    { product: "Pressure Valve PV300", category: "Valve & Instrumentation", price: 4200000, quantity: 2 }
                ],
                subtotal: 27900000,
                shipping: 220000,
                total: 28120000,
                status: "processing",
                paymentMethod: "transfer",
                notes: "Konfirmasi pembayaran sudah diterima."
            },
            {
                id: 8,
                orderNumber: "ORD-2025-008",
                customer: {
                    name: "UD Jaya Teknik",
                    email: "jaya.teknik@yahoo.com",
                    phone: "0811-2233-4455",
                    address: "Jl. Diponegoro No. 78, Semarang"
                },
                date: "2025-12-11 10:05",
                items: [
                    { product: "Sporeport Pro X200", category: "Sporeport", price: 12500000, quantity: 2 }
                ],
                subtotal: 25000000,
                shipping: 150000,
                total: 25150000,
                status: "pending",
                paymentMethod: "cash",
                notes: "Menunggu konfirmasi stok."
            }
        ];

        // DOM Elements
        const ordersTableBody = document.getElementById('ordersTableBody');
        const totalOrdersElement = document.getElementById('totalOrders');
        const orderDetailModal = document.getElementById('orderDetailModal');
        const editOrderModal = document.getElementById('editOrderModal');
        const deleteModal = document.getElementById('deleteModal');
        const exportModal = document.getElementById('exportModal');
        const exportOrdersBtn = document.getElementById('exportOrdersBtn');
        const closeDetailModalBtn = document.getElementById('closeDetailModal');
        const closeEditModalBtn = document.getElementById('closeEditModal');
        const closeDeleteModalBtn = document.getElementById('closeDeleteModal');
        const closeExportModalBtn = document.getElementById('closeExportModal');
        const cancelEditBtn = document.getElementById('cancelEditBtn');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        const cancelExportBtn = document.getElementById('cancelExportBtn');
        const printInvoiceBtn = document.getElementById('printInvoiceBtn');
        const updateStatusBtn = document.getElementById('updateStatusBtn');
        const saveEditBtn = document.getElementById('saveEditBtn');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const confirmExportBtn = document.getElementById('confirmExportBtn');
        const searchOrderInput = document.getElementById('searchOrder');
        const filterStatusSelect = document.getElementById('filterStatus');
        const filterDateFrom = document.getElementById('filterDateFrom');
        const filterDateTo = document.getElementById('filterDateTo');
        const applyFilterBtn = document.getElementById('applyFilterBtn');
        const resetFilterBtn = document.getElementById('resetFilterBtn');
        const exportOptions = document.querySelectorAll('.export-option');
        const exportFormatInput = document.getElementById('exportFormat');
        const exportDateFrom = document.getElementById('exportDateFrom');
        const exportDateTo = document.getElementById('exportDateTo');
        const exportStatus = document.getElementById('exportStatus');
        const exportSummary = document.getElementById('exportSummary');

        let currentOrderId = null;
        let filteredOrders = [...orders];

        // Format angka menjadi Rupiah
        function formatRupiah(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        }

        // Format tanggal
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

        // Get status class
        function getStatusClass(status) {
            switch(status) {
                case 'pending': return 'status-pending';
                case 'processing': return 'status-processing';
                case 'completed': return 'status-completed';
                case 'cancelled': return 'status-cancelled';
                default: return 'status-pending';
            }
        }

        // Get status text
        function getStatusText(status) {
            switch(status) {
                case 'pending': return 'Pending';
                case 'processing': return 'Diproses';
                case 'completed': return 'Selesai';
                case 'cancelled': return 'Dibatalkan';
                default: return 'Pending';
            }
        }

        // Get payment method text
        function getPaymentMethodText(method) {
            switch(method) {
                case 'transfer': return 'Transfer Bank';
                case 'credit_card': return 'Kartu Kredit';
                case 'cash': return 'Tunai';
                default: return 'Transfer Bank';
            }
        }

        // Fungsi untuk mengunduh file
        function downloadFile(content, fileName, mimeType) {
            const blob = new Blob([content], { type: mimeType });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = fileName;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        // Render tabel pesanan
        function renderOrders(ordersToRender = filteredOrders) {
            ordersTableBody.innerHTML = '';
            
            if (ordersToRender.length === 0) {
                ordersTableBody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--gray);">
                            <i class="fas fa-shopping-cart" style="font-size: 40px; margin-bottom: 15px; opacity: 0.5;"></i>
                            <h3 style="margin-bottom: 10px;">Tidak ada pesanan ditemukan</h3>
                            <p>Coba ubah filter pencarian.</p>
                        </td>
                    </tr>
                `;
                totalOrdersElement.textContent = '0';
                return;
            }
            
            ordersToRender.forEach(order => {
                const row = document.createElement('tr');
                
                // Hitung total items
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
                            ${totalItems} item${totalItems > 1 ? 's' : ''}
                            <br>
                            <small>${order.items[0].product}${order.items.length > 1 ? ` +${order.items.length-1} lainnya` : ''}</small>
                        </div>
                    </td>
                    <td class="order-total">${formatRupiah(order.total)}</td>
                    <td>
                        <span class="order-status ${getStatusClass(order.status)}">
                            ${getStatusText(order.status)}
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <button class="action-btn view-btn" onclick="viewOrderDetail(${order.id})" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="action-btn edit-btn" onclick="editOrder(${order.id})" title="Edit Pesanan">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn print-btn" onclick="printInvoice(${order.id})" title="Cetak Invoice">
                                <i class="fas fa-print"></i>
                            </button>
                            <button class="action-btn delete-btn" onclick="showDeleteModal(${order.id})" title="Hapus Pesanan">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                
                ordersTableBody.appendChild(row);
            });
            
            totalOrdersElement.textContent = ordersToRender.length.toString();
        }

        // Filter pesanan
        function filterOrders() {
            const searchTerm = searchOrderInput.value.toLowerCase();
            const statusFilter = filterStatusSelect.value;
            const dateFrom = filterDateFrom.value;
            const dateTo = filterDateTo.value;
            
            filteredOrders = orders.filter(order => {
                // Filter berdasarkan pencarian
                const matchesSearch = searchTerm === '' || 
                    order.orderNumber.toLowerCase().includes(searchTerm) ||
                    order.customer.name.toLowerCase().includes(searchTerm) ||
                    order.customer.email.toLowerCase().includes(searchTerm);
                
                // Filter berdasarkan status
                const matchesStatus = statusFilter === '' || order.status === statusFilter;
                
                // Filter berdasarkan tanggal
                let matchesDate = true;
                if (dateFrom) {
                    const orderDate = new Date(order.date.split(' ')[0]);
                    const fromDate = new Date(dateFrom);
                    matchesDate = matchesDate && orderDate >= fromDate;
                }
                
                if (dateTo) {
                    const orderDate = new Date(order.date.split(' ')[0]);
                    const toDate = new Date(dateTo);
                    matchesDate = matchesDate && orderDate <= toDate;
                }
                
                return matchesSearch && matchesStatus && matchesDate;
            });
            
            renderOrders(filteredOrders);
        }

        // Reset filter
        function resetFilter() {
            searchOrderInput.value = '';
            filterStatusSelect.value = '';
            filterDateFrom.value = '';
            filterDateTo.value = '';
            filteredOrders = [...orders];
            renderOrders(filteredOrders);
        }

        // View order detail
        function viewOrderDetail(id) {
            const order = orders.find(o => o.id === id);
            
            if (order) {
                // Update modal title
                document.getElementById('orderDetailTitle').textContent = `Detail Pesanan ${order.orderNumber}`;
                
                // Update order info
                document.getElementById('detailOrderId').textContent = order.orderNumber;
                document.getElementById('detailOrderDate').textContent = formatDate(order.date);
                document.getElementById('detailOrderStatus').textContent = getStatusText(order.status);
                document.getElementById('detailOrderStatus').className = `order-status ${getStatusClass(order.status)}`;
                document.getElementById('detailPaymentMethod').textContent = getPaymentMethodText(order.paymentMethod);
                
                // Update customer info
                document.getElementById('detailCustomerName').textContent = order.customer.name;
                document.getElementById('detailCustomerEmail').textContent = order.customer.email;
                document.getElementById('detailCustomerPhone').textContent = order.customer.phone;
                document.getElementById('detailCustomerAddress').textContent = order.customer.address;
                
                // Update order items
                const itemsTable = document.getElementById('orderItemsTable');
                itemsTable.innerHTML = '';
                
                order.items.forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.product}</td>
                        <td>${item.category}</td>
                        <td>${formatRupiah(item.price)}</td>
                        <td>${item.quantity}</td>
                        <td>${formatRupiah(item.price * item.quantity)}</td>
                    `;
                    itemsTable.appendChild(row);
                });
                
                // Update totals
                document.getElementById('orderSubtotal').textContent = formatRupiah(order.subtotal);
                document.getElementById('orderShipping').textContent = formatRupiah(order.shipping);
                document.getElementById('orderTotal').textContent = formatRupiah(order.total);
                
                // Update notes
                document.getElementById('orderNotes').textContent = order.notes || 'Tidak ada catatan.';
                
                // Set current order ID and status
                currentOrderId = order.id;
                document.getElementById('updateStatusSelect').value = order.status;
                
                // Show modal
                orderDetailModal.style.display = 'flex';
            }
        }

        // Update order status
        function updateOrderStatus() {
            if (!currentOrderId) return;
            
            const newStatus = document.getElementById('updateStatusSelect').value;
            const orderIndex = orders.findIndex(o => o.id === currentOrderId);
            
            if (orderIndex !== -1) {
                orders[orderIndex].status = newStatus;
                filterOrders(); // Re-render with filters
                orderDetailModal.style.display = 'none';
                showNotification('Status pesanan berhasil diperbarui!', 'success');
            }
        }

        // Edit order
        function editOrder(id) {
            const order = orders.find(o => o.id === id);
            
            if (order) {
                currentOrderId = order.id;
                document.getElementById('editOrderId').value = order.id;
                document.getElementById('editOrderStatus').value = order.status;
                document.getElementById('editPaymentMethod').value = order.paymentMethod;
                document.getElementById('editAdminNotes').value = order.adminNotes || '';
                
                editOrderModal.style.display = 'flex';
            }
        }

        // Save edited order
        function saveEditedOrder() {
            if (!currentOrderId) return;
            
            const orderIndex = orders.findIndex(o => o.id === currentOrderId);
            
            if (orderIndex !== -1) {
                orders[orderIndex].status = document.getElementById('editOrderStatus').value;
                orders[orderIndex].paymentMethod = document.getElementById('editPaymentMethod').value;
                orders[orderIndex].adminNotes = document.getElementById('editAdminNotes').value;
                
                filterOrders(); // Re-render with filters
                editOrderModal.style.display = 'none';
                showNotification('Pesanan berhasil diperbarui!', 'success');
            }
        }

        // Fungsi untuk menghasilkan invoice HTML
        function generateInvoiceHTML(order) {
            const totalItems = order.items.reduce((sum, item) => sum + item.quantity, 0);
            
            return `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Invoice ${order.orderNumber} - PT Megatek Industrial Persada</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            margin: 0;
                            padding: 20px;
                            color: #333;
                        }
                        .invoice-container {
                            max-width: 800px;
                            margin: 0 auto;
                            border: 1px solid #ddd;
                            padding: 30px;
                            background: white;
                        }
                        .header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-start;
                            margin-bottom: 30px;
                            padding-bottom: 20px;
                            border-bottom: 2px solid #004080;
                        }
                        .company-info {
                            flex: 1;
                        }
                        .logo {
                            font-size: 28px;
                            font-weight: bold;
                            color: #004080;
                            margin-bottom: 5px;
                        }
                        .company-name {
                            font-weight: bold;
                            color: #004080;
                            font-size: 18px;
                        }
                        .invoice-info {
                            text-align: right;
                        }
                        .invoice-title {
                            font-size: 24px;
                            font-weight: bold;
                            color: #004080;
                            margin-bottom: 10px;
                        }
                        .details-grid {
                            display: grid;
                            grid-template-columns: 1fr 1fr;
                            gap: 30px;
                            margin-bottom: 30px;
                        }
                        .section {
                            margin-bottom: 20px;
                        }
                        .section-title {
                            font-weight: bold;
                            color: #004080;
                            margin-bottom: 10px;
                            padding-bottom: 5px;
                            border-bottom: 1px solid #ddd;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin: 20px 0;
                        }
                        th {
                            background-color: #004080;
                            color: white;
                            padding: 12px;
                            text-align: left;
                            font-weight: bold;
                        }
                        td {
                            padding: 10px;
                            border-bottom: 1px solid #ddd;
                        }
                        .total-row {
                            font-weight: bold;
                            background-color: #f5f5f5;
                        }
                        .text-right {
                            text-align: right;
                        }
                        .footer {
                            margin-top: 50px;
                            padding-top: 20px;
                            border-top: 2px solid #004080;
                            text-align: center;
                            color: #666;
                            font-size: 12px;
                        }
                        .status-badge {
                            display: inline-block;
                            padding: 5px 15px;
                            border-radius: 20px;
                            font-weight: bold;
                        }
                        .status-pending {
                            background-color: #fff3e0;
                            color: #f57c00;
                        }
                        .status-processing {
                            background-color: #e3f2fd;
                            color: #0288d1;
                        }
                        .status-completed {
                            background-color: #e8f5e8;
                            color: #2e7d32;
                        }
                        .status-cancelled {
                            background-color: #ffebee;
                            color: #d32f2f;
                        }
                        @media print {
                            body {
                                margin: 0;
                                padding: 0;
                            }
                            .invoice-container {
                                border: none;
                                padding: 0;
                            }
                            .no-print {
                                display: none !important;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="invoice-container">
                        <div class="header">
                            <div class="company-info">
                                <div class="logo">MEGATEK</div>
                                <div class="company-name">PT Megatek Industrial Persada</div>
                                <div>Your Trusted Industrial Partner</div>
                                <div>Jl. Industri Raya No. 123, Jakarta</div>
                                <div>Telp: (021) 12345678 | Email: info@megatek.co.id</div>
                            </div>
                            <div class="invoice-info">
                                <div class="invoice-title">INVOICE</div>
                                <div><strong>No. Invoice:</strong> ${order.orderNumber}</div>
                                <div><strong>Tanggal:</strong> ${formatDate(order.date)}</div>
                                <div><strong>Status:</strong> 
                                    <span class="status-badge ${getStatusClass(order.status)}">
                                        ${getStatusText(order.status)}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="details-grid">
                            <div class="section">
                                <div class="section-title">Informasi Pelanggan</div>
                                <div><strong>${order.customer.name}</strong></div>
                                <div>${order.customer.email}</div>
                                <div>${order.customer.phone}</div>
                                <div>${order.customer.address}</div>
                            </div>
                            
                            <div class="section">
                                <div class="section-title">Informasi Pembayaran</div>
                                <div><strong>Metode Pembayaran:</strong> ${getPaymentMethodText(order.paymentMethod)}</div>
                                <div><strong>Jatuh Tempo:</strong> ${formatDate(new Date(Date.now() + 7 * 24 * 60 * 60 * 1000))}</div>
                                <div><strong>No. Referensi:</strong> REF-${order.id.toString().padStart(3, '0')}</div>
                            </div>
                        </div>
                        
                        <table>
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Kategori</th>
                                    <th>Harga Satuan</th>
                                    <th>Qty</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${order.items.map(item => `
                                    <tr>
                                        <td>${item.product}</td>
                                        <td>${item.category}</td>
                                        <td>${formatRupiah(item.price)}</td>
                                        <td>${item.quantity}</td>
                                        <td class="text-right">${formatRupiah(item.price * item.quantity)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-right"><strong>Subtotal:</strong></td>
                                    <td class="text-right">${formatRupiah(order.subtotal)}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-right"><strong>Biaya Pengiriman:</strong></td>
                                    <td class="text-right">${formatRupiah(order.shipping)}</td>
                                </tr>
                                <tr class="total-row">
                                    <td colspan="4" class="text-right"><strong>TOTAL:</strong></td>
                                    <td class="text-right">${formatRupiah(order.total)}</td>
                                </tr>
                            </tfoot>
                        </table>
                        
                        <div class="section">
                            <div class="section-title">Catatan</div>
                            <div>${order.notes || 'Tidak ada catatan.'}</div>
                        </div>
                        
                        <div class="footer">
                            <p>Terima kasih atas kepercayaan Anda menggunakan produk PT Megatek Industrial Persada</p>
                            <p>Invoice ini sah dan dapat digunakan sebagai bukti transaksi</p>
                            <p>Dicetak pada: ${new Date().toLocaleString('id-ID')}</p>
                        </div>
                        
                        <div class="no-print" style="text-align: center; margin-top: 30px;">
                            <button onclick="window.print()" style="padding: 10px 20px; background: #004080; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                <i class="fas fa-print"></i> Cetak Invoice
                            </button>
                        </div>
                    </div>
                </body>
                </html>
            `;
        }

        // Print invoice
        function printInvoice(id) {
            const order = orders.find(o => o.id === id);
            
            if (order) {
                const invoiceHTML = generateInvoiceHTML(order);
                
                // Buka di tab baru
                const printWindow = window.open('', '_blank');
                printWindow.document.write(invoiceHTML);
                printWindow.document.close();
                
                // Fokus dan tunggu sebentar sebelum print
                printWindow.focus();
                setTimeout(() => {
                    printWindow.print();
                    showNotification('Invoice berhasil dicetak!', 'success');
                }, 500);
            }
        }

        // Show delete modal
        function showDeleteModal(id) {
            currentOrderId = id;
            deleteModal.style.display = 'flex';
        }

        // Delete order
        function deleteOrder() {
            orders = orders.filter(o => o.id !== currentOrderId);
            filterOrders(); // Re-render with filters
            deleteModal.style.display = 'none';
            showNotification('Pesanan berhasil dihapus!', 'danger');
        }

        // Fungsi untuk ekspor ke CSV
        function exportToCSV(ordersToExport) {
            let csvContent = 'No. Pesanan,Pelanggan,Email,Tanggal,Items,Subtotal,Pengiriman,Total,Status,Metode Pembayaran,Catatan\n';
            
            ordersToExport.forEach(order => {
                const totalItems = order.items.reduce((sum, item) => sum + item.quantity, 0);
                const row = [
                    `"${order.orderNumber}"`,
                    `"${order.customer.name}"`,
                    `"${order.customer.email}"`,
                    `"${formatDate(order.date)}"`,
                    totalItems,
                    order.subtotal,
                    order.shipping,
                    order.total,
                    `"${getStatusText(order.status)}"`,
                    `"${getPaymentMethodText(order.paymentMethod)}"`,
                    `"${order.notes || ''}"`
                ];
                csvContent += row.join(',') + '\n';
            });
            
            return csvContent;
        }

        // Fungsi untuk ekspor ke JSON
        function exportToJSON(ordersToExport) {
            const exportData = ordersToExport.map(order => ({
                orderNumber: order.orderNumber,
                customer: order.customer,
                date: order.date,
                items: order.items,
                subtotal: order.subtotal,
                shipping: order.shipping,
                total: order.total,
                status: order.status,
                paymentMethod: order.paymentMethod,
                notes: order.notes
            }));
            
            return JSON.stringify(exportData, null, 2);
        }

        // Fungsi untuk ekspor ke Excel
        function exportToExcel(ordersToExport) {
            // Untuk Excel, kita buat HTML table yang bisa dibuka di Excel
            let htmlContent = `
                <html>
                <head>
                    <meta charset="UTF-8">
                    <title>Data Pesanan PT Megatek</title>
                    <style>
                        table { border-collapse: collapse; width: 100%; }
                        th { background-color: #004080; color: white; padding: 10px; text-align: left; }
                        td { padding: 8px; border: 1px solid #ddd; }
                    </style>
                </head>
                <body>
                    <h1>Data Pesanan PT Megatek Industrial Persada</h1>
                    <p>Dibuat pada: ${new Date().toLocaleString('id-ID')}</p>
                    <table>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Email</th>
                            <th>Tanggal</th>
                            <th>Items</th>
                            <th>Subtotal</th>
                            <th>Pengiriman</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Metode Pembayaran</th>
                            <th>Catatan</th>
                        </tr>
            `;
            
            ordersToExport.forEach(order => {
                const totalItems = order.items.reduce((sum, item) => sum + item.quantity, 0);
                htmlContent += `
                    <tr>
                        <td>${order.orderNumber}</td>
                        <td>${order.customer.name}</td>
                        <td>${order.customer.email}</td>
                        <td>${formatDate(order.date)}</td>
                        <td>${totalItems}</td>
                        <td>${formatRupiah(order.subtotal)}</td>
                        <td>${formatRupiah(order.shipping)}</td>
                        <td>${formatRupiah(order.total)}</td>
                        <td>${getStatusText(order.status)}</td>
                        <td>${getPaymentMethodText(order.paymentMethod)}</td>
                        <td>${order.notes || ''}</td>
                    </tr>
                `;
            });
            
            htmlContent += `
                    </table>
                </body>
                </html>
            `;
            
            return htmlContent;
        }

        // Fungsi untuk ekspor ke PDF (HTML untuk dicetak)
        function exportToPDF(ordersToExport) {
            let htmlContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Laporan Pesanan PT Megatek</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        h1 { color: #004080; text-align: center; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .logo { font-size: 24px; font-weight: bold; color: #004080; }
                        .subtitle { color: #666; font-size: 14px; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th { background-color: #004080; color: white; padding: 10px; text-align: left; }
                        td { padding: 8px; border-bottom: 1px solid #ddd; }
                        .total-row { font-weight: bold; background-color: #f5f5f5; }
                        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
                        .date { text-align: right; margin-bottom: 20px; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <div class="logo">PT Megatek Industrial Persada</div>
                        <div class="subtitle">Your Trusted Industrial Partner</div>
                    </div>
                    
                    <div class="date">
                        <strong>Tanggal Laporan:</strong> ${new Date().toLocaleDateString('id-ID')}
                    </div>
                    
                    <h1>Laporan Pesanan</h1>
                    
                    <table>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Tanggal</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
            `;
            
            ordersToExport.forEach(order => {
                const totalItems = order.items.reduce((sum, item) => sum + item.quantity, 0);
                htmlContent += `
                    <tr>
                        <td>${order.orderNumber}</td>
                        <td>${order.customer.name}</td>
                        <td>${formatDate(order.date)}</td>
                        <td>${totalItems} item${totalItems > 1 ? 's' : ''}</td>
                        <td>${formatRupiah(order.total)}</td>
                        <td>${getStatusText(order.status)}</td>
                    </tr>
                `;
            });
            
            const totalAmount = ordersToExport.reduce((sum, order) => sum + order.total, 0);
            htmlContent += `
                    </table>
                    
                    <div class="footer">
                        <p>Total ${ordersToExport.length} pesanan dengan total nilai: ${formatRupiah(totalAmount)}</p>
                        <p>Dibuat pada: ${new Date().toLocaleString('id-ID')}</p>
                        <p>PT Megatek Industrial Persada &copy; 2025</p>
                    </div>
                </body>
                </html>
            `;
            
            return htmlContent;
        }

        // Fungsi untuk membuka modal ekspor
        function openExportModal() {
            // Set tanggal default
            const today = new Date();
            const lastMonth = new Date();
            lastMonth.setMonth(today.getMonth() - 1);
            
            exportDateFrom.valueAsDate = lastMonth;
            exportDateTo.valueAsDate = today;
            
            // Update ringkasan
            updateExportSummary();
            
            exportModal.style.display = 'flex';
        }

        // Update ringkasan ekspor
        function updateExportSummary() {
            const ordersToExport = getFilteredOrdersForExport();
            const format = exportFormatInput.value;
            const formatNames = {
                csv: 'CSV',
                excel: 'Excel',
                pdf: 'PDF',
                json: 'JSON'
            };
            
            exportSummary.textContent = 
                `Akan mengekspor ${ordersToExport.length} pesanan dalam format ${formatNames[format]}. ` +
                `Total nilai: ${formatRupiah(ordersToExport.reduce((sum, order) => sum + order.total, 0))}`;
        }

        // Filter pesanan untuk ekspor
        function getFilteredOrdersForExport() {
            const dateFrom = exportDateFrom.value;
            const dateTo = exportDateTo.value;
            const status = exportStatus.value;
            
            let ordersToExport = orders;
            
            if (dateFrom) {
                const fromDate = new Date(dateFrom);
                ordersToExport = ordersToExport.filter(order => {
                    const orderDate = new Date(order.date.split(' ')[0]);
                    return orderDate >= fromDate;
                });
            }
            
            if (dateTo) {
                const toDate = new Date(dateTo);
                ordersToExport = ordersToExport.filter(order => {
                    const orderDate = new Date(order.date.split(' ')[0]);
                    return orderDate <= toDate;
                });
            }
            
            if (status) {
                ordersToExport = ordersToExport.filter(order => order.status === status);
            }
            
            return ordersToExport;
        }

        // Fungsi untuk melakukan ekspor
        function performExport() {
            const format = exportFormatInput.value;
            const ordersToExport = getFilteredOrdersForExport();
            
            if (ordersToExport.length === 0) {
                showNotification('Tidak ada data yang ditemukan berdasarkan filter yang dipilih.', 'warning');
                return;
            }
            
            let fileContent, fileName, mimeType;
            
            switch(format) {
                case 'csv':
                    fileContent = exportToCSV(ordersToExport);
                    fileName = `pesanan_${new Date().toISOString().split('T')[0]}.csv`;
                    mimeType = 'text/csv;charset=utf-8;';
                    break;
                    
                case 'excel':
                    fileContent = exportToExcel(ordersToExport);
                    fileName = `pesanan_${new Date().toISOString().split('T')[0]}.xls`;
                    mimeType = 'application/vnd.ms-excel;charset=utf-8;';
                    break;
                    
                case 'pdf':
                    fileContent = exportToPDF(ordersToExport);
                    fileName = `laporan_pesanan_${new Date().toISOString().split('T')[0]}.html`;
                    mimeType = 'text/html;charset=utf-8;';
                    
                    // Buka di tab baru untuk dicetak sebagai PDF
                    const printWindow = window.open('', '_blank');
                    printWindow.document.write(fileContent);
                    printWindow.document.close();
                    printWindow.focus();
                    
                    showNotification('Laporan PDF dibuka di tab baru. Silakan gunakan fitur print browser untuk menyimpan sebagai PDF.', 'info');
                    exportModal.style.display = 'none';
                    return;
                    
                case 'json':
                    fileContent = exportToJSON(ordersToExport);
                    fileName = `pesanan_${new Date().toISOString().split('T')[0]}.json`;
                    mimeType = 'application/json;charset=utf-8;';
                    break;
            }
            
            downloadFile(fileContent, fileName, mimeType);
            showNotification(`Data berhasil diekspor (${ordersToExport.length} pesanan)!`, 'success');
            exportModal.style.display = 'none';
        }

        // Close modals
        function closeDetailModal() {
            orderDetailModal.style.display = 'none';
        }

        function closeEditModal() {
            editOrderModal.style.display = 'none';
        }

        function closeDeleteModal() {
            deleteModal.style.display = 'none';
            currentOrderId = null;
        }

        function closeExportModal() {
            exportModal.style.display = 'none';
        }

        // Show notification
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
            `;
            
            if (type === 'success') {
                notification.style.backgroundColor = 'var(--success)';
            } else if (type === 'danger') {
                notification.style.backgroundColor = 'var(--danger)';
            } else if (type === 'info') {
                notification.style.backgroundColor = 'var(--info)';
            } else if (type === 'warning') {
                notification.style.backgroundColor = 'var(--warning)';
            } else {
                notification.style.backgroundColor = 'var(--primary)';
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
        exportOrdersBtn.addEventListener('click', openExportModal);
        closeDetailModalBtn.addEventListener('click', closeDetailModal);
        closeEditModalBtn.addEventListener('click', closeEditModal);
        closeDeleteModalBtn.addEventListener('click', closeDeleteModal);
        closeExportModalBtn.addEventListener('click', closeExportModal);
        cancelEditBtn.addEventListener('click', closeEditModal);
        cancelDeleteBtn.addEventListener('click', closeDeleteModal);
        cancelExportBtn.addEventListener('click', closeExportModal);
        printInvoiceBtn.addEventListener('click', () => printInvoice(currentOrderId));
        updateStatusBtn.addEventListener('click', updateOrderStatus);
        saveEditBtn.addEventListener('click', saveEditedOrder);
        confirmDeleteBtn.addEventListener('click', deleteOrder);
        confirmExportBtn.addEventListener('click', performExport);
        applyFilterBtn.addEventListener('click', filterOrders);
        resetFilterBtn.addEventListener('click', resetFilter);

        // Event listeners untuk opsi ekspor
        exportOptions.forEach(option => {
            option.addEventListener('click', () => {
                exportOptions.forEach(opt => opt.classList.remove('selected'));
                option.classList.add('selected');
                exportFormatInput.value = option.dataset.format;
                updateExportSummary();
            });
        });

        // Event listeners untuk update ringkasan ekspor
        exportDateFrom.addEventListener('change', updateExportSummary);
        exportDateTo.addEventListener('change', updateExportSummary);
        exportStatus.addEventListener('change', updateExportSummary);

        // Real-time filtering
        searchOrderInput.addEventListener('input', filterOrders);
        filterStatusSelect.addEventListener('change', filterOrders);
        filterDateFrom.addEventListener('change', filterOrders);
        filterDateTo.addEventListener('change', filterOrders);

        // Close modals when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === orderDetailModal) closeDetailModal();
            if (e.target === editOrderModal) closeEditModal();
            if (e.target === deleteModal) closeDeleteModal();
            if (e.target === exportModal) closeExportModal();
        });

        // Set default dates for filter
        const today = new Date();
        const lastWeek = new Date();
        lastWeek.setDate(today.getDate() - 7);
        
        filterDateFrom.valueAsDate = lastWeek;
        filterDateTo.valueAsDate = today;

        // Render initial data
        renderOrders(filteredOrders);
        
        // Update ringkasan ekspor saat pertama kali
        setTimeout(updateExportSummary, 100);
    </script>
</body>
</html>