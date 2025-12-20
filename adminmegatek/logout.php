<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - PT Megatek Industrial Persada</title>
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
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
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
            left: 0;
            top: 0;
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

        /* Logout Container */
        .logout-container {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            padding: 40px;
        }

        .logout-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
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
            height: 5px;
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-light) 100%);
        }

        .logout-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin: 0 auto 30px;
            box-shadow: 0 8px 20px rgba(0, 64, 128, 0.2);
        }

        .logout-title {
            font-size: 28px;
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 15px;
        }

        .logout-subtitle {
            color: var(--gray);
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .user-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 40px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: var(--border-radius);
        }

        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
        }

        .user-details h3 {
            font-weight: 600;
            color: var(--secondary);
            margin-bottom: 5px;
        }

        .user-details p {
            color: var(--gray);
            font-size: 14px;
        }

        .logout-message {
            background-color: rgba(0, 64, 128, 0.05);
            border-left: 4px solid var(--primary);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 40px;
            text-align: left;
        }

        .logout-message p {
            color: var(--secondary);
            line-height: 1.6;
        }

        .logout-message i {
            color: var(--primary);
            margin-right: 10px;
        }

        .btn {
            padding: 14px 30px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
            font-size: 16px;
            min-width: 180px;
            margin: 10px;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 64, 128, 0.2);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-outline:hover {
            background-color: rgba(0, 64, 128, 0.05);
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #b71c1c;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(211, 47, 47, 0.2);
        }

        .btn-group {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .countdown {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--light-gray);
        }

        .countdown-text {
            color: var(--gray);
            font-size: 14px;
            margin-bottom: 10px;
        }

        .countdown-timer {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 30px 20px 20px;
            color: var(--gray);
            font-size: 14px;
            margin-top: 40px;
        }

        .company-info {
            margin-top: 10px;
            font-size: 13px;
            color: var(--gray);
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
        }

        @media (max-width: 768px) {
            .logout-container {
                padding: 20px;
            }
            
            .logout-card {
                padding: 40px 25px;
            }
            
            .btn-group {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 300px;
            }
            
            .user-info {
                flex-direction: column;
                text-align: center;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes slideInLeft {
            from { transform: translateX(-100px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideInRight {
            from { transform: translateX(100px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <!-- Sidebar (Simplified for logout page) -->
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
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content" style="margin-left: 260px; width: calc(100% - 260px);">
        <div class="logout-container">
            <div class="logout-card fade-in">
                <div class="logout-icon">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                
                <h1 class="logout-title">Keluar dari Sistem</h1>
                
                <p class="logout-subtitle">
                    Anda akan keluar dari Admin Panel PT Megatek Industrial Persada. Pastikan Anda telah menyimpan semua pekerjaan Anda sebelum melanjutkan.
                </p>
                
                <!-- User Info -->
                <div class="user-info">
                    <div class="avatar">AM</div>
                    <div class="user-details">
                        <h3>Admin Megatek</h3>
                        <p>Administrator Panel</p>
                        <p style="color: var(--primary); font-size: 13px; margin-top: 5px;">
                            <i class="fas fa-clock"></i> Login terakhir: <?php echo date('d F Y H:i'); ?>
                        </p>
                    </div>
                </div>
                
                <!-- Logout Message -->
                <div class="logout-message">
                    <p><i class="fas fa-info-circle"></i> Setelah logout, Anda akan diarahkan ke halaman login. Untuk mengakses kembali, Anda harus memasukkan kredensial Anda.</p>
                </div>
                
                <!-- Action Buttons -->
                <div class="btn-group">
                    <button class="btn btn-outline" id="cancelBtn">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                    </button>
                    <button class="btn btn-danger" id="logoutBtn">
                        <i class="fas fa-sign-out-alt"></i> Ya, Keluar Sekarang
                    </button>
                </div>
                
                <!-- Countdown Timer -->
                <div class="countdown" id="countdownSection" style="display: none;">
                    <p class="countdown-text">Anda akan dialihkan ke halaman login dalam:</p>
                    <div class="countdown-timer" id="countdownTimer">10</div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <p>&copy; 2025 PT Megatek Industrial Persada - Your Trusted Industrial Partner</p>
                <div class="company-info">
                    <p>Jl. Industri Raya No. 123, Jakarta 12560 | Telp: (021) 1234-5678</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Success Modal -->
    <div class="modal" id="successModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="modal-content" style="background-color: white; width: 90%; max-width: 400px; border-radius: var(--border-radius); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); overflow: hidden; text-align: center; padding: 40px 30px;">
            <div class="logout-icon" style="margin-bottom: 25px; background: linear-gradient(135deg, var(--success) 0%, #4caf50 100%);">
                <i class="fas fa-check"></i>
            </div>
            <h2 style="color: var(--success); margin-bottom: 15px; font-size: 24px;">Berhasil Logout</h2>
            <p style="color: var(--gray); margin-bottom: 30px; line-height: 1.6;">Anda telah berhasil logout dari sistem. Terima kasih telah menggunakan Admin Panel PT Megatek Industrial Persada.</p>
            <button class="btn btn-primary" id="goToLoginBtn" style="width: 100%;">
                <i class="fas fa-sign-in-alt"></i> Ke Halaman Login
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
        const goToLoginBtn = document.getElementById('goToLoginBtn');

        // Variables
        let countdownValue = 10;
        let countdownInterval = null;

        // Format waktu
        function formatTime(seconds) {
            return seconds < 10 ? `0${seconds}` : seconds;
        }

        // Mulai countdown
        function startCountdown() {
            countdownSection.style.display = 'block';
            countdownValue = 10;
            countdownTimer.textContent = formatTime(countdownValue);
            
            countdownInterval = setInterval(() => {
                countdownValue--;
                countdownTimer.textContent = formatTime(countdownValue);
                
                if (countdownValue <= 0) {
                    clearInterval(countdownInterval);
                    completeLogout();
                }
            }, 1000);
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

        // Simulasi proses logout
        function processLogout() {
            // Tampilkan animasi loading pada tombol
            const originalText = logoutBtn.innerHTML;
            logoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            logoutBtn.disabled = true;
            cancelBtn.disabled = true;
            
            // Simulasi delay untuk proses logout
            setTimeout(() => {
                // Reset tombol
                logoutBtn.innerHTML = originalText;
                logoutBtn.disabled = false;
                cancelBtn.disabled = false;
                
                // Tampilkan modal sukses
                successModal.style.display = 'flex';
                
                // Mulai countdown otomatis
                startCountdown();
                
                // Tampilkan notifikasi
                showNotification('Logout berhasil! Anda akan dialihkan ke halaman login.', 'success');
            }, 1500);
        }

        // Selesaikan logout
        function completeLogout() {
            // Simulasi redirect ke halaman login
            // Dalam implementasi nyata, ini akan mengarahkan ke login.php
            // Untuk demo, kita akan menunjukkan pesan
            successModal.style.display = 'none';
            
            // Tampilkan pesan redirect
            showNotification('Mengalihkan ke halaman login...', 'info');
            
            // Simulasi redirect setelah 1 detik
            setTimeout(() => {
                // Dalam implementasi nyata, gunakan:
                // window.location.href = 'login.php';
                
                // Untuk demo, kita refresh halaman untuk reset state
                window.location.href = 'login.php';
            }, 1000);
        }

        // Event Listeners
        cancelBtn.addEventListener('click', function() {
            // Kembali ke dashboard
            showNotification('Logout dibatalkan. Kembali ke dashboard...', 'info');
            
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 500);
        });

        logoutBtn.addEventListener('click', processLogout);

        goToLoginBtn.addEventListener('click', function() {
            // Langsung ke halaman login tanpa menunggu countdown
            clearInterval(countdownInterval);
            completeLogout();
        });

        // Handle escape key untuk cancel logout
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (successModal.style.display === 'flex') {
                    successModal.style.display = 'none';
                    clearInterval(countdownInterval);
                    showNotification('Logout dibatalkan', 'info');
                } else {
                    cancelBtn.click();
                }
            }
        });

        // Tutup modal jika klik di luar konten modal
        successModal.addEventListener('click', function(e) {
            if (e.target === successModal) {
                successModal.style.display = 'none';
                clearInterval(countdownInterval);
                showNotification('Logout dibatalkan', 'info');
            }
        });

        // Auto-focus pada tombol logout untuk aksesibilitas
        window.addEventListener('load', function() {
            logoutBtn.focus();
        });

        // Tambahkan efek visual pada tombol logout saat hover
        logoutBtn.addEventListener('mouseenter', function() {
            this.classList.add('pulse');
        });

        logoutBtn.addEventListener('mouseleave', function() {
            this.classList.remove('pulse');
        });

        // Informasi sesi pengguna
        const sessionInfo = {
            loginTime: "<?php echo date('H:i'); ?>",
            sessionDuration: "2 jam 15 menit",
            lastActivity: "<?php echo date('H:i'); ?>"
        };

        // Tampilkan informasi sesi di console (untuk debugging)
        console.log('Informasi Sesi:', sessionInfo);
    </script>
</body>
</html>