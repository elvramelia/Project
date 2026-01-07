<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Banner - PT Megatek Industrial Persada</title>
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

        /* Stats Cards */
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

        /* Upload Section */
        .upload-section {
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

        .section-subtitle {
            color: var(--gray);
            margin-bottom: 30px;
            font-size: 16px;
            line-height: 1.6;
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

        /* Upload Area */
        .upload-area {
            border: 3px dashed var(--light-gray);
            border-radius: var(--border-radius);
            padding: 40px 20px;
            text-align: center;
            transition: all 0.3s;
            margin-bottom: 30px;
            cursor: pointer;
        }

        .upload-area:hover {
            border-color: var(--primary);
            background-color: rgba(0, 64, 128, 0.02);
        }

        .upload-area.drag-over {
            border-color: var(--primary);
            background-color: rgba(0, 64, 128, 0.05);
        }

        .upload-icon {
            font-size: 60px;
            color: var(--primary-light);
            margin-bottom: 20px;
        }

        .upload-text h3 {
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 20px;
        }

        .upload-text p {
            color: var(--gray);
            margin-bottom: 15px;
        }

        .file-input {
            display: none;
        }

        .upload-btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: var(--primary);
            color: white;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .upload-btn:hover {
            background-color: var(--primary-light);
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: var(--border-radius);
            margin-top: 15px;
            display: none;
        }

        .file-icon {
            font-size: 30px;
            color: var(--primary);
        }

        .file-details h4 {
            margin-bottom: 5px;
            color: var(--secondary);
        }

        .file-details p {
            color: var(--gray);
            font-size: 14px;
        }

        /* Banner Preview */
        .banner-preview {
            margin-top: 30px;
            padding: 25px;
            background-color: #f9f9f9;
            border-radius: var(--border-radius);
            border: 1px solid var(--light-gray);
        }

        .preview-title {
            font-size: 18px;
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .preview-container {
            position: relative;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            background-color: white;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .preview-image {
            max-width: 100%;
            max-height: 400px;
            display: none;
        }

        .preview-placeholder {
            text-align: center;
            padding: 40px;
            color: var(--gray);
        }

        .preview-placeholder i {
            font-size: 50px;
            margin-bottom: 15px;
            color: var(--light-gray);
        }

        /* Banner Settings */
        .settings-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            margin-bottom: 30px;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .setting-group {
            margin-bottom: 20px;
        }

        .setting-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--secondary);
            font-size: 15px;
        }

        .setting-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: border 0.3s;
        }

        .setting-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 64, 128, 0.1);
        }

        .setting-select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 16px;
            background-color: white;
            cursor: pointer;
        }

        .setting-textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 16px;
            min-height: 100px;
            resize: vertical;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .checkbox-label {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .setting-note {
            color: var(--gray);
            font-size: 14px;
            margin-top: 5px;
            font-style: italic;
        }

        /* Active Banners */
        .banners-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            margin-bottom: 30px;
        }

        .banners-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .banner-card {
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            overflow: hidden;
            transition: all 0.3s;
        }

        .banner-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .banner-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-bottom: 1px solid var(--light-gray);
        }

        .banner-info {
            padding: 20px;
        }

        .banner-title {
            font-weight: 600;
            color: var(--secondary);
            margin-bottom: 5px;
        }

        .banner-meta {
            display: flex;
            justify-content: space-between;
            color: var(--gray);
            font-size: 14px;
            margin-bottom: 15px;
        }

        .banner-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-active {
            background-color: rgba(46, 125, 50, 0.15);
            color: var(--success);
        }

        .status-inactive {
            background-color: rgba(211, 47, 47, 0.15);
            color: var(--danger);
        }

        .banner-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .action-btn {
            padding: 8px 15px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }

        .edit-btn {
            background-color: var(--primary);
            color: white;
        }

        .delete-btn {
            background-color: var(--danger);
            color: white;
        }

        .toggle-btn {
            background-color: var(--success);
            color: white;
        }

        .action-btn:hover {
            opacity: 0.9;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 50px;
            margin-bottom: 15px;
            color: var(--light-gray);
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
            
            .settings-grid {
                grid-template-columns: 1fr;
            }
            
            .banners-grid {
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
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .banner-actions {
                flex-direction: column;
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
            max-width: 600px;
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
                <a href="laporan.php" class="nav-link">
                    <i class="fas fa-chart-bar"></i>
                    <span>Laporan</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="uploadbanner.php" class="nav-link active">
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
            <h1><i class="fa-solid fa-download"></i> Upload Banner</h1>
            <div class="user-info">
                <span>Admin Megatek</span>
                <div class="avatar">AM</div>
            </div>
        </header>

        <!-- Stats Cards -->
        <div class="stats-cards">
        </div>

        <!-- Upload Section -->
        <div class="upload-section fade-in">
            <div class="section-header">
                <h2 class="section-title">Upload Banner Baru</h2>
            </div>
            
            <p class="section-subtitle">
                Unggah banner promosi untuk ditampilkan di website. Format yang didukung: JPG, PNG, GIF, WebP. Ukuran maksimum: 5MB. Disarankan ukuran: 1200x400px untuk banner utama.
            </p>

            <!-- Upload Area -->
            <div class="upload-area" id="uploadArea">
                <div class="upload-icon">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <div class="upload-text">
                    <h3>Drag & Drop file banner Anda di sini</h3>
                    <p>atau klik untuk memilih file dari komputer</p>
                    <label for="bannerFile" class="upload-btn">
                        <i class="fa-solid fa-folder-open"></i> Pilih File
                    </label>
                    <input type="file" id="bannerFile" class="file-input" accept=".jpg,.jpeg,.png,.gif,.webp">
                </div>
                
                <!-- File Info -->
                <div class="file-info" id="fileInfo">
                    <div class="file-icon">
                        <i class="fa-solid fa-image"></i>
                    </div>
                    <div class="file-details">
                        <h4 id="fileName">nama_file.jpg</h4>
                        <p id="fileSize">0 KB</p>
                    </div>
                </div>
            </div>

            <!-- Banner Preview -->
            <div class="banner-preview">
                <div class="preview-title">
                    <i class="fa-solid fa-image"></i> Preview Banner
                </div>
                <div class="preview-container">
                    <img src="" alt="Preview Banner" class="preview-image" id="previewImage">
                    <div class="preview-placeholder" id="previewPlaceholder">
                        <i class="fa-solid fa-image"></i>
                        <p>Banner akan ditampilkan di sini setelah diunggah</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Banner Settings -->
        <div class="settings-section fade-in" style="animation-delay: 0.2s;">
            <div class="section-header">
                <h2 class="section-title">Pengaturan Banner</h2>
            </div>

            <div class="settings-grid">
                <div>
                    <div class="setting-group">
                        <label class="setting-label">Judul Banner</label>
                        <input type="text" class="setting-control" id="bannerTitle" placeholder="Masukkan judul banner...">
                        <div class="setting-note">Judul akan digunakan untuk identifikasi banner di admin panel</div>
                    </div>

                    <div class="setting-group">
                        <label class="setting-label">Link Target</label>
                        <input type="url" class="setting-control" id="bannerLink" placeholder="https://example.com/page">
                        <div class="setting-note">URL yang akan dibuka saat banner diklik (opsional)</div>
                    </div>

                    <div class="setting-group">
                        <label class="setting-label">Posisi Tampilan</label>
                        <select class="setting-select" id="bannerPosition">
                            <option value="homepage-top">Atas Halaman Utama</option>
                            <option value="homepage-middle">Tengah Halaman Utama</option>
                            <option value="homepage-bottom">Bawah Halaman Utama</option>
                            <option value="product-page">Halaman Produk</option>
                            <option value="sidebar">Sidebar</option>
                            <option value="popup">Popup</option>
                        </select>
                    </div>
                </div>

                <div>
                    <div class="setting-group">
                        <label class="setting-label">Durasi Tampil</label>
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" class="checkbox-input" id="dateRangeCheckbox">
                                <span>Tentukan rentang tanggal</span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-group" id="dateRangeGroup" style="display: none;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <label class="setting-label">Mulai Tanggal</label>
                                <input type="date" class="setting-control" id="startDate">
                            </div>
                            <div>
                                <label class="setting-label">Sampai Tanggal</label>
                                <input type="date" class="setting-control" id="endDate">
                            </div>
                        </div>
                    </div>

                    <div class="setting-group">
                        <label class="setting-label">Status Banner</label>
                        <select class="setting-select" id="bannerStatus">
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                            <option value="scheduled">Dijadwalkan</option>
                        </select>
                    </div>

                    <div class="setting-group">
                        <label class="setting-label">Prioritas</label>
                        <select class="setting-select" id="bannerPriority">
                            <option value="1">Tinggi (1)</option>
                            <option value="2" selected>Normal (2)</option>
                            <option value="3">Rendah (3)</option>
                        </select>
                        <div class="setting-note">Banner dengan prioritas tinggi akan ditampilkan terlebih dahulu</div>
                    </div>
                </div>
            </div>

            <div class="setting-group">
                <label class="setting-label">Deskripsi Banner</label>
                <textarea class="setting-textarea" id="bannerDescription" placeholder="Masukkan deskripsi banner (opsional)..."></textarea>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="settings-section fade-in" style="animation-delay: 0.3s;">
            <div style="display: flex; justify-content: flex-end; gap: 15px;">
                <button class="btn btn-outline" id="resetBtn">
                    <i class="fa-solid fa-rotate-left"></i> Reset Form
                </button>
                <button class="btn btn-primary" id="uploadBtn">
                    <i class="fa-solid fa-upload"></i> Upload Banner
                </button>
            </div>
        </div>

        <!-- Active Banners -->
        

        <!-- Footer -->
        <footer class="footer">
            <p>&copy; 2025 PT Megatek Industrial Persada - Your Trusted Industrial Partner</p>
        </footer>
    </main>

    <!-- Loading Modal -->
    <div class="modal" id="loadingModal">
        <div class="modal-content" style="max-width: 300px; text-align: center;">
            <div class="modal-body">
                <div class="spinner"></div>
                <p style="margin-top: 15px; font-weight: 600; color: var(--primary);">Mengunggah banner...</p>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Konfirmasi Hapus</h3>
                <button class="close-modal" id="closeDeleteModal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus banner ini? Tindakan ini tidak dapat dibatalkan.</p>
                <p style="margin-top: 10px; font-weight: 600;" id="deleteBannerName"></p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelDeleteBtn">Batal</button>
                <button class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fa-solid fa-trash"></i> Hapus Banner
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Banner Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Banner</h3>
                <button class="close-modal" id="closeEditModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="setting-group">
                    <label class="setting-label">Judul Banner</label>
                    <input type="text" class="setting-control" id="editBannerTitle">
                </div>

                <div class="setting-group">
                    <label class="setting-label">Link Target</label>
                    <input type="url" class="setting-control" id="editBannerLink">
                </div>

                <div class="setting-group">
                    <label class="setting-label">Status Banner</label>
                    <select class="setting-select" id="editBannerStatus">
                        <option value="active">Aktif</option>
                        <option value="inactive">Tidak Aktif</option>
                        <option value="scheduled">Dijadwalkan</option>
                    </select>
                </div>

                <div class="setting-group">
                    <label class="setting-label">Prioritas</label>
                    <select class="setting-select" id="editBannerPriority">
                        <option value="1">Tinggi (1)</option>
                        <option value="2">Normal (2)</option>
                        <option value="3">Rendah (3)</option>
                    </select>
                </div>

                <input type="hidden" id="editBannerId">
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelEditBtn">Batal</button>
                <button class="btn btn-primary" id="saveEditBtn">
                    <i class="fa-solid fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>

    <script>
        // Data banner contoh
        let banners = [
            { id: 1, title: "Promosi Sporeport", image: "https://via.placeholder.com/300x150/004080/FFFFFF?text=Sporeport+Pro", link: "/produk/sporeport", position: "homepage-top", status: "active", priority: 1, size: "450 KB", uploaded: "18 Des 2025" },
            { id: 2, title: "Boiler SteamMaster", image: "https://via.placeholder.com/300x150/0066cc/FFFFFF?text=Boiler+SteamMaster", link: "/produk/boiler", position: "homepage-middle", status: "active", priority: 2, size: "520 KB", uploaded: "15 Des 2025" },
            { id: 3, title: "FBR Burner Series", image: "https://via.placeholder.com/300x150/e6b800/000000?text=FBR+Burner", link: "/produk/burner", position: "product-page", status: "active", priority: 2, size: "380 KB", uploaded: "12 Des 2025" },
            { id: 4, title: "Valve Instrumentation", image: "https://via.placeholder.com/300x150/2e7d32/FFFFFF?text=Control+Valve", link: "/produk/valve", position: "sidebar", status: "inactive", priority: 3, size: "410 KB", uploaded: "10 Des 2025" },
            { id: 5, title: "Diskon Akhir Tahun", image: "https://via.placeholder.com/300x150/f57c00/FFFFFF?text=Diskon+30%25", link: "/promo", position: "popup", status: "scheduled", priority: 1, size: "620 KB", uploaded: "5 Des 2025" }
        ];

        // DOM Elements
        const uploadArea = document.getElementById('uploadArea');
        const bannerFile = document.getElementById('bannerFile');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const previewImage = document.getElementById('previewImage');
        const previewPlaceholder = document.getElementById('previewPlaceholder');
        const uploadBtn = document.getElementById('uploadBtn');
        const resetBtn = document.getElementById('resetBtn');
        const refreshBannersBtn = document.getElementById('refreshBannersBtn');
        const bannersGrid = document.getElementById('bannersGrid');
        const loadingModal = document.getElementById('loadingModal');
        const deleteModal = document.getElementById('deleteModal');
        const closeDeleteModal = document.getElementById('closeDeleteModal');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const deleteBannerName = document.getElementById('deleteBannerName');
        const editModal = document.getElementById('editModal');
        const closeEditModal = document.getElementById('closeEditModal');
        const cancelEditBtn = document.getElementById('cancelEditBtn');
        const saveEditBtn = document.getElementById('saveEditBtn');
        const dateRangeCheckbox = document.getElementById('dateRangeCheckbox');
        const dateRangeGroup = document.getElementById('dateRangeGroup');

        // Variabel global
        let selectedFile = null;
        let bannerToDelete = null;
        let bannerToEdit = null;

        // Format ukuran file
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Render daftar banner
        function renderBanners() {
            bannersGrid.innerHTML = '';
            
            if (banners.length === 0) {
                bannersGrid.innerHTML = `
                    <div class="empty-state">
                        <i class="fa-solid fa-images"></i>
                        <h3>Belum ada banner</h3>
                        <p>Unggah banner pertama Anda untuk memulai</p>
                    </div>
                `;
                return;
            }
            
            banners.forEach(banner => {
                const bannerCard = document.createElement('div');
                bannerCard.className = 'banner-card fade-in';
                
                const statusClass = banner.status === 'active' ? 'status-active' : 
                                  banner.status === 'inactive' ? 'status-inactive' : 'status-pending';
                const statusText = banner.status === 'active' ? 'Aktif' : 
                                 banner.status === 'inactive' ? 'Tidak Aktif' : 'Dijadwalkan';
                
                bannerCard.innerHTML = `
                    <img src="${banner.image}" alt="${banner.title}" class="banner-image">
                    <div class="banner-info">
                        <div class="banner-title">${banner.title}</div>
                        <div class="banner-meta">
                            <span>${banner.size}</span>
                            <span>${banner.uploaded}</span>
                        </div>
                        <div class="banner-status ${statusClass}">${statusText}</div>
                        <div class="banner-actions">
                            <button class="action-btn edit-btn" onclick="editBanner(${banner.id})">
                                <i class="fa-solid fa-edit"></i> Edit
                            </button>
                            <button class="action-btn ${banner.status === 'active' ? 'toggle-btn' : 'btn-success'}" onclick="toggleBannerStatus(${banner.id})">
                                <i class="fa-solid fa-power-off"></i> ${banner.status === 'active' ? 'Nonaktifkan' : 'Aktifkan'}
                            </button>
                            <button class="action-btn delete-btn" onclick="confirmDeleteBanner(${banner.id})">
                                <i class="fa-solid fa-trash"></i> Hapus
                            </button>
                        </div>
                    </div>
                `;
                
                bannersGrid.appendChild(bannerCard);
            });
        }

        // Handle file selection
        function handleFileSelect(file) {
            if (!file) return;
            
            selectedFile = file;
            
            // Update file info
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            fileInfo.style.display = 'flex';
            
            // Preview image
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewImage.style.display = 'block';
                previewPlaceholder.style.display = 'none';
            };
            reader.readAsDataURL(file);
            
            // Auto-fill title from filename
            const title = file.name.replace(/\.[^/.]+$/, "").replace(/[-_]/g, " ");
            document.getElementById('bannerTitle').value = title.charAt(0).toUpperCase() + title.slice(1);
        }

        // Handle drag and drop
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.classList.add('drag-over');
        });

        uploadArea.addEventListener('dragleave', function() {
            uploadArea.classList.remove('drag-over');
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('drag-over');
            
            if (e.dataTransfer.files.length > 0) {
                handleFileSelect(e.dataTransfer.files[0]);
            }
        });

        // Handle file input change
        bannerFile.addEventListener('change', function(e) {
            if (this.files.length > 0) {
                handleFileSelect(this.files[0]);
            }
        });

        // Click upload area to trigger file input
        uploadArea.addEventListener('click', function(e) {
            if (e.target !== bannerFile && e.target.className !== 'upload-btn') {
                bannerFile.click();
            }
        });

        // Toggle date range group
        dateRangeCheckbox.addEventListener('change', function() {
            dateRangeGroup.style.display = this.checked ? 'block' : 'none';
        });

        // Reset form
        resetBtn.addEventListener('click', function() {
            // Reset file input
            bannerFile.value = '';
            selectedFile = null;
            fileInfo.style.display = 'none';
            previewImage.style.display = 'none';
            previewPlaceholder.style.display = 'block';
            
            // Reset form fields
            document.getElementById('bannerTitle').value = '';
            document.getElementById('bannerLink').value = '';
            document.getElementById('bannerPosition').value = 'homepage-top';
            document.getElementById('dateRangeCheckbox').checked = false;
            dateRangeGroup.style.display = 'none';
            document.getElementById('startDate').value = '';
            document.getElementById('endDate').value = '';
            document.getElementById('bannerStatus').value = 'active';
            document.getElementById('bannerPriority').value = '2';
            document.getElementById('bannerDescription').value = '';
            
            showNotification('Form telah direset', 'info');
        });

        // Upload banner
        uploadBtn.addEventListener('click', function() {
            const title = document.getElementById('bannerTitle').value.trim();
            const link = document.getElementById('bannerLink').value.trim();
            const position = document.getElementById('bannerPosition').value;
            const status = document.getElementById('bannerStatus').value;
            const priority = parseInt(document.getElementById('bannerPriority').value);
            const description = document.getElementById('bannerDescription').value.trim();
            
            // Validasi
            if (!selectedFile) {
                showNotification('Silakan pilih file banner terlebih dahulu', 'danger');
                return;
            }
            
            if (!title) {
                showNotification('Silakan isi judul banner', 'danger');
                return;
            }
            
            if (selectedFile.size > 5 * 1024 * 1024) { // 5MB
                showNotification('Ukuran file terlalu besar. Maksimal 5MB', 'danger');
                return;
            }
            
            // Validasi tipe file
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(selectedFile.type)) {
                showNotification('Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP', 'danger');
                return;
            }
            
            // Tampilkan loading
            showLoading();
            
            // Simulasi proses upload
            setTimeout(() => {
                // Tambahkan banner baru ke array
                const newBanner = {
                    id: banners.length + 1,
                    title: title,
                    image: previewImage.src, // URL data dari preview
                    link: link || '#',
                    position: position,
                    status: status,
                    priority: priority,
                    size: formatFileSize(selectedFile.size),
                    uploaded: new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }),
                    description: description
                };
                
                banners.unshift(newBanner); // Tambahkan di awal
                
                // Reset form
                resetBtn.click();
                
                // Perbarui daftar banner
                renderBanners();
                
                // Sembunyikan loading
                hideLoading();
                
                // Tampilkan notifikasi sukses
                showNotification('Banner berhasil diunggah!', 'success');
                
                // Scroll ke daftar banner
                document.querySelector('.banners-section').scrollIntoView({ behavior: 'smooth' });
            }, 2000);
        });

        // Konfirmasi hapus banner
        function confirmDeleteBanner(id) {
            const banner = banners.find(b => b.id === id);
            if (banner) {
                bannerToDelete = id;
                deleteBannerName.textContent = banner.title;
                deleteModal.style.display = 'flex';
            }
        }

        // Hapus banner
        function deleteBanner() {
            banners = banners.filter(b => b.id !== bannerToDelete);
            renderBanners();
            closeDeleteModal();
            showNotification('Banner berhasil dihapus', 'danger');
            bannerToDelete = null;
        }

        // Edit banner
        function editBanner(id) {
            const banner = banners.find(b => b.id === id);
            if (banner) {
                bannerToEdit = id;
                document.getElementById('editBannerTitle').value = banner.title;
                document.getElementById('editBannerLink').value = banner.link;
                document.getElementById('editBannerStatus').value = banner.status;
                document.getElementById('editBannerPriority').value = banner.priority.toString();
                document.getElementById('editBannerId').value = banner.id;
                editModal.style.display = 'flex';
            }
        }

        // Simpan perubahan edit
        function saveEdit() {
            const title = document.getElementById('editBannerTitle').value.trim();
            const link = document.getElementById('editBannerLink').value.trim();
            const status = document.getElementById('editBannerStatus').value;
            const priority = parseInt(document.getElementById('editBannerPriority').value);
            const id = parseInt(document.getElementById('editBannerId').value);
            
            if (!title) {
                showNotification('Judul banner tidak boleh kosong', 'danger');
                return;
            }
            
            const bannerIndex = banners.findIndex(b => b.id === id);
            if (bannerIndex !== -1) {
                banners[bannerIndex].title = title;
                banners[bannerIndex].link = link || '#';
                banners[bannerIndex].status = status;
                banners[bannerIndex].priority = priority;
                
                renderBanners();
                closeEditModal();
                showNotification('Banner berhasil diperbarui', 'success');
            }
        }

        // Toggle status banner
        function toggleBannerStatus(id) {
            const bannerIndex = banners.findIndex(b => b.id === id);
            if (bannerIndex !== -1) {
                banners[bannerIndex].status = banners[bannerIndex].status === 'active' ? 'inactive' : 'active';
                renderBanners();
                showNotification(`Banner ${banners[bannerIndex].status === 'active' ? 'diaktifkan' : 'dinonaktifkan'}`, 'info');
            }
        }

        // Refresh daftar banner
        refreshBannersBtn.addEventListener('click', function() {
            // Simulasi refresh
            this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Refreshing...';
            this.disabled = true;
            
            setTimeout(() => {
                renderBanners();
                this.innerHTML = '<i class="fa-solid fa-rotate"></i> Refresh';
                this.disabled = false;
                showNotification('Daftar banner diperbarui', 'info');
            }, 1000);
        });

        // Tampilkan loading
        function showLoading() {
            loadingModal.style.display = 'flex';
        }

        // Sembunyikan loading
        function hideLoading() {
            loadingModal.style.display = 'none';
        }

        // Tutup modal delete
        function closeDeleteModalFunc() {
            deleteModal.style.display = 'none';
            bannerToDelete = null;
        }

        // Tutup modal edit
        function closeEditModalFunc() {
            editModal.style.display = 'none';
            bannerToEdit = null;
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

        // Event listeners untuk modal
        closeDeleteModal.addEventListener('click', closeDeleteModalFunc);
        cancelDeleteBtn.addEventListener('click', closeDeleteModalFunc);
        confirmDeleteBtn.addEventListener('click', deleteBanner);

        closeEditModal.addEventListener('click', closeEditModalFunc);
        cancelEditBtn.addEventListener('click', closeEditModalFunc);
        saveEditBtn.addEventListener('click', saveEdit);

        // Tutup modal jika klik di luar konten modal
        window.addEventListener('click', (e) => {
            if (e.target === loadingModal) {
                hideLoading();
            }
            if (e.target === deleteModal) {
                closeDeleteModalFunc();
            }
            if (e.target === editModal) {
                closeEditModalFunc();
            }
        });

        // Inisialisasi
        document.addEventListener('DOMContentLoaded', function() {
            // Render daftar banner awal
            renderBanners();
            
            // Set tanggal minimum untuk input date
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('startDate').min = today;
            document.getElementById('endDate').min = today;
            
            // Auto-set end date when start date changes
            document.getElementById('startDate').addEventListener('change', function() {
                document.getElementById('endDate').min = this.value;
                if (document.getElementById('endDate').value < this.value) {
                    document.getElementById('endDate').value = this.value;
                }
            });
            
            // Set default values for date inputs
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const tomorrowStr = tomorrow.toISOString().split('T')[0];
            
            document.getElementById('startDate').value = today;
            document.getElementById('endDate').value = tomorrowStr;
        });
    </script>
</body>
</html>