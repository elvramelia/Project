<?php
session_start();

// Jika aksi logout benar-benar dikonfirmasi (dipanggil dari JavaScript)
if (isset($_GET['action']) && $_GET['action'] == 'do_logout') {
    // Hapus semua session
    session_unset();
    session_destroy();
    
    // Redirect ke halaman beranda di luar folder adminmegatek
    header("Location: ../beranda.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Admin Hardjadinata Karya Utama</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            /* WARNA DISESUAIKAN DENGAN LOGO HKU */
            --primary: #0021A5; 
            --primary-light: #1A3DBF; 
            --secondary: #333333;
            --accent: #E30613; 
            --light: #f5f5f5;
            --danger: #E30613; 
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

        /* PERBAIKAN: Menghapus justify-content & align-items dari body */
        body {
            background-color: #f9f9f9;
            color: var(--secondary);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar & Layout Styles */
        .sidebar { width: 260px; background-color: var(--primary); color: white; padding: 20px 0; position: fixed; height: 100vh; overflow-y: auto; transition: all 0.3s; box-shadow: var(--box-shadow); z-index: 100; }
        
        .logo { padding: 0 20px 20px; border-bottom: 3px solid var(--accent); margin-bottom: 20px; }
        .logo h1 { font-size: 22px; font-weight: 700; color: white; }
        .logo h2 { font-size: 14px; font-weight: 600; color:  white; margin-top: 5px; }
        
        .nav-menu { list-style: none; padding: 0 15px; }
        .nav-item { margin-bottom: 5px; }
        .nav-link { display: flex; align-items: center; padding: 12px 15px; color: rgba(255, 255, 255, 0.9); text-decoration: none; border-radius: var(--border-radius); transition: all 0.3s; }
        .nav-link { display: flex; align-items: center; padding: 12px 15px; color: rgba(255, 255, 255, 0.9); text-decoration: none; border-radius: var(--border-radius); transition: all 0.3s; font-size: 15px;}
        .nav-link:hover, .nav-link.active { background-color: rgba(255, 255, 255, 0.1); color: white; border-left: 4px solid var(--accent); }
        .nav-link i { margin-right: 12px; font-size: 18px; width: 24px; text-align: center; }

        /* PERBAIKAN: Main Content dibuat flex column & center */
        .main-content { 
            flex: 1; 
            margin-left: 260px; 
            padding: 20px; 
            transition: all 0.3s; 
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #f9f9f9 0%, #eef1f6 100%);
        }

        /* Logout Container */
        .logout-container {
            width: 100%;
            max-width: 500px;
        }

        .logout-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            padding: 50px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .logout-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%);
        }

        .logout-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin: 0 auto 30px;
            box-shadow: 0 8px 20px rgba(0, 33, 165, 0.2);
        }

        .logout-title {
            font-size: 26px;
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 15px;
        }

        .logout-subtitle {
            color: var(--gray);
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .user-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            border: 1px solid var(--light-gray);
        }

        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--accent);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
        }

        .user-details { text-align: left; }
        .user-details h3 { font-weight: 600; color: var(--secondary); margin-bottom: 5px; font-size: 16px; }
        .user-details p { color: var(--gray); font-size: 13px; }

        .logout-message {
            background-color: rgba(0, 33, 165, 0.05);
            border-left: 4px solid var(--primary);
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            text-align: left;
        }

        .logout-message p { color: var(--secondary); line-height: 1.6; font-size: 13px; }
        .logout-message i { color: var(--primary); margin-right: 10px; }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
            font-size: 14px;
            flex: 1;
        }

        .btn-primary { background-color: var(--primary); color: white; }
        .btn-primary:hover { background-color: var(--primary-light); transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0, 33, 165, 0.2); }
        .btn-outline { background-color: transparent; color: var(--primary); border: 2px solid var(--primary); }
        .btn-outline:hover { background-color: rgba(0, 33, 165, 0.05); transform: translateY(-2px); }
        .btn-danger { background-color: var(--danger); color: white; }
        .btn-danger:hover { background-color: #c0392b; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(227, 6, 19, 0.2); }

        .btn-group {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }

        .countdown {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--light-gray);
        }

        .countdown-text { color: var(--gray); font-size: 13px; margin-bottom: 10px; }
        .countdown-timer { font-size: 24px; font-weight: 700; color: var(--primary); }

        /* Footer */
        .footer { text-align: center; padding: 20px; color: var(--gray); font-size: 13px; margin-top: 20px; }

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
            .btn-group { flex-direction: column; }
            .btn { width: 100%; }
        }

        /* Animation */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { animation: fadeIn 0.8s ease-out; }
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }
        .pulse { animation: pulse 2s infinite; }
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
            <li class="nav-item"><a href="pesanan.php" class="nav-link"><i class="fas fa-shopping-cart"></i><span>Pesanan</span></a></li>
            <li class="nav-item"><a href="pelanggan.php" class="nav-link"><i class="fas fa-users"></i><span>Users</span></a></li>
            <li class="nav-item"><a href="laporan.php" class="nav-link"><i class="fas fa-chart-bar"></i><span>Laporan</span></a></li>
            <li class="nav-item">
                <a href="uploadbaner.php" class="nav-link">
                    <i class="fas fa-upload"></i>
                    <span>Upload Banner</span>
                </a>
            </li>
            <li class="nav-item"><a href="logout.php" class="nav-link active"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="logout-container">
            <div class="logout-card fade-in">
                <div class="logout-icon">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                
                <h1 class="logout-title">Keluar dari Sistem</h1>
                
                <p class="logout-subtitle">
                    Anda akan keluar dari Admin Panel. Pastikan Anda telah menyimpan semua pekerjaan sebelum melanjutkan.
                </p>
                
                <div class="user-info">
                    <div class="avatar">AM</div>
                    <div class="user-details">
                        <h3>Admin HKU</h3>
                        <p>Administrator Panel</p>
                        <p style="color: var(--primary); font-size: 12px; margin-top: 5px;">
                            <i class="fas fa-clock"></i> Waktu: <?php echo date('d M Y, H:i'); ?>
                        </p>
                    </div>
                </div>
                
                <div class="logout-message">
                    <p><i class="fas fa-info-circle"></i> Setelah logout, sesi Anda akan dihapus dan Anda akan diarahkan kembali ke halaman beranda publik website.</p>
                </div>
                
                <div class="btn-group">
                    <button class="btn btn-outline" id="cancelBtn">
                        <i class="fas fa-arrow-left"></i> Batal
                    </button>
                    <button class="btn btn-danger" id="logoutBtn">
                        <i class="fas fa-power-off"></i> Ya, Keluar
                    </button>
                </div>
                
                <div class="countdown" id="countdownSection" style="display: none;">
                    <p class="countdown-text">Mengarahkan ke Beranda dalam:</p>
                    <div class="countdown-timer" id="countdownTimer">3</div>
                </div>
            </div>
            
            <div class="footer">
                <p>&copy; 2026 Hardjadinata Karya Utama - Your Trusted Industrial Partner</p>
            </div>
        </div>
    </main>

    <div class="modal" id="successModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.6); z-index: 1000; align-items: center; justify-content: center;">
        <div class="modal-content" style="background-color: white; width: 90%; max-width: 400px; border-radius: var(--border-radius); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); overflow: hidden; text-align: center; padding: 40px 30px;">
            <div class="logout-icon" style="margin-bottom: 25px; background: var(--success); box-shadow: 0 8px 20px rgba(46, 125, 50, 0.3);">
                <i class="fas fa-check"></i>
            </div>
            <h2 style="color: var(--success); margin-bottom: 15px; font-size: 24px;">Berhasil Logout</h2>
            <p style="color: var(--gray); margin-bottom: 30px; line-height: 1.6; font-size: 14px;">Sesi login Anda telah dihapus. Mengarahkan kembali ke beranda publik...</p>
            <button class="btn btn-primary" id="goToBerandaBtn" style="width: 100%;">
                <i class="fas fa-home"></i> Ke Beranda Sekarang
            </button>
        </div>
    </div>

    <script>
        // DOM Elements
        const cancelBtn = document.getElementById('cancelBtn');
        const logoutBtn = document.getElementById('logoutBtn');
        const countdownSection = document.getElementById('countdownSection');
        const countdownTimer = document.getElementById('countdownTimer');
        const successModal = document.getElementById('successModal');
        const goToBerandaBtn = document.getElementById('goToBerandaBtn');

        let countdownValue = 3;
        let countdownInterval = null;

        // Mulai countdown
        function startCountdown() {
            countdownSection.style.display = 'block';
            countdownValue = 3;
            countdownTimer.textContent = countdownValue;
            
            countdownInterval = setInterval(() => {
                countdownValue--;
                countdownTimer.textContent = countdownValue;
                
                if (countdownValue <= 0) {
                    clearInterval(countdownInterval);
                    completeLogout();
                }
            }, 1000);
        }

        // Tampilkan notifikasi Custom
        function showNotification(message, type) {
            const existingNotification = document.querySelector('.notification');
            if (existingNotification) existingNotification.remove();
            
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.style.cssText = `
                position: fixed; top: 20px; right: 20px; padding: 15px 20px; 
                border-radius: var(--border-radius); color: white; font-weight: 600; 
                display: flex; align-items: center; justify-content: space-between; 
                min-width: 300px; box-shadow: var(--box-shadow); z-index: 1001; 
                animation: fadeIn 0.3s ease-out;
            `;
            
            if (type === 'success') notification.style.backgroundColor = 'var(--success)';
            else if (type === 'danger') notification.style.backgroundColor = 'var(--danger)';
            else if (type === 'info') notification.style.backgroundColor = 'var(--info)';
            else notification.style.backgroundColor = 'var(--primary)';
            
            notification.innerHTML = `
                <span>${message}</span>
                <button class="close-notification" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer; margin-left: 15px;">&times;</button>
            `;
            
            document.body.appendChild(notification);
            
            notification.querySelector('.close-notification').addEventListener('click', () => notification.remove());
            setTimeout(() => { if (notification.parentNode) notification.remove(); }, 3000);
        }

        // Simulasi proses logout
        function processLogout() {
            const originalText = logoutBtn.innerHTML;
            logoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            logoutBtn.disabled = true;
            cancelBtn.disabled = true;
            
            setTimeout(() => {
                successModal.style.display = 'flex';
                startCountdown();
            }, 800);
        }

        // Selesaikan logout dan redirect via PHP (memanggil parameter ?action=do_logout)
        function completeLogout() {
            window.location.href = 'logout.php?action=do_logout';
        }

        // Event Listeners
        cancelBtn.addEventListener('click', function() {
            // Kembali ke dashboard (index.php) jika batal
            showNotification('Dibatalkan... Kembali ke Dashboard', 'info');
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 500);
        });

        logoutBtn.addEventListener('click', processLogout);

        goToBerandaBtn.addEventListener('click', function() {
            clearInterval(countdownInterval);
            completeLogout();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (successModal.style.display !== 'flex') {
                    cancelBtn.click();
                }
            }
        });

        window.addEventListener('load', function() {
            logoutBtn.focus();
        });

        logoutBtn.addEventListener('mouseenter', function() { this.classList.add('pulse'); });
        logoutBtn.addEventListener('mouseleave', function() { this.classList.remove('pulse'); });
    </script>
</body>
</html>