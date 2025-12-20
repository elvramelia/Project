<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - PT Megatek Industrial Persada</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
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
            --purple: #7b1fa2;
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
            background-color: var(--primary-light);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            margin-bottom: 30px;
        }

        .filter-title {
            font-size: 18px;
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-label {
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--secondary);
            font-size: 15px;
        }

        .filter-input, .filter-select {
            padding: 12px 15px;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: border 0.3s;
        }

        .filter-input:focus, .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 64, 128, 0.1);
        }

        .filter-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 10px;
        }

        /* Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
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
            color: rgba(0, 64, 128, 0.1);
            margin-top: 10px;
        }

        /* Charts Section */
        .charts-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        @media (max-width: 1200px) {
            .charts-section {
                grid-template-columns: 1fr;
            }
        }

        .chart-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-title {
            font-size: 18px;
            color: var(--primary);
            font-weight: 600;
        }

        .chart-legend {
            display: flex;
            gap: 15px;
            font-size: 14px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 3px;
        }

        .chart-wrapper {
            position: relative;
            height: 300px;
            width: 100%;
        }

        /* Report Tables */
        .report-section {
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
            box-shadow: 0 4px 8px rgba(0, 64, 128, 0.2);
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

        .status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
        }

        .status.completed {
            background-color: rgba(46, 125, 50, 0.15);
            color: var(--success);
        }

        .status.pending {
            background-color: rgba(245, 124, 0, 0.15);
            color: var(--warning);
        }

        .status.cancelled {
            background-color: rgba(211, 47, 47, 0.15);
            color: var(--danger);
        }

        .trend-up {
            color: var(--success);
            font-weight: 600;
        }

        .trend-down {
            color: var(--danger);
            font-weight: 600;
        }

        .trend-neutral {
            color: var(--gray);
            font-weight: 600;
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
            
            .charts-section {
                grid-template-columns: 1fr;
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
            
            .filter-row {
                grid-template-columns: 1fr;
            }
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-actions {
                flex-direction: column;
                width: 100%;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
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

        /* Loading Spinner */
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top: 4px solid var(--primary);
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
            max-width: 800px;
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

        .modal-footer {
            padding: 20px 25px;
            background-color: #f9f9f9;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            border-top: 1px solid var(--light-gray);
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
                <a href="pesanan.php" class="nav-link">
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
                <a href="laporan.php" class="nav-link active">
                    <i class="fas fa-chart-bar"></i>
                    <span>Laporan</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="uploadbaner.php" class="nav-link">
                    <i class="fa-solid fa-download" style="color: #ffffff;"></i>
                    <span>Upload Banner</span>
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
            <h1><i class="fas fa-chart-bar"></i> Laporan & Analitik</h1>
            <div class="user-info">
                <span>Admin Megatek</span>
                <div class="avatar">AM</div>
            </div>
        </header>

        <!-- Filter Section -->
        <div class="filter-section fade-in">
            <div class="filter-title">
                <i class="fas fa-filter"></i> Filter Laporan
            </div>
            
            <div class="filter-row">
                <div class="filter-group">
                    <label class="filter-label">Periode Laporan</label>
                    <select class="filter-select" id="reportPeriod">
                        <option value="monthly">Bulan Ini</option>
                        <option value="last-month">Bulan Lalu</option>
                        <option value="quarterly">Kuartal Ini</option>
                        <option value="yearly">Tahun Ini</option>
                        <option value="custom">Periode Kustom</option>
                    </select>
                </div>
                
                <div class="filter-group" id="customDateRange" style="display: none;">
                    <label class="filter-label">Rentang Tanggal</label>
                    <input type="text" class="filter-input" id="dateRange" placeholder="Pilih rentang tanggal">
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">Tipe Laporan</label>
                    <select class="filter-select" id="reportType">
                        <option value="sales">Penjualan</option>
                        <option value="orders">Pesanan</option>
                        <option value="customers">Pelanggan</option>
                        <option value="products">Produk</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">Kategori Produk</label>
                    <select class="filter-select" id="productCategory">
                        <option value="all">Semua Kategori</option>
                        <option value="Sporeport">Sporeport</option>
                        <option value="FBR Burner">FBR Burner</option>
                        <option value="Boiler">Boiler</option>
                        <option value="Valve & Instrumentation">Valve & Instrumentation</option>
                    </select>
                </div>
            </div>
            
            <div class="filter-actions">
                <button class="btn btn-outline" id="resetFilterBtn">
                    <i class="fas fa-redo"></i> Reset Filter
                </button>
                <button class="btn btn-primary" id="applyFilterBtn">
                    <i class="fas fa-chart-line"></i> Tampilkan Laporan
                </button>
                <button class="btn btn-success" id="exportReportBtn">
                    <i class="fas fa-file-export"></i> Export Laporan
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="card fade-in">
                <div class="card-title">Total Pendapatan</div>
                <div class="card-value">Rp 1,2M</div>
                <div class="card-change"><span class="trend-up">+15%</span> dari bulan lalu</div>
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="card fade-in" style="animation-delay: 0.1s;">
                <div class="card-title">Total Pesanan</div>
                <div class="card-value">127</div>
                <div class="card-change"><span class="trend-up">+8%</span> dari bulan lalu</div>
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="card fade-in" style="animation-delay: 0.2s;">
                <div class="card-title">Pelanggan Baru</div>
                <div class="card-value">18</div>
                <div class="card-change"><span class="trend-up">+5%</span> dari bulan lalu</div>
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="card fade-in" style="animation-delay: 0.3s;">
                <div class="card-title">Produk Terjual</div>
                <div class="card-value">342</div>
                <div class="card-change"><span class="trend-up">+12%</span> dari bulan lalu</div>
                <i class="fas fa-box-open"></i>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <!-- Sales Chart -->
            <div class="chart-container fade-in">
                <div class="chart-header">
                    <h3 class="chart-title">Grafik Pendapatan</h3>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #3b82f6;"></div>
                            <span>Pendapatan</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #10b981;"></div>
                            <span>Target</span>
                        </div>
                    </div>
                </div>
                <div class="chart-wrapper">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- Product Category Chart -->
            <div class="chart-container fade-in" style="animation-delay: 0.2s;">
                <div class="chart-header">
                    <h3 class="chart-title">Penjualan per Kategori Produk</h3>
                    <div class="chart-legend" id="categoryLegend"></div>
                </div>
                <div class="chart-wrapper">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Report Table -->
        <div class="report-section fade-in" style="animation-delay: 0.4s;">
            <div class="section-header">
                <h2 class="section-title">Laporan Bulanan</h2>
                <div>
                    <button class="btn btn-info" id="printReportBtn">
                        <i class="fas fa-print"></i> Cetak Laporan
                    </button>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th>Pendapatan</th>
                            <th>Pesanan</th>
                            <th>Pelanggan Baru</th>
                            <th>Produk Terjual</th>
                            <th>Rata-rata Nilai Pesanan</th>
                            <th>Trend</th>
                        </tr>
                    </thead>
                    <tbody id="monthlyReportTable">
                        <!-- Data laporan bulanan akan dimuat di sini melalui JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Products Report -->
        <div class="report-section fade-in" style="animation-delay: 0.5s;">
            <div class="section-header">
                <h2 class="section-title">Produk Terlaris</h2>
                <select class="filter-select" id="topProductsFilter" style="width: 200px;">
                    <option value="5">Top 5 Produk</option>
                    <option value="10">Top 10 Produk</option>
                    <option value="20">Top 20 Produk</option>
                </select>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Peringkat</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Jumlah Terjual</th>
                            <th>Pendapatan</th>
                            <th>% dari Total</th>
                            <th>Trend</th>
                        </tr>
                    </thead>
                    <tbody id="topProductsTable">
                        <!-- Data produk terlaris akan dimuat di sini melalui JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <p>&copy; 2025 PT Megatek Industrial Persada - Your Trusted Industrial Partner</p>
            <p style="margin-top: 10px; font-size: 12px;">Laporan terakhir diupdate: <span id="lastUpdated"><?php echo date('d F Y H:i'); ?></span></p>
        </footer>
    </main>

    <!-- Loading Modal -->
    <div class="modal" id="loadingModal">
        <div class="modal-content" style="max-width: 300px; text-align: center;">
            <div class="modal-body">
                <div class="spinner"></div>
                <p style="margin-top: 15px; font-weight: 600; color: var(--primary);">Memproses laporan...</p>
            </div>
        </div>
    </div>

    <!-- Export Modal -->
    <div class="modal" id="exportModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Export Laporan</h3>
                <button class="close-modal" id="closeExportModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="filter-label">Format Export</label>
                    <select class="filter-select" id="exportFormat">
                        <option value="excel">Excel (.xlsx)</option>
                        <option value="pdf">PDF (.pdf)</option>
                        <option value="csv">CSV (.csv)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="filter-label">Data yang akan di-export</label>
                    <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 10px;">
                        <label style="display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox" id="exportSales" checked>
                            <span>Data Penjualan</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox" id="exportOrders" checked>
                            <span>Data Pesanan</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox" id="exportProducts" checked>
                            <span>Data Produk Terlaris</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox" id="exportCharts" checked>
                            <span>Grafik dan Chart</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="filter-label">Periode Data</label>
                    <select class="filter-select" id="exportPeriod">
                        <option value="current">Data saat ini (sesuai filter)</option>
                        <option value="last-month">Bulan Lalu</option>
                        <option value="last-quarter">Kuartal Lalu</option>
                        <option value="last-year">Tahun Lalu</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelExportBtn">Batal</button>
                <button class="btn btn-success" id="confirmExportBtn">
                    <i class="fas fa-download"></i> Download Laporan
                </button>
            </div>
        </div>
    </div>

    <script>
        // Data laporan contoh
        const monthlyData = [
            { month: 'Jan 2025', revenue: 950000000, orders: 98, newCustomers: 12, productsSold: 265, avgOrderValue: 9693878 },
            { month: 'Feb 2025', revenue: 1050000000, orders: 112, newCustomers: 15, productsSold: 298, avgOrderValue: 9375000 },
            { month: 'Mar 2025', revenue: 980000000, orders: 105, newCustomers: 14, productsSold: 287, avgOrderValue: 9333333 },
            { month: 'Apr 2025', revenue: 1100000000, orders: 118, newCustomers: 16, productsSold: 312, avgOrderValue: 9322034 },
            { month: 'Mei 2025', revenue: 1150000000, orders: 124, newCustomers: 18, productsSold: 328, avgOrderValue: 9274194 },
            { month: 'Jun 2025', revenue: 1200000000, orders: 127, newCustomers: 18, productsSold: 342, avgOrderValue: 9448819 }
        ];

        const topProductsData = [
            { rank: 1, name: 'Sporeport Pro X200', category: 'Sporeport', sold: 45, revenue: 562500000, percentage: 18.5 },
            { rank: 2, name: 'FBR Burner Eco Series', category: 'FBR Burner', sold: 38, revenue: 323000000, percentage: 13.2 },
            { rank: 3, name: 'Control Valve AV100', category: 'Valve & Instrumentation', sold: 67, revenue: 234500000, percentage: 10.8 },
            { rank: 4, name: 'Boiler SteamMaster 500', category: 'Boiler', sold: 8, revenue: 1480000000, percentage: 9.5 },
            { rank: 5, name: 'Sporeport Mini S50', category: 'Sporeport', sold: 32, revenue: 240000000, percentage: 8.2 },
            { rank: 6, name: 'FBR Burner Heavy Duty', category: 'FBR Burner', sold: 24, revenue: 288000000, percentage: 7.9 },
            { rank: 7, name: 'Control Valve BV200', category: 'Valve & Instrumentation', sold: 41, revenue: 143500000, percentage: 6.5 },
            { rank: 8, name: 'Boiler Compact 300', category: 'Boiler', sold: 6, revenue: 900000000, percentage: 5.8 },
            { rank: 9, name: 'Sporeport Basic B100', category: 'Sporeport', sold: 28, revenue: 196000000, percentage: 5.2 },
            { rank: 10, name: 'FBR Burner Standard', category: 'FBR Burner', sold: 19, revenue: 152000000, percentage: 4.6 }
        ];

        const categoryData = [
            { category: 'Sporeport', revenue: 998500000, percentage: 32.5, color: '#3b82f6' },
            { category: 'FBR Burner', revenue: 763000000, percentage: 24.8, color: '#10b981' },
            { category: 'Boiler', revenue: 2380000000, percentage: 22.7, color: '#f59e0b' },
            { category: 'Valve & Instrumentation', revenue: 378000000, percentage: 12.3, color: '#8b5cf6' },
            { category: 'Lainnya', revenue: 245000000, percentage: 7.7, color: '#ef4444' }
        ];

        // DOM Elements
        const reportPeriod = document.getElementById('reportPeriod');
        const customDateRange = document.getElementById('customDateRange');
        const dateRange = document.getElementById('dateRange');
        const reportType = document.getElementById('reportType');
        const productCategory = document.getElementById('productCategory');
        const resetFilterBtn = document.getElementById('resetFilterBtn');
        const applyFilterBtn = document.getElementById('applyFilterBtn');
        const exportReportBtn = document.getElementById('exportReportBtn');
        const printReportBtn = document.getElementById('printReportBtn');
        const topProductsFilter = document.getElementById('topProductsFilter');
        const monthlyReportTable = document.getElementById('monthlyReportTable');
        const topProductsTable = document.getElementById('topProductsTable');
        const loadingModal = document.getElementById('loadingModal');
        const exportModal = document.getElementById('exportModal');
        const closeExportModal = document.getElementById('closeExportModal');
        const cancelExportBtn = document.getElementById('cancelExportBtn');
        const confirmExportBtn = document.getElementById('confirmExportBtn');
        const lastUpdated = document.getElementById('lastUpdated');

        // Chart instances
        let salesChart = null;
        let categoryChart = null;

        // Format angka menjadi Rupiah
        function formatRupiah(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount);
        }

        // Format angka dengan separator ribuan
        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        // Hitung trend (naik/turun)
        function calculateTrend(current, previous) {
            if (previous === 0) return { value: 100, class: 'trend-up', icon: 'fa-arrow-up' };
            
            const change = ((current - previous) / previous) * 100;
            const rounded = Math.round(change * 10) / 10;
            
            if (rounded > 0) {
                return { value: rounded, class: 'trend-up', icon: 'fa-arrow-up' };
            } else if (rounded < 0) {
                return { value: Math.abs(rounded), class: 'trend-down', icon: 'fa-arrow-down' };
            } else {
                return { value: 0, class: 'trend-neutral', icon: 'fa-minus' };
            }
        }

        // Render tabel laporan bulanan
        function renderMonthlyReport() {
            monthlyReportTable.innerHTML = '';
            
            monthlyData.forEach((data, index) => {
                const previousRevenue = index > 0 ? monthlyData[index - 1].revenue : 0;
                const revenueTrend = calculateTrend(data.revenue, previousRevenue);
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><strong>${data.month}</strong></td>
                    <td><strong>${formatRupiah(data.revenue)}</strong></td>
                    <td>${formatNumber(data.orders)} pesanan</td>
                    <td>${formatNumber(data.newCustomers)} pelanggan</td>
                    <td>${formatNumber(data.productsSold)} unit</td>
                    <td>${formatRupiah(data.avgOrderValue)}</td>
                    <td>
                        <span class="${revenueTrend.class}">
                            <i class="fas ${revenueTrend.icon}"></i> ${revenueTrend.value}%
                        </span>
                    </td>
                `;
                monthlyReportTable.appendChild(row);
            });
        }

        // Render tabel produk terlaris
        function renderTopProducts() {
            topProductsTable.innerHTML = '';
            
            const limit = parseInt(topProductsFilter.value);
            const filteredData = topProductsData.slice(0, limit);
            
            filteredData.forEach(product => {
                const row = document.createElement('tr');
                
                // Tentukan kelas trend berdasarkan peringkat
                let trendClass = 'trend-neutral';
                let trendIcon = 'fa-minus';
                
                if (product.rank === 1) {
                    trendClass = 'trend-up';
                    trendIcon = 'fa-trophy';
                } else if (product.rank <= 3) {
                    trendClass = 'trend-up';
                    trendIcon = 'fa-arrow-up';
                } else if (product.rank >= 8) {
                    trendClass = 'trend-down';
                    trendIcon = 'fa-arrow-down';
                }
                
                row.innerHTML = `
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 30px; height: 30px; border-radius: 50%; background-color: ${product.rank <= 3 ? 'var(--primary)' : '#f0f0f0'}; color: ${product.rank <= 3 ? 'white' : 'var(--secondary)'}; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                ${product.rank}
                            </div>
                            ${product.rank === 1 ? '<span style="color: var(--accent);"><i class="fas fa-crown"></i></span>' : ''}
                        </div>
                    </td>
                    <td><strong>${product.name}</strong></td>
                    <td><span class="status completed">${product.category}</span></td>
                    <td>${formatNumber(product.sold)} unit</td>
                    <td><strong>${formatRupiah(product.revenue)}</strong></td>
                    <td>${product.percentage.toFixed(1)}%</td>
                    <td>
                        <span class="${trendClass}">
                            <i class="fas ${trendIcon}"></i>
                        </span>
                    </td>
                `;
                topProductsTable.appendChild(row);
            });
        }

        // Inisialisasi chart penjualan
        function initSalesChart() {
            const ctx = document.getElementById('salesChart').getContext('2d');
            
            if (salesChart) {
                salesChart.destroy();
            }
            
            const months = monthlyData.map(data => data.month.split(' ')[0]);
            const revenues = monthlyData.map(data => data.revenue / 1000000); // Convert to millions
            const targets = monthlyData.map(data => (data.revenue * 0.9) / 1000000); // 90% of revenue as target
            
            salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [
                        {
                            label: 'Pendapatan (dalam juta)',
                            data: revenues,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Target',
                            data: targets,
                            borderColor: '#10b981',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            fill: false,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += 'Rp ' + formatNumber(context.parsed.y) + ' juta';
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + formatNumber(value) + ' jt';
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        }
                    }
                }
            });
        }

        // Inisialisasi chart kategori
        function initCategoryChart() {
            const ctx = document.getElementById('categoryChart').getContext('2d');
            const legendContainer = document.getElementById('categoryLegend');
            
            if (categoryChart) {
                categoryChart.destroy();
            }
            
            // Update legend
            legendContainer.innerHTML = '';
            categoryData.forEach(item => {
                const legendItem = document.createElement('div');
                legendItem.className = 'legend-item';
                legendItem.innerHTML = `
                    <div class="legend-color" style="background-color: ${item.color};"></div>
                    <span>${item.category} (${item.percentage.toFixed(1)}%)</span>
                `;
                legendContainer.appendChild(legendItem);
            });
            
            categoryChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: categoryData.map(item => item.category),
                    datasets: [{
                        data: categoryData.map(item => item.revenue / 1000000), // Convert to millions
                        backgroundColor: categoryData.map(item => item.color),
                        borderWidth: 2,
                        borderColor: 'white'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const percentage = categoryData[context.dataIndex].percentage;
                                    return `${label}: Rp ${formatNumber(value)} juta (${percentage.toFixed(1)}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Tampilkan loading
        function showLoading() {
            loadingModal.style.display = 'flex';
        }

        // Sembunyikan loading
        function hideLoading() {
            loadingModal.style.display = 'none';
        }

        // Terapkan filter
        function applyFilters() {
            showLoading();
            
            // Simulasi proses filter (dalam aplikasi nyata, ini akan request ke server)
            setTimeout(() => {
                // Update data berdasarkan filter
                updateDataBasedOnFilters();
                hideLoading();
                showNotification('Filter berhasil diterapkan!', 'success');
            }, 1000);
        }

        // Update data berdasarkan filter
        function updateDataBasedOnFilters() {
            // Dalam implementasi nyata, ini akan mengambil data baru dari server
            // Untuk demo, kita hanya akan memperbarui teks terakhir diupdate
            const now = new Date();
            lastUpdated.textContent = now.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Reset filter
        function resetFilters() {
            reportPeriod.value = 'monthly';
            customDateRange.style.display = 'none';
            reportType.value = 'sales';
            productCategory.value = 'all';
            
            if (dateRange._flatpickr) {
                dateRange._flatpickr.clear();
            }
            
            showNotification('Filter telah direset', 'info');
        }

        // Tampilkan modal export
        function showExportModal() {
            exportModal.style.display = 'flex';
        }

        // Tutup modal export
        function closeExportModalFunc() {
            exportModal.style.display = 'none';
        }

        // Proses export laporan
        function exportReport() {
            const format = document.getElementById('exportFormat').value;
            const period = document.getElementById('exportPeriod').value;
            
            showLoading();
            
            // Simulasi proses export
            setTimeout(() => {
                hideLoading();
                closeExportModalFunc();
                
                let message = `Laporan berhasil diexport dalam format ${format.toUpperCase()}!`;
                if (format === 'excel') {
                    message += ' File akan otomatis didownload.';
                }
                
                showNotification(message, 'success');
                
                // Dalam aplikasi nyata, ini akan memicu download file
                // Untuk demo, kita hanya menampilkan notifikasi
            }, 1500);
        }

        // Cetak laporan
        function printReport() {
            showLoading();
            
            setTimeout(() => {
                hideLoading();
                window.print();
                showNotification('Mempersiapkan laporan untuk dicetak...', 'info');
            }, 800);
        }

        // Tampilkan notifikasi
        function showNotification(message, type) {
            // Hapus notifikasi sebelumnya
            const existingNotification = document.querySelector('.notification');
            if (existingNotification) {
                existingNotification.remove();
            }
            
            // Buat elemen notifikasi
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
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
            } else {
                notification.style.backgroundColor = 'var(--primary)';
            }
            
            notification.innerHTML = `
                <span>${message}</span>
                <button class="close-notification" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer; margin-left: 15px;">&times;</button>
            `;
            
            // Tambahkan ke body
            document.body.appendChild(notification);
            
            // Tombol tutup notifikasi
            const closeBtn = notification.querySelector('.close-notification');
            closeBtn.addEventListener('click', () => {
                notification.remove();
            });
            
            // Hapus otomatis setelah 3 detik
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 3000);
        }

        // Inisialisasi datepicker
        function initDatePicker() {
            flatpickr(dateRange, {
                mode: "range",
                dateFormat: "d-m-Y",
                locale: "id",
                onChange: function(selectedDates, dateStr) {
                    if (dateStr) {
                        console.log("Rentang tanggal dipilih:", dateStr);
                    }
                }
            });
        }

        // Event Listeners
        reportPeriod.addEventListener('change', function() {
            if (this.value === 'custom') {
                customDateRange.style.display = 'block';
            } else {
                customDateRange.style.display = 'none';
            }
        });

        resetFilterBtn.addEventListener('click', resetFilters);
        applyFilterBtn.addEventListener('click', applyFilters);
        exportReportBtn.addEventListener('click', showExportModal);
        printReportBtn.addEventListener('click', printReport);
        topProductsFilter.addEventListener('change', renderTopProducts);

        closeExportModal.addEventListener('click', closeExportModalFunc);
        cancelExportBtn.addEventListener('click', closeExportModalFunc);
        confirmExportBtn.addEventListener('click', exportReport);

        // Tutup modal jika klik di luar konten modal
        window.addEventListener('click', (e) => {
            if (e.target === loadingModal) {
                loadingModal.style.display = 'none';
            }
            if (e.target === exportModal) {
                closeExportModalFunc();
            }
        });

        // Inisialisasi
        document.addEventListener('DOMContentLoaded', function() {
            // Set waktu terakhir update
            const now = new Date();
            lastUpdated.textContent = now.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            // Render data awal
            renderMonthlyReport();
            renderTopProducts();
            initSalesChart();
            initCategoryChart();
            initDatePicker();
            
            // Tambahkan event listener untuk saat halaman dicetak
            window.addEventListener('beforeprint', function() {
                document.querySelector('.sidebar').style.display = 'none';
                document.querySelector('.main-content').style.marginLeft = '0';
            });
            
            window.addEventListener('afterprint', function() {
                document.querySelector('.sidebar').style.display = 'block';
                document.querySelector('.main-content').style.marginLeft = '260px';
            });
        });
    </script>
</body>
</html>