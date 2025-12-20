<?php
require_once 'config/database.php';
require_once 'config/check_login.php';

if (!isLoggedIn()) {
    header('Location: index.php?login_required=1');
    exit();
}

if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header('Location: index.php');
    exit();
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

// Fetch order details
$order_query = "
    SELECT o.*, 
           CONCAT(u.first_name, ' ', u.last_name) as customer_name,
           u.email, u.phone_number
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.user_id = ?
";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();

if (!$order) {
    header('Location: index.php');
    exit();
}

// Fetch order items
$items_query = "SELECT * FROM order_items WHERE order_id = ?";
$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pesanan - Megatek Industrial Persada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Reuse the CSS from checkout.php with minor adjustments */
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

        .confirmation-hero {
            background-color: var(--primary-blue);
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .confirmation-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .confirmation-card {
            background-color: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
        }

        .success-icon {
            font-size: 4rem;
            color: #4CAF50;
            margin-bottom: 20px;
        }

        .order-details {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .payment-instruction {
            background-color: #f0f7ff;
            border-left: 4px solid var(--primary-blue);
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            text-align: left;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn-print, .btn-track {
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-print {
            background-color: var(--primary-blue);
            color: white;
        }

        .btn-track {
            background-color: white;
            color: var(--primary-blue);
            border: 1px solid var(--primary-blue);
        }

        .btn-print:hover, .btn-track:hover {
            opacity: 0.9;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <section class="confirmation-hero">
        <div class="container">
            <h1>Pesanan Berhasil!</h1>
            <p>Terima kasih telah berbelanja di Megatek Industrial Persada</p>
        </div>
    </section>

    <div class="confirmation-container">
        <div class="confirmation-card">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h2>Pesanan Anda Telah Diterima</h2>
            <p class="text-muted">Nomor Pesanan: <strong><?php echo $order['order_number']; ?></strong></p>
            <p>Kami telah mengirimkan detail pesanan ke email: <?php echo $order['email']; ?></p>
            
            <div class="order-details">
                <h4>Detail Pesanan</h4>
                <div class="detail-row">
                    <span>Tanggal Pesanan:</span>
                    <span><?php echo date('d F Y H:i', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="detail-row">
                    <span>Total Pembayaran:</span>
                    <span class="text-primary fw-bold">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></span>
                </div>
                <div class="detail-row">
                    <span>Metode Pembayaran:</span>
                    <span><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></span>
                </div>
                <div class="detail-row">
                    <span>Status:</span>
                    <span class="badge bg-warning"><?php echo ucfirst($order['status']); ?></span>
                </div>
            </div>

            <?php if ($order['payment_method'] === 'bank_transfer'): ?>
            <div class="payment-instruction">
                <h4><i class="fas fa-info-circle me-2"></i>Instruksi Pembayaran</h4>
                <p>Silakan lakukan transfer ke:</p>
                <p><strong>Bank BCA</strong><br>
                No. Rekening: 1234567890<br>
                Atas Nama: Megatek Industrial Persada</p>
                <p><strong>Jumlah Transfer:</strong> Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></p>
                <p class="text-muted">Konfirmasi pembayaran akan diproses dalam 1x24 jam setelah transfer dilakukan.</p>
            </div>
            <?php elseif ($order['payment_method'] === 'qris'): ?>
            <div class="payment-instruction">
                <h4><i class="fas fa-info-circle me-2"></i>Instruksi Pembayaran</h4>
                <p>Silakan selesaikan pembayaran dengan QRIS melalui aplikasi e-wallet atau mobile banking Anda.</p>
                <p class="text-muted">Pembayaran akan diverifikasi secara otomatis.</p>
            </div>
            <?php elseif ($order['payment_method'] === 'cod'): ?>
            <div class="payment-instruction">
                <h4><i class="fas fa-info-circle me-2"></i>Instruksi Pembayaran</h4>
                <p>Anda akan membayar saat barang diterima di alamat:</p>
                <p><strong><?php echo $order['shipping_address']; ?></strong></p>
                <p class="text-muted">Kurir akan menghubungi Anda 1 jam sebelum pengiriman.</p>
            </div>
            <?php endif; ?>

            <div class="action-buttons">
                <a href="javascript:window.print()" class="btn-print">
                    <i class="fas fa-print"></i> Cetak Invoice
                </a>
                <a href="orders.php" class="btn-track">
                    <i class="fas fa-shopping-bag"></i> Lihat Pesanan Saya
                </a>
                <a href="produk.php" class="btn-track">
                    <i class="fas fa-shopping-cart"></i> Lanjut Belanja
                </a>
            </div>

            <p class="text-muted mt-4">
                <small>
                    Butuh bantuan? Hubungi <a href="mailto:support@megatek.co.id">support@megatek.co.id</a> atau 
                    <a href="tel:+623112345678">+62 31 1234 5678</a>
                </small>
            </p>
        </div>
    </div>

    <script>
        // Print functionality
        function printInvoice() {
            window.print();
        }

        // Auto redirect after 30 seconds for inactivity
        let idleTime = 0;
        const idleInterval = setInterval(timerIncrement, 1000);

        function timerIncrement() {
            idleTime++;
            if (idleTime > 300) { // 5 minutes
                window.location.href = "index.php";
            }
        }

        // Reset idle time on user activity
        document.addEventListener('mousemove', function() {
            idleTime = 0;
        });

        document.addEventListener('keypress', function() {
            idleTime = 0;
        });
    </script>
</body>
</html>