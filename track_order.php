<?php
require_once 'config/database.php';
require_once 'config/check_login.php';

// 1. Cek Login
if (!isLoggedIn()) {
    header('Location: index.php?login_required=1');
    exit();
}

// 2. Validasi ID Pesanan
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// 3. Ambil Data Pesanan
// Catatan: Saya menambahkan field 'tracking_number' dan 'courier' pada query. 
// Pastikan tabel 'orders' Anda memiliki kolom ini, atau hapus bagian tersebut jika belum ada.
$query = "
    SELECT o.*, 
           (SELECT SUM(quantity) FROM order_items WHERE order_id = o.id) as total_items
    FROM orders o 
    WHERE o.id = ? AND o.user_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Pesanan tidak ditemukan atau bukan milik user ini
    header('Location: orders.php');
    exit();
}

$order = $result->fetch_assoc();

// 4. Ambil Item Pesanan
$items_query = "
    SELECT oi.*, p.image_url, p.name AS product_name
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
";
$stmt_items = $conn->prepare($items_query);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);

// 5. Logika Timeline Status
$status_steps = ['pending', 'processing', 'shipped', 'delivered'];
$current_status = $order['status'];
$current_step_index = array_search($current_status, $status_steps);

// Jika status cancelled, kita set index ke -1 atau handle khusus
if ($current_status === 'cancelled') {
    $current_step_index = -1;
}

// Data Dummy untuk Riwayat Pengiriman (Karena belum ada API kurir real)
// Dalam aplikasi nyata, ini diambil dari API JNE/J&T/Sicepat
$tracking_history = [];
if ($current_status == 'pending') {
    $tracking_history[] = ['date' => $order['created_at'], 'desc' => 'Pesanan berhasil dibuat, menunggu pembayaran.'];
} elseif ($current_status == 'processing') {
    $tracking_history[] = ['date' => $order['updated_at'], 'desc' => 'Pembayaran dikonfirmasi. Pesanan sedang diproses oleh gudang.'];
    $tracking_history[] = ['date' => $order['created_at'], 'desc' => 'Pesanan berhasil dibuat.'];
} elseif ($current_status == 'shipped') {
    $tracking_history[] = ['date' => date('Y-m-d H:i:s'), 'desc' => 'Paket telah diserahkan ke kurir pengiriman.'];
    $tracking_history[] = ['date' => $order['updated_at'], 'desc' => 'Pesanan selesai dikemas dan siap dikirim.'];
    $tracking_history[] = ['date' => $order['created_at'], 'desc' => 'Pesanan berhasil dibuat.'];
} elseif ($current_status == 'delivered') {
    $tracking_history[] = ['date' => $order['updated_at'], 'desc' => 'Paket telah diterima oleh Pelanggan.'];
    $tracking_history[] = ['date' => date('Y-m-d H:i:s', strtotime($order['updated_at'] . ' - 1 day')), 'desc' => 'Paket sedang diantar kurir ke lokasi tujuan.'];
    $tracking_history[] = ['date' => date('Y-m-d H:i:s', strtotime($order['updated_at'] . ' - 2 days')), 'desc' => 'Paket keluar dari hub transit Surabaya.'];
    $tracking_history[] = ['date' => $order['created_at'], 'desc' => 'Pesanan berhasil dibuat.'];
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Pesanan #<?php echo $order['order_number']; ?> - Megatek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-blue: #1a4b8c;
            --light-gray: #f8f9fa;
            --dark-gray: #222;
            --success-green: #28a745;
        }

        body {
            font-family: "Poppins", sans-serif;
            background-color: var(--light-gray);
            color: var(--dark-gray);
        }

        /* Navbar & Topbar Styles (Sama seperti orders.php agar konsisten) */
        .top-bar { background-color: #f0f2f5; padding: 5px 0; font-size: 12px; border-bottom: 1px solid #e0e0e0; }
        .navbar { background-color: white; box-shadow: 0 2px 6px rgba(0,0,0,0.1); padding: 10px 20px; }
        .navbar-brand img { height: 40px; }
        .nav-icons { display: flex; align-items: center; gap: 20px; }
        .nav-icon { display: flex; flex-direction: column; align-items: center; color: #666; text-decoration: none; font-size: 12px; }
        .nav-icon:hover { color: var(--primary-blue); }
        
        /* Main Menu */
        .main-menu { background-color: white; border-bottom: 1px solid #e0e0e0; margin-bottom: 30px; }
        .menu-container { max-width: 1200px; margin: 0 auto; padding: 0 15px; display: flex; gap: 30px; }
        .menu-category { padding: 15px 0; color: var(--dark-gray); text-decoration: none; font-weight: 500; border-bottom: 3px solid transparent; }
        .menu-category:hover, .menu-category.active { color: var(--primary-blue); border-bottom-color: var(--primary-blue); }

        /* Tracking Layout */
        .track-container {
            max-width: 1000px;
            margin: 0 auto 50px;
            padding: 0 15px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #666;
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .back-link:hover { color: var(--primary-blue); }

        .track-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .track-header {
            background-color: var(--primary-blue);
            color: white;
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .track-header h2 { margin: 0; font-size: 1.5rem; font-weight: 600; }
        .track-header p { margin: 0; opacity: 0.9; font-size: 0.95rem; }

        .est-date {
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 8px;
            text-align: right;
        }
        .est-date small { display: block; font-size: 0.8rem; }
        .est-date span { font-weight: 600; font-size: 1.1rem; }

        .track-body { padding: 30px; }

        /* Progress Bar Horizontal */
        .track-progress {
            position: relative;
            display: flex;
            justify-content: space-between;
            margin-bottom: 50px;
            margin-top: 20px;
        }

        .progress-line-bg {
            position: absolute;
            top: 20px;
            left: 0;
            width: 100%;
            height: 4px;
            background-color: #e0e0e0;
            z-index: 1;
        }

        .progress-line-fill {
            position: absolute;
            top: 20px;
            left: 0;
            height: 4px;
            background-color: var(--success-green);
            z-index: 2;
            transition: width 0.5s ease;
        }

        .track-step {
            position: relative;
            z-index: 3;
            text-align: center;
            width: 25%;
        }

        .step-icon {
            width: 45px;
            height: 45px;
            background-color: white;
            border: 4px solid #e0e0e0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            color: #ccc;
            font-size: 18px;
            transition: all 0.3s;
        }

        .track-step.active .step-icon {
            border-color: var(--primary-blue);
            color: white;
            background-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(26, 75, 140, 0.2);
        }

        .track-step.completed .step-icon {
            border-color: var(--success-green);
            background-color: var(--success-green);
            color: white;
        }

        .step-label { font-weight: 600; color: #999; font-size: 0.9rem; margin-bottom: 5px; }
        .track-step.active .step-label { color: var(--primary-blue); }
        .track-step.completed .step-label { color: var(--success-green); }
        
        .step-date { font-size: 0.8rem; color: #999; }

        /* Layout Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        /* Tracking History (Vertical Timeline) */
        .history-section h4, .items-section h4 {
            color: var(--dark-gray);
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .timeline-vertical {
            list-style: none;
            padding: 0;
            margin: 0;
            border-left: 2px solid #e0e0e0;
            margin-left: 10px;
        }

        .timeline-item {
            position: relative;
            padding-left: 30px;
            padding-bottom: 25px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -6px;
            top: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #ccc;
            border: 2px solid white;
        }

        .timeline-item:first-child::before {
            background-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(26, 75, 140, 0.2);
            width: 14px;
            height: 14px;
            left: -8px;
        }

        .timeline-time {
            font-size: 0.85rem;
            color: #888;
            margin-bottom: 3px;
        }

        .timeline-desc {
            font-size: 0.95rem;
            color: var(--dark-gray);
            line-height: 1.5;
        }

        /* Right Column Info */
        .info-card {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .info-label { font-size: 0.85rem; color: #666; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-value { font-weight: 600; color: var(--dark-gray); font-size: 1rem; }
        
        .product-mini {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .product-mini:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        
        .product-img {
            width: 60px;
            height: 60px;
            border-radius: 6px;
            object-fit: cover;
            background-color: #fff;
            border: 1px solid #eee;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .track-progress { flex-direction: column; gap: 20px; align-items: flex-start; margin-left: 20px; border-left: 4px solid #e0e0e0; }
            .progress-line-bg, .progress-line-fill { display: none; }
            .track-step { width: 100%; display: flex; align-items: center; gap: 15px; text-align: left; }
            .step-icon { margin: 0; margin-left: -24px; z-index: 5; }
            
            .info-grid { grid-template-columns: 1fr; }
            .track-header { text-align: center; justify-content: center; }
            .est-date { text-align: center; width: 100%; }
        }
    </style>
</head>
<body>

    <nav class="navbar d-flex align-items-center">
        <a class="navbar-brand mx-2" href="index.php">
            <img src="gambar/LOGO.png" alt="Megatek Logo">
        </a>
        <div class="search-bar" style="flex-grow: 1; max-width: 500px; margin: 0 auto; position: relative;">
            <input type="text" class="form-control" placeholder="Cari pesanan..." disabled style="background-color: #f8f9fa;">
        </div>
        <div class="nav-icons">
            <a href="keranjang.php" class="nav-icon">
                <i class="fas fa-shopping-cart"></i> <span>Cart</span>
            </a>
            <div class="user-dropdown">
                <a href="pesanan.php" class="nav-icon active">
                    <i class="fas fa-user"></i> <span>Akun</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="main-menu">
        <div class="menu-container">
            <a href="beranda.php" class="menu-category"><span>Beranda</span></a>
            <a href="tentangkami.php" class="menu-category">Tentang Kami</a>
            <a href="produk.php" class="menu-category">Produk</a>
            <a href="hubungikami.php" class="menu-category">Hubungi Kami</a>
        </div>
    </div>

    <div class="track-container">
        <a href="orders.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Pesanan
        </a>

        <div class="track-card">
            <div class="track-header">
                <div>
                    <h2>Lacak Pesanan</h2>
                    <p>No. Pesanan: <strong>#<?php echo $order['order_number']; ?></strong></p>
                </div>
                
                <?php if ($order['status'] !== 'cancelled'): ?>
                <div class="est-date">
                    <small>Estimasi Tiba</small>
                    <span>
                        <?php 
                        // Simulasi estimasi tanggal (H+3 dari update terakhir)
                        echo date('d M Y', strtotime($order['updated_at'] . ' + 3 days')); 
                        ?>
                    </span>
                </div>
                <?php else: ?>
                <div class="est-date" style="background: rgba(220, 53, 69, 0.2); color: white;">
                    <span>DIBATALKAN</span>
                </div>
                <?php endif; ?>
            </div>

            <div class="track-body">
                <?php if ($order['status'] !== 'cancelled'): ?>
                    <?php 
                        // Hitung persentase progress bar
                        $progress_percent = 0;
                        if ($current_status == 'pending') $progress_percent = 12; // Sedikit jalan
                        elseif ($current_status == 'processing') $progress_percent = 38;
                        elseif ($current_status == 'shipped') $progress_percent = 65;
                        elseif ($current_status == 'delivered') $progress_percent = 100;
                    ?>
                    
                    <div class="track-progress">
                        <div class="progress-line-bg"></div>
                        <div class="progress-line-fill" style="width: <?php echo $progress_percent; ?>%;"></div>

                        <div class="track-step <?php echo $current_step_index >= 0 ? ($current_step_index > 0 ? 'completed' : 'active') : ''; ?>">
                            <div class="step-icon"><i class="fas fa-clipboard-list"></i></div>
                            <div class="step-label">Pesanan Dibuat</div>
                            <div class="step-date"><?php echo date('d M', strtotime($order['created_at'])); ?></div>
                        </div>

                        <div class="track-step <?php echo $current_step_index >= 1 ? ($current_step_index > 1 ? 'completed' : 'active') : ''; ?>">
                            <div class="step-icon"><i class="fas fa-box-open"></i></div>
                            <div class="step-label">Diproses</div>
                            <div class="step-date">
                                <?php echo ($current_step_index >= 1) ? date('d M', strtotime($order['updated_at'])) : '-'; ?>
                            </div>
                        </div>

                        <div class="track-step <?php echo $current_step_index >= 2 ? ($current_step_index > 2 ? 'completed' : 'active') : ''; ?>">
                            <div class="step-icon"><i class="fas fa-shipping-fast"></i></div>
                            <div class="step-label">Dikirim</div>
                            <div class="step-date">
                                <?php echo ($current_step_index >= 2) ? date('d M', strtotime($order['updated_at'])) : '-'; ?>
                            </div>
                        </div>

                        <div class="track-step <?php echo $current_step_index >= 3 ? 'completed' : ''; ?>">
                            <div class="step-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="step-label">Selesai</div>
                            <div class="step-date">
                                <?php echo ($current_step_index >= 3) ? date('d M', strtotime($order['updated_at'])) : '-'; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger text-center">
                        <i class="fas fa-times-circle fa-3x mb-3"></i><br>
                        <h4>Pesanan Dibatalkan</h4>
                        <p>Pesanan ini telah dibatalkan pada <?php echo date('d F Y', strtotime($order['updated_at'])); ?></p>
                    </div>
                <?php endif; ?>

                <div class="info-grid">
                    <div class="history-section">
                        <h4>Riwayat Perjalanan</h4>
                        <?php if (empty($tracking_history) && $order['status'] !== 'cancelled'): ?>
                            <p class="text-muted">Belum ada riwayat update.</p>
                        <?php else: ?>
                            <ul class="timeline-vertical">
                                <?php foreach ($tracking_history as $history): ?>
                                    <li class="timeline-item">
                                        <div class="timeline-time"><?php echo date('d F Y, H:i', strtotime($history['date'])); ?> WIB</div>
                                        <div class="timeline-desc"><?php echo htmlspecialchars($history['desc']); ?></div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <div class="items-section mt-5">
                            <h4>Item Pesanan (<?php echo $order['total_items']; ?>)</h4>
                            <?php foreach ($order_items as $item): ?>
                                <div class="product-mini">
                                    <img src="uploads/<?php echo htmlspecialchars($item['image_url'] ?? 'img/produk-sample.png'); ?>" alt="Produk" class="product-img">
                                    <div>
                                        <div style="font-weight: 500;"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                        <small class="text-muted"><?php echo $item['quantity']; ?> x Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="details-section">
                        <div class="info-card">
                            <div class="mb-3">
                                <div class="info-label">Jasa Pengiriman</div>
                                <div class="info-value">
                                    <?php echo !empty($order['courier']) ? strtoupper($order['courier']) : 'Megatek Express'; ?>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="info-label">Nomor Resi</div>
                                <div class="info-value text-primary">
                                    <?php 
                                    if (!empty($order['tracking_number'])) {
                                        echo htmlspecialchars($order['tracking_number']);
                                        echo ' <i class="far fa-copy ms-2" style="cursor:pointer; font-size:0.8em;" onclick="alert(\'Resi disalin!\')"></i>';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="info-label">Penerima</div>
                                <div class="info-value"><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></div>
                            </div>

                            <div class="mb-0">
                                <div class="info-label">Alamat Pengiriman</div>
                                <div class="info-value" style="font-size: 0.9rem; font-weight: 400;">
                                    <?php echo htmlspecialchars($order['shipping_address']); ?>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="contact.php" class="btn btn-outline-primary">
                                <i class="fas fa-headset me-2"></i> Bantuan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="copyright text-center pt-3 pb-3 border-top">
                © Copyright 2023 PT. Megatek Industrial Persada. All rights reserved.
            </div>
        </div>
    </footer>

</body>
</html>