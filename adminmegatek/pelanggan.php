<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pelanggan - PT Megatek Industrial Persada</title>
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

        .status.active {
            background-color: rgba(46, 125, 50, 0.15);
            color: var(--success);
        }

        .status.inactive {
            background-color: rgba(211, 47, 47, 0.15);
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
            box-shadow: 0 0 0 3px rgba(0, 64, 128, 0.1);
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
                <a href="pelanggan.php" class="nav-link active">
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
            <h1></i> Manajemen Pelanggan</h1>
            <div class="user-info">
                <span>Admin Megatek</span>
                <div class="avatar">AM</div>
            </div>
        </header>

        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="card fade-in">
                <div class="card-title">Total Pelanggan</div>
                <div class="card-value">89</div>
                <div class="card-change">+5% dari bulan lalu</div>
                <i class="fas fa-users"></i>
            </div>
            <div class="card fade-in" style="animation-delay: 0.1s;">
                <div class="card-title">Pelanggan Aktif</div>
                <div class="card-value">72</div>
                <div class="card-change">+8% dari bulan lalu</div>
                <i class="fas fa-user-check"></i>
            </div>
            <div class="card fade-in" style="animation-delay: 0.2s;">
                <div class="card-title">Pesanan Pelanggan</div>
                <div class="card-value">127</div>
                <div class="card-change">+12% dari bulan lalu</div>
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="card fade-in" style="animation-delay: 0.3s;">
                <div class="card-title">Pendapatan</div>
                <div class="card-value">Rp 1,2M</div>
                <div class="card-change">+15% dari bulan lalu</div>
                <i class="fas fa-chart-line"></i>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="search-filter fade-in">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Cari pelanggan berdasarkan nama, email, atau telepon...">
            </div>
            <div class="filter-options">
                <select class="filter-select" id="statusFilter">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Tidak Aktif</option>
                    <option value="pending">Menunggu</option>
                </select>
                <select class="filter-select" id="sortBy">
                    <option value="name">Urutkan berdasarkan Nama</option>
                    <option value="newest">Terbaru</option>
                    <option value="oldest">Terlama</option>
                    <option value="most-orders">Paling Banyak Pesanan</option>
                </select>
            </div>
        </div>

        <!-- CRUD Section -->
        <section class="crud-section fade-in" style="animation-delay: 0.4s;">
            <div class="section-header">
                <h2 class="section-title">Daftar Pelanggan</h2>
                <button class="btn btn-primary" id="addCustomerBtn">
                    <i class="fas fa-plus"></i> Tambah Pelanggan Baru
                </button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pelanggan</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Alamat</th>
                            <th>Status</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="customerTableBody">
                        <!-- Data pelanggan akan dimuat di sini melalui JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination" id="pagination">
                <!-- Pagination akan dimuat melalui JavaScript -->
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <p>&copy; 2025 PT Megatek Industrial Persada - Your Trusted Industrial Partner</p>
        </footer>
    </main>

    <!-- Modal Tambah/Edit Pelanggan -->
    <div class="modal" id="customerModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Tambah Pelanggan Baru</h3>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="customerForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName" class="form-label">Nama Depan *</label>
                            <input type="text" id="firstName" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="lastName" class="form-label">Nama Belakang *</label>
                            <input type="text" id="lastName" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" id="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="phone" class="form-label">Telepon *</label>
                            <input type="tel" id="phone" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address" class="form-label">Alamat Lengkap</label>
                        <textarea id="address" class="form-control" rows="3" placeholder="Jl. Contoh No. 123"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city" class="form-label">Kota</label>
                            <input type="text" id="city" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="province" class="form-label">Provinsi</label>
                            <input type="text" id="province" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="postalCode" class="form-label">Kode Pos</label>
                            <input type="text" id="postalCode" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="customerStatus" class="form-label">Status</label>
                            <select id="customerStatus" class="form-select" required>
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                                <option value="pending">Menunggu</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes" class="form-label">Catatan</label>
                        <textarea id="notes" class="form-control" rows="3" placeholder="Catatan tambahan tentang pelanggan"></textarea>
                    </div>
                    
                    <input type="hidden" id="customerId">
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelBtn">Batal</button>
                <button class="btn btn-primary" id="saveCustomerBtn">Simpan Pelanggan</button>
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
                <p>Apakah Anda yakin ingin menghapus pelanggan ini? Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelDeleteBtn">Batal</button>
                <button class="btn btn-danger" id="confirmDeleteBtn">Hapus Pelanggan</button>
            </div>
        </div>
    </div>

    <!-- Modal Detail Pelanggan -->
    <div class="modal" id="detailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Detail Pelanggan</h3>
                <button class="close-modal" id="closeDetailModal">&times;</button>
            </div>
            <div class="modal-body" id="detailModalBody">
                <!-- Detail pelanggan akan dimuat di sini -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="closeDetailBtn">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        // Data pelanggan contoh (berdasarkan struktur database)
        let customers = [
            { id: 1, firstName: "Dina", lastName: "Kamilia", email: "dina@gmail.com", phone: "083898387872819", address: "Jl. Merdeka No. 123", city: "Jakarta", province: "DKI Jakarta", postalCode: "10110", status: "active", notes: "Pelanggan setia sejak 2023", createdAt: "2025-12-18", totalOrders: 12 },
            { id: 2, firstName: "Ahmad", lastName: "Fauzi", email: "ahmad.fauzi@email.com", phone: "081234567890", address: "Jl. Sudirman No. 45", city: "Bandung", province: "Jawa Barat", postalCode: "40112", status: "active", notes: "Pemilik pabrik tekstil", createdAt: "2025-12-17", totalOrders: 8 },
            { id: 3, firstName: "Siti", lastName: "Nurhaliza", email: "siti.nur@email.com", phone: "082345678901", address: "Jl. Gatot Subroto No. 67", city: "Surabaya", province: "Jawa Timur", postalCode: "60264", status: "active", notes: "Manager procurement", createdAt: "2025-12-16", totalOrders: 15 },
            { id: 4, firstName: "Budi", lastName: "Santoso", email: "budi.santoso@email.com", phone: "083456789012", address: "Jl. Thamrin No. 89", city: "Medan", province: "Sumatera Utara", postalCode: "20112", status: "inactive", notes: "Sudah tidak aktif selama 6 bulan", createdAt: "2025-12-15", totalOrders: 3 },
            { id: 5, firstName: "Maya", lastName: "Indah", email: "maya.indah@email.com", phone: "084567890123", address: "Jl. Pemuda No. 12", city: "Semarang", province: "Jawa Tengah", postalCode: "50132", status: "active", notes: "Pembelian rutin setiap bulan", createdAt: "2025-12-14", totalOrders: 22 },
            { id: 6, firstName: "Rizky", lastName: "Pratama", email: "rizky.pratama@email.com", phone: "085678901234", address: "Jl. Asia Afrika No. 34", city: "Bandung", province: "Jawa Barat", postalCode: "40111", status: "pending", notes: "Baru mendaftar, belum melakukan pembelian", createdAt: "2025-12-13", totalOrders: 0 },
            { id: 7, firstName: "Linda", lastName: "Wijaya", email: "linda.wijaya@email.com", phone: "086789012345", address: "Jl. Diponegoro No. 56", city: "Yogyakarta", province: "DI Yogyakarta", postalCode: "55221", status: "active", notes: "Pelanggan korporat", createdAt: "2025-12-12", totalOrders: 18 },
            { id: 8, firstName: "Hendra", lastName: "Setiawan", email: "hendra.setiawan@email.com", phone: "087890123456", address: "Jl. Gajah Mada No. 78", city: "Jakarta", province: "DKI Jakarta", postalCode: "10130", status: "active", notes: "Owner perusahaan kontraktor", createdAt: "2025-12-11", totalOrders: 9 },
            { id: 9, firstName: "Dewi", lastName: "Lestari", email: "dewi.lestari@email.com", phone: "088901234567", address: "Jl. Hayam Wuruk No. 90", city: "Surabaya", province: "Jawa Timur", postalCode: "60272", status: "inactive", notes: "Pindah ke supplier lain", createdAt: "2025-12-10", totalOrders: 7 },
            { id: 10, firstName: "Fajar", lastName: "Nugroho", email: "fajar.nugroho@email.com", phone: "089012345678", address: "Jl. Ahmad Yani No. 23", city: "Makassar", province: "Sulawesi Selatan", postalCode: "90112", status: "active", notes: "Berminat dengan produk boiler", createdAt: "2025-12-09", totalOrders: 5 }
        ];

        // Variabel untuk pagination dan filter
        let currentPage = 1;
        let rowsPerPage = 10;
        let filteredCustomers = [...customers];
        let currentCustomerId = null;
        let isEditMode = false;

        // DOM Elements
        const customerTableBody = document.getElementById('customerTableBody');
        const customerModal = document.getElementById('customerModal');
        const deleteModal = document.getElementById('deleteModal');
        const detailModal = document.getElementById('detailModal');
        const addCustomerBtn = document.getElementById('addCustomerBtn');
        const closeModalBtn = document.getElementById('closeModal');
        const closeDeleteModalBtn = document.getElementById('closeDeleteModal');
        const closeDetailModalBtn = document.getElementById('closeDetailModal');
        const closeDetailBtn = document.getElementById('closeDetailBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        const saveCustomerBtn = document.getElementById('saveCustomerBtn');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const customerForm = document.getElementById('customerForm');
        const modalTitle = document.getElementById('modalTitle');
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const sortBy = document.getElementById('sortBy');
        const pagination = document.getElementById('pagination');

        // Format tanggal
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }

        // Dapatkan inisial nama
        function getInitials(firstName, lastName) {
            return (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();
        }

        // Dapatkan label status
        function getStatusLabel(status) {
            const statusMap = {
                'active': 'Aktif',
                'inactive': 'Tidak Aktif',
                'pending': 'Menunggu'
            };
            return statusMap[status] || status;
        }

        // Dapatkan class CSS untuk status
        function getStatusClass(status) {
            return `status ${status}`;
        }

        // Filter dan sortir pelanggan
        function filterAndSortCustomers() {
            const searchTerm = searchInput.value.toLowerCase();
            const statusValue = statusFilter.value;
            const sortValue = sortBy.value;
            
            // Filter berdasarkan pencarian
            let filtered = customers.filter(customer => {
                const fullName = `${customer.firstName} ${customer.lastName}`.toLowerCase();
                const matchesSearch = 
                    fullName.includes(searchTerm) ||
                    customer.email.toLowerCase().includes(searchTerm) ||
                    customer.phone.includes(searchTerm) ||
                    customer.address.toLowerCase().includes(searchTerm);
                
                const matchesStatus = !statusValue || customer.status === statusValue;
                
                return matchesSearch && matchesStatus;
            });
            
            // Sortir berdasarkan pilihan
            switch(sortValue) {
                case 'name':
                    filtered.sort((a, b) => {
                        const nameA = `${a.firstName} ${a.lastName}`.toLowerCase();
                        const nameB = `${b.firstName} ${b.lastName}`.toLowerCase();
                        return nameA.localeCompare(nameB);
                    });
                    break;
                case 'newest':
                    filtered.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
                    break;
                case 'oldest':
                    filtered.sort((a, b) => new Date(a.createdAt) - new Date(b.createdAt));
                    break;
                case 'most-orders':
                    filtered.sort((a, b) => b.totalOrders - a.totalOrders);
                    break;
            }
            
            filteredCustomers = filtered;
            currentPage = 1;
            renderCustomers();
        }

        // Render tabel pelanggan
        function renderCustomers() {
            customerTableBody.innerHTML = '';
            
            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = startIndex + rowsPerPage;
            const currentData = filteredCustomers.slice(startIndex, endIndex);
            
            if (currentData.length === 0) {
                customerTableBody.innerHTML = `
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: var(--gray);">
                            <i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px; display: block; color: var(--light-gray);"></i>
                            <h3>Tidak ada data pelanggan</h3>
                            <p>${searchInput.value || statusFilter.value ? 'Coba ubah filter pencarian Anda' : 'Tambahkan pelanggan baru dengan mengklik tombol "Tambah Pelanggan Baru"'}</p>
                        </td>
                    </tr>
                `;
                renderPagination();
                return;
            }
            
            currentData.forEach(customer => {
                const row = document.createElement('tr');
                
                row.innerHTML = `
                    <td>${customer.id}</td>
                    <td>
                        <div class="customer-info">
                            <div class="customer-avatar">${getInitials(customer.firstName, customer.lastName)}</div>
                            <div class="customer-details">
                                <h4>${customer.firstName} ${customer.lastName}</h4>
                                <p>ID: ${customer.id}</p>
                            </div>
                        </div>
                    </td>
                    <td>${customer.email}</td>
                    <td>${customer.phone}</td>
                    <td>${customer.address ? customer.address.substring(0, 30) + (customer.address.length > 30 ? '...' : '') : '-'}</td>
                    <td><span class="${getStatusClass(customer.status)}">${getStatusLabel(customer.status)}</span></td>
                    <td>${formatDate(customer.createdAt)}</td>
                    <td>
                        <div class="actions">
                            <button class="action-btn view-btn" onclick="viewCustomerDetail(${customer.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="action-btn edit-btn" onclick="editCustomer(${customer.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete-btn" onclick="showDeleteModal(${customer.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                
                customerTableBody.appendChild(row);
            });
            
            renderPagination();
        }

        // Render pagination
        function renderPagination() {
            pagination.innerHTML = '';
            
            const totalPages = Math.ceil(filteredCustomers.length / rowsPerPage);
            
            if (totalPages <= 1) return;
            
            // Tombol Previous
            const prevButton = document.createElement('button');
            prevButton.innerHTML = '<i class="fas fa-chevron-left"></i>';
            prevButton.disabled = currentPage === 1;
            prevButton.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderCustomers();
                }
            });
            pagination.appendChild(prevButton);
            
            // Nomor halaman
            const maxVisiblePages = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
            
            if (endPage - startPage + 1 < maxVisiblePages) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }
            
            for (let i = startPage; i <= endPage; i++) {
                const pageButton = document.createElement('button');
                pageButton.textContent = i;
                if (i === currentPage) {
                    pageButton.classList.add('active');
                }
                pageButton.addEventListener('click', () => {
                    currentPage = i;
                    renderCustomers();
                });
                pagination.appendChild(pageButton);
            }
            
            // Tombol Next
            const nextButton = document.createElement('button');
            nextButton.innerHTML = '<i class="fas fa-chevron-right"></i>';
            nextButton.disabled = currentPage === totalPages;
            nextButton.addEventListener('click', () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderCustomers();
                }
            });
            pagination.appendChild(nextButton);
        }

        // Tambah pelanggan baru
        function addCustomer() {
            isEditMode = false;
            modalTitle.textContent = 'Tambah Pelanggan Baru';
            customerForm.reset();
            document.getElementById('customerId').value = '';
            customerModal.style.display = 'flex';
        }

        // Edit pelanggan
        function editCustomer(id) {
            isEditMode = true;
            const customer = customers.find(c => c.id === id);
            
            if (customer) {
                modalTitle.textContent = 'Edit Pelanggan';
                document.getElementById('customerId').value = customer.id;
                document.getElementById('firstName').value = customer.firstName;
                document.getElementById('lastName').value = customer.lastName;
                document.getElementById('email').value = customer.email;
                document.getElementById('phone').value = customer.phone;
                document.getElementById('address').value = customer.address || '';
                document.getElementById('city').value = customer.city || '';
                document.getElementById('province').value = customer.province || '';
                document.getElementById('postalCode').value = customer.postalCode || '';
                document.getElementById('customerStatus').value = customer.status;
                document.getElementById('notes').value = customer.notes || '';
                
                customerModal.style.display = 'flex';
            }
        }

        // Lihat detail pelanggan
        function viewCustomerDetail(id) {
            const customer = customers.find(c => c.id === id);
            if (customer) {
                const detailBody = document.getElementById('detailModalBody');
                detailBody.innerHTML = `
                    <div class="customer-detail-header" style="display: flex; align-items: center; gap: 20px; margin-bottom: 25px;">
                        <div class="customer-avatar" style="width: 60px; height: 60px; font-size: 22px;">${getInitials(customer.firstName, customer.lastName)}</div>
                        <div>
                            <h3 style="color: var(--primary); margin-bottom: 5px;">${customer.firstName} ${customer.lastName}</h3>
                            <p style="color: var(--gray);">ID: ${customer.id} | Pelanggan sejak: ${formatDate(customer.createdAt)}</p>
                        </div>
                    </div>
                    
                    <div class="customer-info-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                        <div>
                            <h4 style="color: var(--secondary); margin-bottom: 10px;">Informasi Kontak</h4>
                            <p><strong>Email:</strong><br>${customer.email}</p>
                            <p><strong>Telepon:</strong><br>${customer.phone}</p>
                            <p><strong>Status:</strong> <span class="${getStatusClass(customer.status)}">${getStatusLabel(customer.status)}</span></p>
                        </div>
                        <div>
                            <h4 style="color: var(--secondary); margin-bottom: 10px;">Alamat</h4>
                            <p>${customer.address || '-'}</p>
                            <p>${customer.city || ''}${customer.province ? ', ' + customer.province : ''} ${customer.postalCode || ''}</p>
                        </div>
                    </div>
                    
                    <div class="customer-stats" style="background-color: #f9f9f9; padding: 20px; border-radius: var(--border-radius); margin-bottom: 25px;">
                        <h4 style="color: var(--secondary); margin-bottom: 15px;">Statistik Pelanggan</h4>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                            <div style="text-align: center;">
                                <div style="font-size: 24px; font-weight: 700; color: var(--primary);">${customer.totalOrders}</div>
                                <div style="color: var(--gray); font-size: 14px;">Total Pesanan</div>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-size: 24px; font-weight: 700; color: var(--success);">${customer.status === 'active' ? 'Aktif' : 'Non-Aktif'}</div>
                                <div style="color: var(--gray); font-size: 14px;">Status Akun</div>
                            </div>
                        </div>
                    </div>
                    
                    ${customer.notes ? `
                    <div>
                        <h4 style="color: var(--secondary); margin-bottom: 10px;">Catatan</h4>
                        <p style="background-color: #f5f5f5; padding: 15px; border-radius: var(--border-radius);">${customer.notes}</p>
                    </div>
                    ` : ''}
                `;
                
                detailModal.style.display = 'flex';
            }
        }

        // Simpan pelanggan (tambah atau edit)
        function saveCustomer() {
            const id = document.getElementById('customerId').value;
            const firstName = document.getElementById('firstName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const address = document.getElementById('address').value.trim();
            const city = document.getElementById('city').value.trim();
            const province = document.getElementById('province').value.trim();
            const postalCode = document.getElementById('postalCode').value.trim();
            const status = document.getElementById('customerStatus').value;
            const notes = document.getElementById('notes').value.trim();
            
            if (!firstName || !lastName || !email || !phone) {
                showNotification('Harap lengkapi semua field yang wajib diisi!', 'danger');
                return;
            }
            
            if (isEditMode) {
                // Edit pelanggan yang ada
                const index = customers.findIndex(c => c.id === parseInt(id));
                if (index !== -1) {
                    customers[index] = { 
                        ...customers[index],
                        firstName, 
                        lastName, 
                        email, 
                        phone, 
                        address: address || null, 
                        city: city || null, 
                        province: province || null, 
                        postalCode: postalCode || null, 
                        status, 
                        notes: notes || null 
                    };
                }
            } else {
                // Tambah pelanggan baru
                const newId = customers.length > 0 ? Math.max(...customers.map(c => c.id)) + 1 : 1;
                customers.push({ 
                    id: newId, 
                    firstName, 
                    lastName, 
                    email, 
                    phone, 
                    address: address || null, 
                    city: city || null, 
                    province: province || null, 
                    postalCode: postalCode || null, 
                    status, 
                    notes: notes || null,
                    createdAt: new Date().toISOString().split('T')[0],
                    totalOrders: 0
                });
            }
            
            filterAndSortCustomers();
            closeCustomerModal();
            showNotification(isEditMode ? 'Pelanggan berhasil diperbarui!' : 'Pelanggan berhasil ditambahkan!', 'success');
        }

        // Tampilkan modal konfirmasi hapus
        function showDeleteModal(id) {
            currentCustomerId = id;
            deleteModal.style.display = 'flex';
        }

        // Hapus pelanggan
        function deleteCustomer() {
            customers = customers.filter(c => c.id !== currentCustomerId);
            filterAndSortCustomers();
            closeDeleteModal();
            showNotification('Pelanggan berhasil dihapus!', 'danger');
        }

        // Tutup modal pelanggan
        function closeCustomerModal() {
            customerModal.style.display = 'none';
        }

        // Tutup modal hapus
        function closeDeleteModal() {
            deleteModal.style.display = 'none';
            currentCustomerId = null;
        }

        // Tutup modal detail
        function closeDetailModal() {
            detailModal.style.display = 'none';
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
            notification.innerHTML = `
                <span>${message}</span>
                <button class="close-notification">&times;</button>
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

        // Event Listeners
        addCustomerBtn.addEventListener('click', addCustomer);
        closeModalBtn.addEventListener('click', closeCustomerModal);
        closeDeleteModalBtn.addEventListener('click', closeDeleteModal);
        closeDetailModalBtn.addEventListener('click', closeDetailModal);
        closeDetailBtn.addEventListener('click', closeDetailModal);
        cancelBtn.addEventListener('click', closeCustomerModal);
        cancelDeleteBtn.addEventListener('click', closeDeleteModal);
        saveCustomerBtn.addEventListener('click', saveCustomer);
        confirmDeleteBtn.addEventListener('click', deleteCustomer);

        // Filter event listeners
        searchInput.addEventListener('input', filterAndSortCustomers);
        statusFilter.addEventListener('change', filterAndSortCustomers);
        sortBy.addEventListener('change', filterAndSortCustomers);

        // Tutup modal jika klik di luar konten modal
        window.addEventListener('click', (e) => {
            if (e.target === customerModal) {
                closeCustomerModal();
            }
            if (e.target === deleteModal) {
                closeDeleteModal();
            }
            if (e.target === detailModal) {
                closeDetailModal();
            }
        });

        // Render data awal
        filterAndSortCustomers();
    </script>
</body>
</html>