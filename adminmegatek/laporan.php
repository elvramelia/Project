<?php
// --- BAGIAN BACKEND PHP ---
// Pastikan path ini benar sesuai struktur folder Anda
require_once '../config/database.php'; 

// Inisialisasi variabel default
$total_pendapatan = 0;
$total_pesanan = 0;
$pelanggan_baru = 0;
$total_terjual = 0;
$monthlyData = [];
$topProductsData = [];
$categoryData = [];

// 1. DATA KARTU STATISTIK & GRAFIK PENDAPATAN (6 Bulan Terakhir)
// PERBAIKAN: Menggunakan 'created_at' bukannya 'order_date'
$query_monthly = "
    SELECT 
        DATE_FORMAT(o.created_at, '%b %Y') as month_label,
        SUM(oi.subtotal) as revenue,
        COUNT(DISTINCT o.id) as total_orders,
        SUM(oi.quantity) as products_sold
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY YEAR(o.created_at), MONTH(o.created_at)
    ORDER BY o.created_at ASC
";

$result_monthly = mysqli_query($conn, $query_monthly);

// Cek error query jika ada
if (!$result_monthly) {
    die("Query Error (Monthly): " . mysqli_error($conn));
}

if ($result_monthly) {
    while ($row = mysqli_fetch_assoc($result_monthly)) {
        $total_pendapatan += $row['revenue'];
        $total_pesanan += $row['total_orders'];
        $total_terjual += $row['products_sold'];

        $monthlyData[] = [
            'month' => $row['month_label'],
            'revenue' => (float)$row['revenue'],
            'orders' => (int)$row['total_orders'],
            'newCustomers' => rand(5, 15), // Dummy data
            'productsSold' => (int)$row['products_sold'],
            'avgOrderValue' => $row['revenue'] / ($row['total_orders'] ?: 1)
        ];
    }
}

// 2. DATA PELANGGAN BARU
// PERBAIKAN: Menggunakan tabel 'users' bukannya 'customers'
$query_cust = "SELECT COUNT(*) as total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
$result_cust = mysqli_query($conn, $query_cust);
if ($result_cust) {
    $row_cust = mysqli_fetch_assoc($result_cust);
    $pelanggan_baru = $row_cust['total'];
} else {
    // Fallback jika terjadi error
    $pelanggan_baru = 0; 
}


// 3. DATA PRODUK TERLARIS (Top 10)
$query_top = "
    SELECT 
        oi.product_name,
        SUM(oi.quantity) as sold,
        SUM(oi.subtotal) as revenue,
        p.category
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    GROUP BY oi.product_id, oi.product_name
    ORDER BY sold DESC
    LIMIT 10
";

$result_top = mysqli_query($conn, $query_top);
$rank = 1;

// Hitung total revenue global untuk persentase
$global_rev_query = mysqli_query($conn, "SELECT SUM(subtotal) as total FROM order_items");
$global_row = mysqli_fetch_assoc($global_rev_query);
$global_revenue = $global_row['total'] ?? 1;

if ($result_top) {
    while ($row = mysqli_fetch_assoc($result_top)) {
        $percentage = ($row['revenue'] / $global_revenue) * 100;
        $topProductsData[] = [
            'rank' => $rank++,
            'name' => $row['product_name'],
            'category' => $row['category'] ?? 'Umum',
            'sold' => (int)$row['sold'],
            'revenue' => (float)$row['revenue'],
            'percentage' => (float)$percentage
        ];
    }
}

// 4. DATA KATEGORI (Chart Donat)
$query_cat = "
    SELECT 
        p.category,
        SUM(oi.subtotal) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY p.category
";
$result_cat = mysqli_query($conn, $query_cat);
$colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#64748b'];
$i = 0;

if ($result_cat) {
    while ($row = mysqli_fetch_assoc($result_cat)) {
        $cat_percentage = ($row['revenue'] / $global_revenue) * 100;
        $categoryData[] = [
            'category' => $row['category'] ?? 'Lainnya',
            'revenue' => (float)$row['revenue'],
            'percentage' => (float)$cat_percentage,
            'color' => $colors[$i % count($colors)]
        ];
        $i++;
    }
}
?>

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

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f9f9f9; color: var(--secondary); display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar { width: 260px; background-color: var(--primary); color: white; padding: 20px 0; position: fixed; height: 100vh; overflow-y: auto; transition: all 0.3s; box-shadow: var(--box-shadow); z-index: 100; }
        .logo { padding: 0 20px 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); margin-bottom: 20px; }
        .logo h1 { font-size: 22px; font-weight: 700; color: white; }
        .logo h2 { font-size: 14px; font-weight: 400; color: rgba(255, 255, 255, 0.8); margin-top: 5px; }
        .nav-menu { list-style: none; padding: 0 15px; }
        .nav-item { margin-bottom: 5px; }
        .nav-link { display: flex; align-items: center; padding: 12px 15px; color: rgba(255, 255, 255, 0.9); text-decoration: none; border-radius: var(--border-radius); transition: all 0.3s; }
        .nav-link:hover, .nav-link.active { background-color: rgba(255, 255, 255, 0.1); color: white; }
        .nav-link i { margin-right: 12px; font-size: 18px; width: 24px; text-align: center; }

        /* Main Content */
        .main-content { flex: 1; margin-left: 260px; padding: 20px; transition: all 0.3s; }
        .header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 20px; border-bottom: 1px solid var(--light-gray); margin-bottom: 30px; }
        .header h1 { color: var(--primary); font-size: 28px; display: flex; align-items: center; gap: 10px; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .user-info span { font-weight: 600; color: var(--secondary); }
        .avatar { width: 40px; height: 40px; border-radius: 50%; background-color: var(--primary-light); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; }

        /* Filter Section */
        .filter-section { background: white; border-radius: var(--border-radius); box-shadow: var(--box-shadow); padding: 25px; margin-bottom: 30px; }
        .filter-title { font-size: 18px; color: var(--primary); font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .filter-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .filter-group { display: flex; flex-direction: column; }
        .filter-label { margin-bottom: 8px; font-weight: 600; color: var(--secondary); font-size: 15px; }
        .filter-input, .filter-select { padding: 12px 15px; border: 1px solid var(--light-gray); border-radius: var(--border-radius); font-size: 16px; transition: border 0.3s; }
        .filter-input:focus, .filter-select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(0, 64, 128, 0.1); }
        .filter-actions { display: flex; justify-content: flex-end; gap: 15px; margin-top: 10px; }

        /* Stats Cards */
        .stats-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: white; border-radius: var(--border-radius); padding: 20px; box-shadow: var(--box-shadow); border-left: 5px solid var(--primary); }
        .card-title { font-size: 16px; color: var(--gray); margin-bottom: 10px; }
        .card-value { font-size: 28px; font-weight: 700; color: var(--primary); margin-bottom: 5px; }
        .card-change { font-size: 14px; color: var(--success); }
        .card i { float: right; font-size: 40px; color: rgba(0, 64, 128, 0.1); margin-top: 10px; }

        /* Charts Section */
        .charts-section { display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 30px; margin-bottom: 30px; }
        @media (max-width: 1200px) { .charts-section { grid-template-columns: 1fr; } }
        .chart-container { background: white; border-radius: var(--border-radius); box-shadow: var(--box-shadow); padding: 25px; }
        .chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .chart-title { font-size: 18px; color: var(--primary); font-weight: 600; }
        .chart-legend { display: flex; gap: 15px; font-size: 14px; }
        .legend-item { display: flex; align-items: center; gap: 5px; }
        .legend-color { width: 12px; height: 12px; border-radius: 3px; }
        .chart-wrapper { position: relative; height: 300px; width: 100%; }

        /* Report Tables */
        .report-section { background: white; border-radius: var(--border-radius); box-shadow: var(--box-shadow); padding: 25px; margin-bottom: 30px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .section-title { font-size: 22px; color: var(--primary); font-weight: 600; }
        .btn { padding: 10px 20px; border: none; border-radius: var(--border-radius); cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s; font-size: 15px; }
        .btn-primary { background-color: var(--primary); color: white; }
        .btn-primary:hover { background-color: var(--primary-light); transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0, 64, 128, 0.2); }
        .btn-success { background-color: var(--success); color: white; }
        .btn-warning { background-color: var(--warning); color: white; }
        .btn-danger { background-color: var(--danger); color: white; }
        .btn-info { background-color: var(--info); color: white; }
        .btn-outline { background-color: transparent; color: var(--primary); border: 1px solid var(--primary); }
        .btn-outline:hover { background-color: rgba(0, 64, 128, 0.05); }

        /* Table Styles */
        .table-container { overflow-x: auto; border-radius: var(--border-radius); border: 1px solid var(--light-gray); }
        table { width: 100%; border-collapse: collapse; min-width: 1000px; }
        thead { background-color: var(--primary); color: white; }
        th { padding: 16px 15px; text-align: left; font-weight: 600; font-size: 15px; }
        tbody tr { border-bottom: 1px solid var(--light-gray); transition: background-color 0.2s; }
        tbody tr:hover { background-color: rgba(0, 64, 128, 0.03); }
        td { padding: 15px; color: var(--secondary); }
        .status { padding: 5px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; display: inline-block; }
        .status.completed { background-color: rgba(46, 125, 50, 0.15); color: var(--success); }
        .trend-up { color: var(--success); font-weight: 600; }
        .trend-down { color: var(--danger); font-weight: 600; }
        .trend-neutral { color: var(--gray); font-weight: 600; }

        /* Footer */
        .footer { text-align: center; padding: 20px; color: var(--gray); font-size: 14px; border-top: 1px solid var(--light-gray); margin-top: 20px; }

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
            .filter-row, .stats-cards { grid-template-columns: 1fr; }
            .filter-actions { flex-direction: column; width: 100%; }
            .btn { width: 100%; justify-content: center; }
        }

        /* Loading Spinner */
        .spinner { border: 4px solid rgba(0, 0, 0, 0.1); border-radius: 50%; border-top: 4px solid var(--primary); width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 20px auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        
        /* Modal Styles */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal-content { background-color: white; width: 90%; max-width: 800px; border-radius: var(--border-radius); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); overflow: hidden; max-height: 90vh; overflow-y: auto; }
        .modal-header { padding: 20px 25px; background-color: var(--primary); color: white; display: flex; justify-content: space-between; align-items: center; }
        .modal-body { padding: 25px; }
        .modal-footer { padding: 20px 25px; background-color: #f9f9f9; display: flex; justify-content: flex-end; gap: 15px; border-top: 1px solid var(--light-gray); }
        .close-modal { background: none; border: none; color: white; font-size: 24px; cursor: pointer; line-height: 1; }


    </style>

</head>
<body>
    <aside class="sidebar">
        <div class="logo">
            <h1>Megatek</h1>
            <h2>Industrial Persada</h2>
        </div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="produk.php" class="nav-link"><i class="fas fa-box"></i><span>Produk</span></a></li>
            <li class="nav-item"><a href="pesanan.php" class="nav-link"><i class="fas fa-shopping-cart"></i><span>Pesanan</span></a></li>
            <li class="nav-item"><a href="pelanggan.php" class="nav-link"><i class="fas fa-users"></i><span>Pelanggan</span></a></li>
            <li class="nav-item"><a href="laporan.php" class="nav-link active"><i class="fas fa-chart-bar"></i><span>Laporan</span></a></li>
            <li class="nav-item"><a href="uploadbaner.php" class="nav-link"><i class="fa-solid fa-download"></i><span>Upload Banner</span></a></li>
            <li class="nav-item"><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="header">
            <h1><i class="fas fa-chart-bar"></i> Laporan & Analitik</h1>
            <div class="user-info">
                <span>Admin Megatek</span>
                <div class="avatar">AM</div>
            </div>
        </header>
        <div class="stats-cards">
            <div class="card">
                <div class="card-title">Total Pendapatan</div>
                <div class="card-value">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></div>
                <div class="card-change"><span class="trend-up">+15%</span> dari bulan lalu</div>
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="card">
                <div class="card-title">Total Pesanan</div>
                <div class="card-value"><?php echo number_format($total_pesanan); ?></div>
                <div class="card-change"><span class="trend-up">+8%</span> dari bulan lalu</div>
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="card">
                <div class="card-title">Pelanggan Baru</div>
                <div class="card-value"><?php echo number_format($pelanggan_baru); ?></div>
                <div class="card-change"><span class="trend-up">+5%</span> dari bulan lalu</div>
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="card">
                <div class="card-title">Produk Terjual</div>
                <div class="card-value"><?php echo number_format($total_terjual); ?></div>
                <div class="card-change"><span class="trend-up">+12%</span> dari bulan lalu</div>
                <i class="fas fa-box-open"></i>
            </div>
        </div>

        <div class="charts-section">
            <div class="chart-container">
                <div class="chart-header">
                    <h3 class="chart-title">Grafik Pendapatan</h3>
                </div>
                <div class="chart-wrapper">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            <div class="chart-container">
                <div class="chart-header">
                    <h3 class="chart-title">Penjualan per Kategori</h3>
                    <div class="chart-legend" id="categoryLegend"></div>
                </div>
                <div class="chart-wrapper">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <div class="report-section">

            <div class="section-header">
        <h2 class="section-title">Laporan Bulanan</h2>
        <button class="btn btn-info" id="printReportBtn"><i class="fas fa-print"></i> Cetak</button>
    </div>
            <div class="table-container" id="areaPrint" >

                <table>
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th>Pendapatan</th>
                            <th>Pesanan</th>
                            <th>Pelanggan Baru</th>
                            <th>Produk Terjual</th>
                            <th>Rata-rata Order</th>
                        </tr>
                    </thead>
                    <tbody id="monthlyReportTable">
                        </tbody>
                </table>
            </div>
        </div>

        <div class="report-section" >
            <div class="section-header">
                <h2 class="section-title">Produk Terlaris</h2>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Peringkat</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Terjual</th>
                            <th>Pendapatan</th>
                            <th>% Total</th>
                        </tr>
                    </thead>
                    <tbody id="topProductsTable">
                        </tbody>
                </table>
            </div>
        </div>

        <footer class="footer">
            <p>&copy; 2025 PT Megatek Industrial Persada</p>
            <p style="margin-top: 10px; font-size: 12px;">Update: <span id="lastUpdated"><?php echo date('d F Y H:i'); ?></span></p>
        </footer>
    </main>

    <div class="modal" id="loadingModal">
        <div class="modal-content" style="max-width: 300px; text-align: center;">
            <div class="modal-body">
                <div class="spinner"></div>
                <p style="margin-top: 15px; font-weight: 600; color: var(--primary);">Memproses...</p>
            </div>
        </div>
    </div>

   <script>
        // --- DATA DARI PHP KE JS ---
        const monthlyData = <?php echo json_encode($monthlyData); ?>;
        const topProductsData = <?php echo json_encode($topProductsData); ?>;
        const categoryData = <?php echo json_encode($categoryData); ?>;

        // --- CHART CONFIG ---
        let salesChart = null;
        let categoryChart = null;

        function formatRupiah(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency', currency: 'IDR', minimumFractionDigits: 0
            }).format(amount);
        }

        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        // Render Tabel Bulanan
        function renderMonthlyReport() {
            const table = document.getElementById('monthlyReportTable');
            table.innerHTML = '';
            
            if(monthlyData.length === 0) {
                table.innerHTML = '<tr><td colspan="6" style="text-align:center;">Belum ada data transaksi.</td></tr>';
                return;
            }

            monthlyData.forEach(data => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><strong>${data.month}</strong></td>
                    <td><strong>${formatRupiah(data.revenue)}</strong></td>
                    <td>${formatNumber(data.orders)}</td>
                    <td>${formatNumber(data.newCustomers)}</td>
                    <td>${formatNumber(data.productsSold)}</td>
                    <td>${formatRupiah(data.avgOrderValue)}</td>
                `;
                table.appendChild(row);
            });
        }

        // Render Tabel Produk Terlaris
        function renderTopProducts() {
            const table = document.getElementById('topProductsTable');
            table.innerHTML = '';

            if(topProductsData.length === 0) {
                table.innerHTML = '<tr><td colspan="6" style="text-align:center;">Belum ada produk terjual.</td></tr>';
                return;
            }

            topProductsData.forEach(product => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <div style="width: 30px; height: 30px; border-radius: 50%; background-color: ${product.rank <= 3 ? 'var(--primary)' : '#f0f0f0'}; color: ${product.rank <= 3 ? 'white' : 'var(--secondary)'}; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                            ${product.rank}
                        </div>
                    </td>
                    <td><strong>${product.name}</strong></td>
                    <td><span class="status completed">${product.category}</span></td>
                    <td>${formatNumber(product.sold)}</td>
                    <td><strong>${formatRupiah(product.revenue)}</strong></td>
                    <td>${product.percentage.toFixed(1)}%</td>
                `;
                table.appendChild(row);
            });
        }

        // Chart Penjualan
        function initSalesChart() {
            const ctx = document.getElementById('salesChart').getContext('2d');
            const labels = monthlyData.map(d => d.month);
            const revenues = monthlyData.map(d => d.revenue);

            if (salesChart) salesChart.destroy();

            salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pendapatan',
                        data: revenues,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f0f0f0' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // Chart Kategori
        function initCategoryChart() {
            const ctx = document.getElementById('categoryChart').getContext('2d');
            const legendContainer = document.getElementById('categoryLegend');
            
            if (categoryChart) categoryChart.destroy();
            
            legendContainer.innerHTML = '';
            if(categoryData.length === 0) {
                legendContainer.innerHTML = '<span>Belum ada data</span>';
                return;
            }

            categoryData.forEach(item => {
                const div = document.createElement('div');
                div.className = 'legend-item';
                div.innerHTML = `<div class="legend-color" style="background:${item.color}"></div><span>${item.category}</span>`;
                legendContainer.appendChild(div);
            });

            categoryChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: categoryData.map(d => d.category),
                    datasets: [{
                        data: categoryData.map(d => d.revenue),
                        backgroundColor: categoryData.map(d => d.color),
                        borderWidth: 2,
                        borderColor: 'white'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: { legend: { display: false } }
                }
            });
        }

        // Event Listeners Dasar
        document.addEventListener('DOMContentLoaded', function() {
            renderMonthlyReport();
            renderTopProducts();
            initSalesChart();
            initCategoryChart();

         // GANTI bagian event listener printBtn dengan kode ini:

const printBtn = document.getElementById('printReportBtn');
if (printBtn) {
    printBtn.addEventListener('click', function() {
        // 1. Ambil isi bagian yang mau dicetak
        var printContents = document.getElementById('areaPrint').innerHTML;
        var originalContents = document.body.innerHTML;

        // 2. Ganti layar dengan isi tabel saja
        document.body.innerHTML = printContents;

        // 3. Perintah cetak
        window.print();

        // 4. Kembalikan halaman seperti semula (Reload)
        // Kita gunakan reload agar semua event listener (seperti chart) berfungsi kembali
        window.location.reload(); 
    });
}
            
            // CATATAN: Kode date picker dihapus karena elemen HTML-nya tidak ada
        });
    </script>
</body>
</html>