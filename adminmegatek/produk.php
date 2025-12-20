<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Produk - Admin PT Megatek Industrial Persada</title>
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

        .filter-input, .filter-select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 15px;
            transition: border 0.3s;
        }

        .filter-input:focus, .filter-select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            margin-left: auto;
        }

        /* Products Section */
        .products-section {
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

        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-outline:hover {
            background-color: rgba(0, 64, 128, 0.05);
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .product-card {
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius);
            overflow: hidden;
            transition: all 0.3s;
            background-color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-light);
        }

        .product-image {
            height: 200px;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .product-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }

        .badge-new {
            background-color: var(--success);
        }

        .badge-sale {
            background-color: var(--danger);
        }

        .badge-out {
            background-color: var(--gray);
        }

        .product-info {
            padding: 20px;
        }

        .product-category {
            color: var(--primary);
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .product-name {
            font-size: 18px;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .product-description {
            color: var(--gray);
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-price {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 20px;
        }

        .stock-info {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stock-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .stock-high {
            background-color: var(--success);
        }

        .stock-low {
            background-color: var(--warning);
        }

        .stock-none {
            background-color: var(--danger);
        }

        .product-actions {
            display: flex;
            gap: 10px;
            border-top: 1px solid var(--light-gray);
            padding-top: 15px;
        }

        .action-btn {
            flex: 1;
            padding: 8px 15px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.3s;
        }

        .edit-btn {
            background-color: var(--primary);
            color: white;
        }

        .edit-btn:hover {
            background-color: var(--primary-light);
        }

        .delete-btn {
            background-color: var(--danger);
            color: white;
        }

        .delete-btn:hover {
            background-color: #c62828;
        }

        .view-btn {
            background-color: var(--secondary);
            color: white;
        }

        .view-btn:hover {
            background-color: #555;
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
            
            .products-grid {
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
                <a href="produk.php" class="nav-link active">
                    <i class="fas fa-box"></i>
                    <span>Produk</span>
                </a>
            </li>
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
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <div>
                <h1>Manajemen Produk</h1>
            </div>
            <div class="user-info">
                <span>Admin Megatek</span>
                <div class="avatar">AM</div>
            </div>
        </header>

        <!-- Filter Section -->
        <section class="filter-section fade-in">
            <div class="filter-group">
                <label class="filter-label">Cari Produk</label>
                <input type="text" class="filter-input" id="searchProduct" placeholder="Nama produk, atau kategori">
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Kategori</label>
                <select class="filter-select" id="filterCategory">
                    <option value="">Semua Kategori</option>
                    <option value="Sparepart">Sparepart</option>
                    <option value="FBR Burner">FBR Burner</option>
                    <option value="Boiler">Boiler</option>
                    <option value="Valve & Instrumentation">Valve & Instrumentation</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Status Stok</label>
                <select class="filter-select" id="filterStock">
                    <option value="">Semua Status</option>
                    <option value="high">Stok Tersedia</option>
                    <option value="low">Stok Menipis</option>
                    <option value="out">Habis</option>
                </select>
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

        <!-- Products Section -->
        <section class="products-section fade-in">
            <div class="section-header">
                <h2 class="section-title">Daftar Produk</h2>
                <div>
                    <span style="margin-right: 15px; color: var(--gray); font-size: 14px;">
                        <i class="fas fa-box"></i> Total: <strong id="totalProducts">12</strong> produk
                    </span>
                    <button class="btn btn-primary" id="addProductBtn">
                        <i class="fas fa-plus"></i> Tambah Produk Baru
                    </button>
                </div>
            </div>

            <div class="products-grid" id="productsGrid">
                <!-- Produk akan ditampilkan di sini melalui JavaScript -->
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

    <!-- Modal Tambah/Edit Produk -->
    <div class="modal" id="productModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Tambah Produk Baru</h3>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="productForm">
                    <div class="form-group">
                        <label for="productName" class="form-label">Nama Produk</label>
                        <input type="text" id="productName" class="form-control" placeholder="Contoh: Spareport Pro X200" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="productCategory" class="form-label">Kategori</label>
                            <select id="productCategory" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                <option value="Spareport">Spareport</option>
                                <option value="FBR Burner">FBR Burner</option>
                                <option value="Boiler">Boiler</option>
                                <option value="Valve & Instrumentation">Valve & Instrumentation</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="productPrice" class="form-label">Harga (Rp)</label>
                            <input type="number" id="productPrice" class="form-control" placeholder="Contoh: 12500000" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="productStock" class="form-label">Stok</label>
                            <input type="number" id="productStock" class="form-control" placeholder="Contoh: 15" required>
                        </div>
                        <div class="form-group">
                            <label for="productStatus" class="form-label">Status</label>
                            <select id="productStatus" class="form-select" required>
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="productImage" class="form-label">Gambar Produk (URL)</label>
                        <input type="text" id="productImage" class="form-control" placeholder="https://example.com/image.jpg">
                        <small style="color: var(--gray); font-size: 13px;">Kosongkan untuk menggunakan gambar default</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="productDescription" class="form-label">Deskripsi Produk</label>
                        <textarea id="productDescription" class="form-control" rows="4" placeholder="Deskripsi lengkap produk..."></textarea>
                    </div>
                    
                    <input type="hidden" id="productId">
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelBtn">Batal</button>
                <button class="btn btn-primary" id="saveProductBtn">Simpan Produk</button>
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
                <p>Apakah Anda yakin ingin menghapus produk ini? Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelDeleteBtn">Batal</button>
                <button class="btn btn-danger" id="confirmDeleteBtn">Hapus Produk</button>
            </div>
        </div>
    </div>

    <script>
        // Data produk contoh
        let products = [
            { 
                id: 1, 
                name: "Sparepart Pro X200", 
                category: "Sparepart", 
                price: 12500000, 
                stock: 15, 
                status: "active", 
                description: "Sparepart profesional dengan sensor canggih untuk pengukuran presisi",
                image: "https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80"
            },
            { 
                id: 2, 
                name: "FBR Burner Eco Series", 
                category: "FBR Burner", 
                price: 8500000, 
                stock: 8, 
                status: "active", 
                description: "Burner efisiensi tinggi untuk industri dengan konsumsi bahan bakar optimal",
                image: "https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80"
            },
            { 
                id: 3, 
                name: "Boiler SteamMaster 500", 
                category: "Boiler", 
                price: 185000000, 
                stock: 3, 
                status: "active", 
                description: "Boiler kapasitas besar untuk pabrik dengan efisiensi termal tinggi",
                image: "https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80"
            },
            { 
                id: 4, 
                name: "Control Valve AV100", 
                category: "Valve & Instrumentation", 
                price: 3500000, 
                stock: 22, 
                status: "active", 
                description: "Valve kontrol presisi untuk sistem industri dengan akurasi tinggi",
                image: "https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80"
            },
            { 
                id: 5, 
                name: "Sparepart Mini S50", 
                category: "Sparepart", 
                price: 7500000, 
                stock: 0, 
                status: "inactive", 
                description: "Sparepart portabel untuk pengukuran cepat di lapangan",
                image: "https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80"
            },
            { 
                id: 6, 
                name: "FBR Burner Heavy Duty", 
                category: "FBR Burner", 
                price: 12000000, 
                stock: 5, 
                status: "active", 
                description: "Burner untuk kebutuhan industri berat dengan daya tahan tinggi",
                image: "https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80"
            },
            { 
                id: 7, 
                name: "Pressure Valve PV300", 
                category: "Valve & Instrumentation", 
                price: 4200000, 
                stock: 18, 
                status: "active", 
                description: "Valve tekanan tinggi untuk aplikasi industri dengan safety features",
                image: "https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80"
            },
            { 
                id: 8, 
                name: "Boiler Compact 200", 
                category: "Boiler", 
                price: 95000000, 
                stock: 7, 
                status: "active", 
                description: "Boiler kompak dengan efisiensi tinggi untuk industri menengah",
                image: "https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80"
            },
            { 
                id: 9, 
                name: "Sparepart Advanced A500", 
                category: "Sparepart", 
                price: 18500000, 
                stock: 4, 
                status: "active", 
                description: "Sparepart dengan fitur canggih untuk laboratorium dan penelitian",
                image: "https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80"
            },
            { 
                id: 10, 
                name: "FBR Burner Compact", 
                category: "FBR Burner", 
                price: 6500000, 
                stock: 12, 
                status: "active", 
                description: "Burner ukuran kompak untuk aplikasi industri kecil dan menengah",
                image: "https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80"
            },
            { 
                id: 11, 
                name: "Boiler Industrial 1000", 
                category: "Boiler", 
                price: 250000000, 
                stock: 2, 
                status: "active", 
                description: "Boiler industri kapasitas sangat besar untuk pabrik skala besar",
                image: "https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80"
            },
            { 
                id: 12, 
                name: "Flow Meter FM200", 
                category: "Valve & Instrumentation", 
                price: 2800000, 
                stock: 25, 
                status: "active", 
                description: "Flow meter digital untuk pengukuran aliran cairan dengan akurasi tinggi",
                image: "https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80"
            }
        ];

        // DOM Elements
        const productsGrid = document.getElementById('productsGrid');
        const totalProductsElement = document.getElementById('totalProducts');
        const productModal = document.getElementById('productModal');
        const deleteModal = document.getElementById('deleteModal');
        const addProductBtn = document.getElementById('addProductBtn');
        const closeModalBtn = document.getElementById('closeModal');
        const closeDeleteModalBtn = document.getElementById('closeDeleteModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        const saveProductBtn = document.getElementById('saveProductBtn');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const productForm = document.getElementById('productForm');
        const modalTitle = document.getElementById('modalTitle');
        const searchProductInput = document.getElementById('searchProduct');
        const filterCategorySelect = document.getElementById('filterCategory');
        const filterStockSelect = document.getElementById('filterStock');
        const applyFilterBtn = document.getElementById('applyFilterBtn');
        const resetFilterBtn = document.getElementById('resetFilterBtn');

        let currentProductId = null;
        let isEditMode = false;
        let filteredProducts = [...products];

        // Format angka menjadi Rupiah
        function formatRupiah(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        }

        // Tentukan badge berdasarkan stok
        function getStockBadge(stock) {
            if (stock === 0) return { text: 'HABIS', class: 'badge-out' };
            if (stock <= 5) return { text: 'TERBATAS', class: 'badge-sale' };
            if (stock <= 10) return { text: 'STOK SEDIKIT', class: 'badge-sale' };
            return { text: 'BARU', class: 'badge-new' };
        }

        // Tentukan indikator stok
        function getStockIndicator(stock) {
            if (stock === 0) return { class: 'stock-none', text: 'Habis' };
            if (stock <= 5) return { class: 'stock-low', text: 'Stok Menipis' };
            return { class: 'stock-high', text: 'Stok Tersedia' };
        }

        // Render produk dalam grid
        function renderProducts(productsToRender = filteredProducts) {
            productsGrid.innerHTML = '';
            
            if (productsToRender.length === 0) {
                productsGrid.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--gray);">
                        <i class="fas fa-box-open" style="font-size: 60px; margin-bottom: 20px; opacity: 0.5;"></i>
                        <h3 style="margin-bottom: 10px;">Tidak ada produk ditemukan</h3>
                        <p>Coba ubah filter pencarian atau tambahkan produk baru.</p>
                    </div>
                `;
                totalProductsElement.textContent = '0';
                return;
            }
            
            productsToRender.forEach(product => {
                const stockBadge = getStockBadge(product.stock);
                const stockIndicator = getStockIndicator(product.stock);
                const defaultImage = 'https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80';
                
                const productCard = document.createElement('div');
                productCard.className = 'product-card fade-in';
                
                productCard.innerHTML = `
                    <div class="product-image">
                        <img src="${product.image || defaultImage}" alt="${product.name}">
                        <div class="product-badge ${stockBadge.class}">${stockBadge.text}</div>
                    </div>
                    <div class="product-info">
                        <div class="product-category">${product.category}</div>
                        <h3 class="product-name">${product.name}</h3>
                        <p class="product-description">${product.description}</p>
                        
                        <div class="product-price">${formatRupiah(product.price)}</div>
                        
                        <div class="product-meta">
                            <div class="stock-info">
                                <div class="stock-indicator ${stockIndicator.class}"></div>
                                <span>Stok: ${product.stock} unit (${stockIndicator.text})</span>
                            </div>
                            <div>
                                <span class="status ${product.status}" style="padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; background-color: ${product.status === 'active' ? 'rgba(46, 125, 50, 0.15)' : 'rgba(211, 47, 47, 0.15)'}; color: ${product.status === 'active' ? 'var(--success)' : 'var(--danger)'}">
                                    ${product.status === 'active' ? 'Aktif' : 'Nonaktif'}
                                </span>
                            </div>
                        </div>
                        
                        <div class="product-actions">
                            <button class="action-btn view-btn" onclick="viewProduct(${product.id})">
                                <i class="fas fa-eye"></i> Lihat
                            </button>
                            <button class="action-btn edit-btn" onclick="editProduct(${product.id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="action-btn delete-btn" onclick="showDeleteModal(${product.id})">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </div>
                    </div>
                `;
                
                productsGrid.appendChild(productCard);
            });
            
            totalProductsElement.textContent = productsToRender.length.toString();
        }

        // Filter produk
        function filterProducts() {
            const searchTerm = searchProductInput.value.toLowerCase();
            const categoryFilter = filterCategorySelect.value;
            const stockFilter = filterStockSelect.value;
            
            filteredProducts = products.filter(product => {
                // Filter berdasarkan pencarian
                const matchesSearch = searchTerm === '' || 
                    product.name.toLowerCase().includes(searchTerm) ||
                    product.category.toLowerCase().includes(searchTerm) ||
                    product.description.toLowerCase().includes(searchTerm);
                
                // Filter berdasarkan kategori
                const matchesCategory = categoryFilter === '' || product.category === categoryFilter;
                
                // Filter berdasarkan stok
                let matchesStock = true;
                if (stockFilter === 'high') {
                    matchesStock = product.stock > 10;
                } else if (stockFilter === 'low') {
                    matchesStock = product.stock > 0 && product.stock <= 10;
                } else if (stockFilter === 'out') {
                    matchesStock = product.stock === 0;
                }
                
                return matchesSearch && matchesCategory && matchesStock;
            });
            
            renderProducts(filteredProducts);
        }

        // Reset filter
        function resetFilter() {
            searchProductInput.value = '';
            filterCategorySelect.value = '';
            filterStockSelect.value = '';
            filteredProducts = [...products];
            renderProducts(filteredProducts);
        }

        // Tambah produk baru
        function addProduct() {
            isEditMode = false;
            modalTitle.textContent = 'Tambah Produk Baru';
            productForm.reset();
            document.getElementById('productId').value = '';
            productModal.style.display = 'flex';
        }

        // Edit produk
        function editProduct(id) {
            isEditMode = true;
            const product = products.find(p => p.id === id);
            
            if (product) {
                modalTitle.textContent = 'Edit Produk';
                document.getElementById('productId').value = product.id;
                document.getElementById('productName').value = product.name;
                document.getElementById('productCategory').value = product.category;
                document.getElementById('productPrice').value = product.price;
                document.getElementById('productStock').value = product.stock;
                document.getElementById('productStatus').value = product.status;
                document.getElementById('productImage').value = product.image || '';
                document.getElementById('productDescription').value = product.description;
                
                productModal.style.display = 'flex';
            }
        }

        // View produk
        function viewProduct(id) {
            const product = products.find(p => p.id === id);
            if (product) {
                const stockIndicator = getStockIndicator(product.stock);
                
                alert(`DETAIL PRODUK:\n\n` +
                      `Nama: ${product.name}\n` +
                      `Kategori: ${product.category}\n` +
                      `Harga: ${formatRupiah(product.price)}\n` +
                      `Stok: ${product.stock} unit (${stockIndicator.text})\n` +
                      `Status: ${product.status === 'active' ? 'Aktif' : 'Tidak Aktif'}\n\n` +
                      `Deskripsi:\n${product.description}`);
            }
        }

        // Simpan produk (tambah atau edit)
        function saveProduct() {
            const id = document.getElementById('productId').value;
            const name = document.getElementById('productName').value;
            const category = document.getElementById('productCategory').value;
            const price = parseInt(document.getElementById('productPrice').value);
            const stock = parseInt(document.getElementById('productStock').value);
            const status = document.getElementById('productStatus').value;
            const image = document.getElementById('productImage').value;
            const description = document.getElementById('productDescription').value;
            
            if (!name || !category || !price || !stock) {
                alert('Harap lengkapi semua field yang wajib diisi!');
                return;
            }
            
            if (isEditMode) {
                // Edit produk yang ada
                const index = products.findIndex(p => p.id === parseInt(id));
                if (index !== -1) {
                    products[index] = { 
                        id: parseInt(id), 
                        name, 
                        category, 
                        price, 
                        stock, 
                        status, 
                        image: image || products[index].image,
                        description 
                    };
                }
            } else {
                // Tambah produk baru
                const newId = products.length > 0 ? Math.max(...products.map(p => p.id)) + 1 : 1;
                const defaultImage = 'https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80';
                
                products.push({ 
                    id: newId, 
                    name, 
                    category, 
                    price, 
                    stock, 
                    status, 
                    image: image || defaultImage,
                    description 
                });
            }
            
            filterProducts(); // Render ulang dengan filter yang aktif
            closeProductModal();
            showNotification(isEditMode ? 'Produk berhasil diperbarui!' : 'Produk berhasil ditambahkan!', 'success');
        }

        // Tampilkan modal konfirmasi hapus
        function showDeleteModal(id) {
            currentProductId = id;
            deleteModal.style.display = 'flex';
        }

        // Hapus produk
        function deleteProduct() {
            products = products.filter(p => p.id !== currentProductId);
            filterProducts(); // Render ulang dengan filter yang aktif
            closeDeleteModal();
            showNotification('Produk berhasil dihapus!', 'danger');
        }

        // Tutup modal produk
        function closeProductModal() {
            productModal.style.display = 'none';
        }

        // Tutup modal hapus
        function closeDeleteModal() {
            deleteModal.style.display = 'none';
            currentProductId = null;
        }

        // Tampilkan notifikasi
        function showNotification(message, type) {
            // Buat elemen notifikasi
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <span>${message}</span>
                <button class="close-notification">&times;</button>
            `;
            
            // Tambahkan styling untuk notifikasi
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
            } else {
                notification.style.backgroundColor = 'var(--primary)';
            }
            
            // Tombol tutup notifikasi
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
            
            // Tambahkan ke body
            document.body.appendChild(notification);
            
            // Hapus otomatis setelah 3 detik
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 3000);
        }

        // Event Listeners
        addProductBtn.addEventListener('click', addProduct);
        closeModalBtn.addEventListener('click', closeProductModal);
        closeDeleteModalBtn.addEventListener('click', closeDeleteModal);
        cancelBtn.addEventListener('click', closeProductModal);
        cancelDeleteBtn.addEventListener('click', closeDeleteModal);
        saveProductBtn.addEventListener('click', saveProduct);
        confirmDeleteBtn.addEventListener('click', deleteProduct);
        applyFilterBtn.addEventListener('click', filterProducts);
        resetFilterBtn.addEventListener('click', resetFilter);

        // Pencarian real-time
        searchProductInput.addEventListener('input', filterProducts);
        filterCategorySelect.addEventListener('change', filterProducts);
        filterStockSelect.addEventListener('change', filterProducts);

        // Tutup modal jika klik di luar konten modal
        window.addEventListener('click', (e) => {
            if (e.target === productModal) {
                closeProductModal();
            }
            if (e.target === deleteModal) {
                closeDeleteModal();
            }
        });

        // Render data awal
        renderProducts(filteredProducts);
    </script>
</body>
</html>