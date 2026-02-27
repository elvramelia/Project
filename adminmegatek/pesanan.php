<?php
session_start();
require_once '../config/database.php'; // Pastikan path ini benar

// --- BAGIAN 1: LOGIKA PHP ---

$stats = [
    'total' => 0,
    'pending' => 0,
    'processing' => 0,
    'completed' => 0, // Mapping dari 'delivered'
    'cancelled' => 0
];

try {
    // 1. Hitung total semua
    $stmt = $conn->query("SELECT COUNT(*) as total FROM orders");
    $row = $stmt->fetch_assoc();
    $stats['total'] = $row['total'];

    // 2. Hitung per status
    $stmt = $conn->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
    while ($row = $stmt->fetch_assoc()) {
        $status = $row['status'];
        // Mapping status DB ke Display Stats
        if ($status == 'pending') $stats['pending'] = $row['count'];
        if ($status == 'processing') $stats['processing'] = $row['count'];
        if ($status == 'delivered') $stats['completed'] = $row['count']; // delivered = completed di stats
        if ($status == 'cancelled') $stats['cancelled'] = $row['count'];
    }
} catch (Exception $e) {
    // Error handling
}

// 3. Ambil Data Pesanan Lengkap
$final_orders = [];

try {
    $query = "SELECT 
                o.id, 
                o.order_number, 
                o.created_at, 
                o.updated_at,
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
    
    $stmt = $conn->query($query);
    
    if ($stmt) {
        while ($row = $stmt->fetch_assoc()) {
            // Ambil items
            $order_id = $row['id'];
            $stmt_items = $conn->prepare("SELECT product_name, quantity, price, subtotal FROM order_items WHERE order_id = ?");
            $stmt_items->bind_param("i", $order_id);
            $stmt_items->execute();
            $result_items = $stmt_items->get_result();
            
            $formatted_items = [];
            while ($item = $result_items->fetch_assoc()) {
                $formatted_items[] = [
                    'product' => $item['product_name'],
                    'category' => 'Industrial Part',
                    'price' => (float)$item['price'],
                    'quantity' => (int)$item['quantity'],
                    'subtotal' => (float)$item['subtotal']
                ];
            }

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
                'updated_at' => $row['updated_at'],
                'items' => $formatted_items,
                'subtotal' => (float)$row['total_amount'],
                'shipping' => 0, 
                'total' => (float)$row['total_amount'],
                'status' => $row['status'], // Ini harus sesuai ENUM DB: pending, processing, shipped, delivered, cancelled
                'paymentMethod' => $row['payment_method']
            ];
        }
    }

} catch (Exception $e) {
    echo "Error data: " . $e->getMessage();
}

$json_orders = json_encode($final_orders);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pesanan - Admin HKU</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- CSS BAWAAN ANDA (DIPERTAHANKAN) --- */
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

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f9f9f9; color: var(--secondary); display: flex; min-height: 100vh; }

        /* Sidebar & Layout Styles */
        .sidebar { width: 260px; background-color: var(--primary); color: white; padding: 20px 0; position: fixed; height: 100vh; overflow-y: auto; transition: all 0.3s; box-shadow: var(--box-shadow); z-index: 100; }
        .logo { padding: 0 20px 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); margin-bottom: 20px; }
        .logo h1 { font-size: 22px; font-weight: 700; color: white; }
        .logo h2 { font-size: 14px; font-weight: 400; color: rgba(255, 255, 255, 0.8); margin-top: 5px; }
        .nav-menu { list-style: none; padding: 0 15px; }
        .nav-item { margin-bottom: 5px; }
        .nav-link { display: flex; align-items: center; padding: 12px 15px; color: rgba(255, 255, 255, 0.9); text-decoration: none; border-radius: var(--border-radius); transition: all 0.3s; }
        .nav-link:hover, .nav-link.active { background-color: rgba(255, 255, 255, 0.1); color: white; }
        .nav-link i { margin-right: 12px; font-size: 18px; width: 24px; text-align: center; }
        
        .main-content { flex: 1; margin-left: 260px; padding: 20px; transition: all 0.3s; }
        .header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 20px; border-bottom: 1px solid var(--light-gray); margin-bottom: 30px; }
        .header h1 { color: var(--primary); font-size: 28px; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .user-info span { font-weight: 600; color: var(--secondary); }
        .avatar { width: 40px; height: 40px; border-radius: 50%; background-color: var(--primary-light); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; }

        /* Card Stats */
        .stats-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: white; border-radius: var(--border-radius); padding: 20px; box-shadow: var(--box-shadow); border-left: 5px solid var(--primary); }
        .card.pending { border-left-color: var(--warning); }
        .card.processing { border-left-color: var(--info); }
        .card.completed { border-left-color: var(--success); }
        .card-title { font-size: 16px; color: var(--gray); margin-bottom: 10px; }
        .card-value { font-size: 28px; font-weight: 700; color: var(--primary); margin-bottom: 5px; }
        .card i { float: right; font-size: 40px; color: rgba(0, 64, 128, 0.1); margin-top: 10px; }

        /* Table & Filters */
        .orders-section { background: white; border-radius: var(--border-radius); box-shadow: var(--box-shadow); padding: 25px; margin-bottom: 30px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .section-title { font-size: 22px; color: var(--primary); font-weight: 600; }
        .table-container { overflow-x: auto; border-radius: var(--border-radius); border: 1px solid var(--light-gray); }
        table { width: 100%; border-collapse: collapse; min-width: 1000px; }
        thead { background-color: var(--primary); color: white; }
        th { padding: 16px 15px; text-align: left; font-weight: 600; font-size: 15px; }
        tbody tr { border-bottom: 1px solid var(--light-gray); transition: background-color 0.2s; }
        tbody tr:hover { background-color: rgba(0, 64, 128, 0.03); }
        td { padding: 15px; color: var(--secondary); }
        
        .order-status { padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; display: inline-block; }
        .status-pending { background-color: rgba(245, 124, 0, 0.15); color: var(--warning); }
        .status-processing { background-color: rgba(2, 136, 209, 0.15); color: var(--info); }
        .status-shipped { background-color: rgba(123, 31, 162, 0.15); color: purple; } /* New */
        .status-delivered { background-color: rgba(46, 125, 50, 0.15); color: var(--success); }
        .status-cancelled { background-color: rgba(211, 47, 47, 0.15); color: var(--danger); }

        .btn { padding: 8px 15px; border: none; border-radius: var(--border-radius); cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; transition: all 0.3s; font-size: 14px; text-decoration: none; }
        .btn-primary { background-color: var(--primary); color: white; }
        .btn-primary:hover { background-color: var(--primary-light); }
        .btn-outline { background-color: transparent; color: var(--primary); border: 1px solid var(--primary); }
        .btn-outline:hover { background-color: rgba(0, 64, 128, 0.05); }

        /* --- STYLING KHUSUS UNTUK TIMELINE (MENIRU TAMPILAN USER) --- */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal-content { background-color: white; width: 90%; max-width: 900px; border-radius: var(--border-radius); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); overflow: hidden; max-height: 90vh; overflow-y: auto; }
        .modal-header { padding: 20px 25px; background-color: var(--primary); color: white; display: flex; justify-content: space-between; align-items: center; }
        .modal-body { padding: 25px; }
        .order-detail-section { margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        .order-detail-title { font-size: 18px; color: var(--primary); margin-bottom: 15px; font-weight: 700; }

        /* Timeline Container */
        .timeline-wrapper {
            background-color: #f9f9f9;
            padding: 30px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
        }
        
        .timeline-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-steps::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 3px;
            background-color: #e0e0e0;
            z-index: 1;
        }

        .step-item {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            width: 25%;
        }

        .step-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: #e0e0e0;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            font-size: 16px;
            border: 3px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        .step-item.active .step-icon {
            background-color: var(--success);
        }
        
        .step-item.cancelled .step-icon {
            background-color: var(--danger);
        }

        .step-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--gray);
            margin-bottom: 3px;
        }
        
        .step-item.active .step-label {
            color: var(--secondary);
        }

        .step-date {
            font-size: 11px;
            color: #999;
        }

        /* Action Buttons specific for Status */
        .status-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed #ccc;
        }

        .select-status-wrapper {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 15px; }
        .info-row { margin-bottom: 8px; }
        .info-label { font-weight: 600; color: #555; display: inline-block; width: 120px; }
        
        /* Items Table */
        .items-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .items-table th { background: #eee; padding: 10px; text-align: left; font-size: 14px; }
        .items-table td { padding: 10px; border-bottom: 1px solid #eee; font-size: 14px; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        @media (max-width: 768px) {
            .sidebar { display: none; } /* Simplified mobile */
            .main-content { margin-left: 0; }
            .timeline-steps { font-size: 10px; }
            .step-label { font-size: 10px; }
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
            <li class="nav-item"><a href="index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="produk.php" class="nav-link"><i class="fas fa-box"></i><span>Produk</span></a></li>
            <li class="nav-item"><a href="pesanan.php" class="nav-link active"><i class="fas fa-shopping-cart"></i><span>Pesanan</span></a></li>
            <li class="nav-item"><a href="pelanggan.php" class="nav-link"><i class="fas fa-users"></i><span>Pelanggan</span></a></li>
            <li class="nav-item"><a href="laporan.php" class="nav-link"><i class="fas fa-chart-bar"></i><span>Laporan</span></a></li>
            <li class="nav-item"><a href="uploadbaner.php" class="nav-link"><i class="fa-solid fa-download"></i><span>Upload Baner</span></a></li>
            <li class="nav-item"><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="header">
            <h1>Manajemen Pesanan</h1>
            <div class="user-info">
                <span>Admin HKU</span>
                <div class="avatar">AM</div>
            </div>
        </header>

        <div class="stats-cards">
            <div class="card"><div class="card-title">Total Pesanan</div><div class="card-value"><?php echo $stats['total']; ?></div><i class="fas fa-shopping-cart"></i></div>
            <div class="card pending"><div class="card-title">Pending</div><div class="card-value"><?php echo $stats['pending']; ?></div><i class="fas fa-clock"></i></div>
            <div class="card processing"><div class="card-title">Diproses</div><div class="card-value"><?php echo $stats['processing']; ?></div><i class="fas fa-cog"></i></div>
            <div class="card completed"><div class="card-title">Selesai</div><div class="card-value"><?php echo $stats['completed']; ?></div><i class="fas fa-check-circle"></i></div>
        </div>

        <section class="orders-section">
            <div class="section-header">
                <h2 class="section-title">Daftar Pesanan Masuk</h2>
                <input type="text" id="searchOrder" placeholder="Cari No. Pesanan..." style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody">
                        </tbody>
                </table>
            </div>
        </section>
        
        <footer class="footer" style="text-align: center; padding: 20px; color: #777;">
            <p>&copy; 2025 PT Hardjadinata Karya Utama</p>
        </footer>
    </main>

    <div class="modal" id="orderDetailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Detail Pesanan <span id="modalOrderNumber"></span></h3>
                <button onclick="closeModal()" style="background:none; border:none; color:white; font-size:24px; cursor:pointer;">&times;</button>
            </div>
            <div class="modal-body">
                
                <div class="order-detail-section">
                    <h4 class="order-detail-title">Status Pesanan</h4>
                    <div class="timeline-wrapper">
                        <div class="timeline-steps" id="timelineSteps">
                            </div>
                        
                        <div class="status-actions">
                            <div class="select-status-wrapper">
                                <label><strong>Update Status:</strong></label>
                                <select id="updateStatusSelect" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                                    <option value="pending">Pending (Menunggu Pembayaran)</option>
                                    <option value="processing">Processing (Diproses)</option>
                                    <option value="shipped">Shipped (Dikirim)</option>
                                    <option value="delivered">Delivered (Selesai)</option>
                                    <option value="cancelled">Cancelled (Dibatalkan)</option>
                                </select>
                                <button class="btn btn-primary" id="saveStatusBtn">
                                    <i class="fas fa-save"></i> Simpan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="order-detail-section">
                    <div class="info-grid">
                        <div>
                            <h4 style="margin-bottom: 10px;">Informasi Pelanggan</h4>
                            <div class="info-row"><span class="info-label">Nama:</span> <span id="detailName"></span></div>
                            <div class="info-row"><span class="info-label">Email:</span> <span id="detailEmail"></span></div>
                            <div class="info-row"><span class="info-label">Telepon:</span> <span id="detailPhone"></span></div>
                            <div class="info-row"><span class="info-label">Alamat:</span> <span id="detailAddress"></span></div>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 10px;">Informasi Transaksi</h4>
                            <div class="info-row"><span class="info-label">Tanggal:</span> <span id="detailDate"></span></div>
                            <div class="info-row"><span class="info-label">Pembayaran:</span> <span id="detailPayment"></span></div>
                            <div class="info-row"><span class="info-label">Total:</span> <span id="detailTotalMain" style="font-weight:bold; color:var(--primary);"></span></div>
                        </div>
                    </div>
                </div>

                <div class="order-detail-section" style="border: none;">
                    <h4 class="order-detail-title">Produk Dibeli</h4>
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="detailItemsBody"></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-right font-bold">Grand Total</td>
                                <td class="font-bold" id="detailGrandTotal"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Data dari PHP
        let orders = <?php echo $json_orders; ?>;
        let currentOrderId = null;

        // Helper: Format Rupiah
        const formatRupiah = (num) => {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
        };

        // Helper: Format Tanggal
        const formatDate = (dateString) => {
            if(!dateString) return '-';
            const options = { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute:'2-digit' };
            return new Date(dateString).toLocaleDateString('id-ID', options);
        };

        // Helper: Get Badge Class
        const getStatusBadge = (status) => {
            switch(status) {
                case 'pending': return '<span class="order-status status-pending">Pending</span>';
                case 'processing': return '<span class="order-status status-processing">Diproses</span>';
                case 'shipped': return '<span class="order-status status-shipped">Dikirim</span>';
                case 'delivered': return '<span class="order-status status-delivered">Selesai</span>';
                case 'cancelled': return '<span class="order-status status-cancelled">Dibatalkan</span>';
                default: return status;
            }
        };

        // RENDER TABEL UTAMA
        function renderTable(data) {
            const tbody = document.getElementById('ordersTableBody');
            tbody.innerHTML = '';
            
            if(data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Tidak ada data</td></tr>';
                return;
            }

            data.forEach(order => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="font-weight:600; color:var(--primary);">${order.orderNumber}</td>
                    <td>
                        <div>${order.customer.name}</div>
                        <small style="color:#888">${order.customer.email}</small>
                    </td>
                    <td>${formatDate(order.date)}</td>
                    <td style="font-weight:bold;">${formatRupiah(order.total)}</td>
                    <td>${getStatusBadge(order.status)}</td>
                    <td>
                        <button class="btn btn-outline" onclick="openDetail(${order.id})">
                            <i class="fas fa-eye"></i> Detail
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        // GENERATE VISUAL TIMELINE (Bagian penting sesuai request)
        function renderTimeline(status, dateCreated, dateUpdated) {
            const container = document.getElementById('timelineSteps');
            container.innerHTML = '';

            // Definisi Steps sesuai database enum
            const steps = [
                { key: 'pending', label: 'Pesanan Dibuat', icon: 'fa-file-invoice' },
                { key: 'processing', label: 'Diproses', icon: 'fa-cog' },
                { key: 'shipped', label: 'Dikirim', icon: 'fa-shipping-fast' },
                { key: 'delivered', label: 'Selesai', icon: 'fa-check' }
            ];

            // Tentukan index status saat ini
            let currentIndex = -1;
            if (status === 'cancelled') {
                currentIndex = -1; // Special case
            } else {
                currentIndex = steps.findIndex(s => s.key === status);
            }

            // Jika status cancelled, tampilkan tampilan khusus
            if(status === 'cancelled') {
                container.innerHTML = `
                    <div style="width:100%; text-align:center; padding:20px; background-color:#ffebee; border-radius:8px; border:1px solid #ef9a9a;">
                        <i class="fas fa-times-circle" style="font-size:30px; color:#d32f2f; margin-bottom:10px;"></i>
                        <h4 style="color:#d32f2f;">Pesanan Dibatalkan</h4>
                        <p style="color:#555;">Pesanan ini telah dibatalkan pada ${formatDate(dateUpdated)}</p>
                    </div>
                `;
                return;
            }

            // Render 4 steps standard
            steps.forEach((step, index) => {
                const isActive = index <= currentIndex;
                const isCurrent = index === currentIndex;
                
                // Tentukan tanggal yang ditampilkan di bawah step
                let displayDate = '';
                if (index === 0) displayDate = formatDate(dateCreated); // Created date for pending
                else if (isCurrent && dateUpdated) displayDate = formatDate(dateUpdated); // Updated date for current status

                const html = `
                    <div class="step-item ${isActive ? 'active' : ''}">
                        <div class="step-icon">
                            <i class="fas ${step.icon}"></i>
                        </div>
                        <div class="step-label">${step.label}</div>
                        <div class="step-date">${displayDate}</div>
                    </div>
                `;
                container.innerHTML += html;
            });
        }

        // BUKA MODAL DETAIL
        window.openDetail = function(id) {
            const order = orders.find(o => o.id === id);
            if (!order) return;

            currentOrderId = id;

            // Isi Info Header
            document.getElementById('modalOrderNumber').innerText = order.orderNumber;
            document.getElementById('detailName').innerText = order.customer.name;
            document.getElementById('detailEmail').innerText = order.customer.email;
            document.getElementById('detailPhone').innerText = order.customer.phone;
            document.getElementById('detailAddress').innerText = order.customer.address;
            
            document.getElementById('detailDate').innerText = formatDate(order.date);
            document.getElementById('detailPayment').innerText = order.paymentMethod.toUpperCase();
            document.getElementById('detailTotalMain').innerText = formatRupiah(order.total);

            // Render Timeline
            renderTimeline(order.status, order.date, order.updated_at);

            // Set Dropdown value ke status sekarang
            document.getElementById('updateStatusSelect').value = order.status;

            // Render Items Table
            const itemsBody = document.getElementById('detailItemsBody');
            itemsBody.innerHTML = '';
            order.items.forEach(item => {
                itemsBody.innerHTML += `
                    <tr>
                        <td>${item.product}</td>
                        <td>${formatRupiah(item.price)}</td>
                        <td>${item.quantity}</td>
                        <td>${formatRupiah(item.subtotal)}</td>
                    </tr>
                `;
            });
            document.getElementById('detailGrandTotal').innerText = formatRupiah(order.total);

            document.getElementById('orderDetailModal').style.display = 'flex';
        };

        function closeModal() {
            document.getElementById('orderDetailModal').style.display = 'none';
        }

        // FUNGSI UPDATE STATUS (AJAX)
        document.getElementById('saveStatusBtn').addEventListener('click', function() {
            if(!currentOrderId) return;

            const newStatus = document.getElementById('updateStatusSelect').value;
            const btn = this;
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

            // Kirim request ke update_status.php
            fetch('update_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${currentOrderId}&status=${newStatus}`
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert('Status berhasil diperbarui!');
                    // Update data lokal JS tanpa reload
                    const idx = orders.findIndex(o => o.id === currentOrderId);
                    if(idx !== -1) {
                        orders[idx].status = newStatus;
                        orders[idx].updated_at = new Date().toISOString(); // Update timestamp simulasi
                    }
                    renderTable(orders); // Re-render tabel depan
                    openDetail(currentOrderId); // Re-render modal timeline
                } else {
                    alert('Gagal: ' + data.message);
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan koneksi');
                console.error(err);
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });

        // Search Filter Simple
        document.getElementById('searchOrder').addEventListener('input', function(e) {
            const val = e.target.value.toLowerCase();
            const filtered = orders.filter(o => 
                o.orderNumber.toLowerCase().includes(val) || 
                o.customer.name.toLowerCase().includes(val)
            );
            renderTable(filtered);
        });

        // Init
        renderTable(orders);

        // Close modal on outside click
        window.onclick = function(e) {
            const modal = document.getElementById('orderDetailModal');
            if (e.target == modal) closeModal();
        }
    </script>
</body>
</html>