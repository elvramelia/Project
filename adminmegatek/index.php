<?php
// Sambungkan ke database
require_once '../config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - PT Megatek Industrial Persada</title>
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

        /* Table Styles */
        .table-container {
            overflow-x: auto;
            border-radius: var(--border-radius);
            border: 1px solid var(--light-gray);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
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
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
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
                <a href="index.php" class="nav-link active">
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
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <h1>Admin Dashboard</h1>
            <div class="user-info">
                <span>Admin Megatek</span>
                <div class="avatar">AM</div>
            </div>
        </header>

        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="card fade-in">
                <div class="card-title">Total Produk</div>
                <div class="card-value">48</div>
                <div class="card-change">+12% dari bulan lalu</div>
                <i class="fas fa-box"></i>
            </div>
            <div class="card fade-in" style="animation-delay: 0.1s;">
                <div class="card-title">Pesanan Bulan Ini</div>
                <div class="card-value">127</div>
                <div class="card-change">+8% dari bulan lalu</div>
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="card fade-in" style="animation-delay: 0.2s;">
                <div class="card-title">Pelanggan</div>
                <div class="card-value">89</div>
                <div class="card-change">+5% dari bulan lalu</div>
                <i class="fas fa-users"></i>
            </div>
            <div class="card fade-in" style="animation-delay: 0.3s;">
                <div class="card-title">Pendapatan</div>
                <div class="card-value">Rp 1,2M</div>
                <div class="card-change">+15% dari bulan lalu</div>
                <i class="fas fa-chart-line"></i>
            </div>
        </div>

        <!-- CRUD Section -->
        <section class="crud-section fade-in" style="animation-delay: 0.4s;">
            <div class="section-header">
                <h2 class="section-title">Manajemen Produk</h2>
                <button class="btn btn-primary" id="addProductBtn">
                    <i class="fas fa-plus"></i> Tambah Produk Baru
                </button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody">
                        <!-- Data produk akan dimuat di sini melalui JavaScript -->
                    </tbody>
                </table>
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
                        <input type="text" id="productName" class="form-control" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="productCategory" class="form-label">Kategori</label>
                            <select id="productCategory" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                <option value="Sparepart">Sporepart</option>
                                <option value="FBR Burner">FBR Burner</option>
                                <option value="Boiler">Boiler</option>
                                <option value="Valve & Instrumentation">Valve & Instrumentation</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="productPrice" class="form-label">Harga (Rp)</label>
                            <input type="number" id="productPrice" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="productStock" class="form-label">Stok</label>
                            <input type="number" id="productStock" class="form-control" required>
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
                        <label for="productDescription" class="form-label">Deskripsi Produk</label>
                        <textarea id="productDescription" class="form-control" rows="4"></textarea>
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
            { id: 1, name: "Sporeport Pro X200", category: "Sporeport", price: 12500000, stock: 15, status: "active", description: "Sporeport profesional dengan sensor canggih" },
            { id: 2, name: "FBR Burner Eco Series", category: "FBR Burner", price: 8500000, stock: 8, status: "active", description: "Burner efisiensi tinggi untuk industri" },
            { id: 3, name: "Boiler SteamMaster 500", category: "Boiler", price: 185000000, stock: 3, status: "active", description: "Boiler kapasitas besar untuk pabrik" },
            { id: 4, name: "Control Valve AV100", category: "Valve & Instrumentation", price: 3500000, stock: 22, status: "active", description: "Valve kontrol presisi untuk sistem industri" },
            { id: 5, name: "Sporeport Mini S50", category: "Sporeport", price: 7500000, stock: 0, status: "inactive", description: "Sporeport portabel untuk pengukuran cepat" },
            { id: 6, name: "FBR Burner Heavy Duty", category: "FBR Burner", price: 12000000, stock: 5, status: "active", description: "Burner untuk kebutuhan industri berat" }
        ];

        // DOM Elements
        const productTableBody = document.getElementById('productTableBody');
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

        let currentProductId = null;
        let isEditMode = false;

        // Format angka menjadi Rupiah
        function formatRupiah(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        }

        // Render tabel produk
        function renderProducts() {
            productTableBody.innerHTML = '';
            
            products.forEach(product => {
                const row = document.createElement('tr');
                
                row.innerHTML = `
                    <td>${product.id}</td>
                    <td><strong>${product.name}</strong></td>
                    <td>${product.category}</td>
                    <td>${formatRupiah(product.price)}</td>
                    <td>${product.stock}</td>
                    <td><span class="status ${product.status}">${product.status === 'active' ? 'Aktif' : 'Tidak Aktif'}</span></td>
                    <td>
                        <div class="actions">
                            <button class="action-btn view-btn" onclick="viewProduct(${product.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="action-btn edit-btn" onclick="editProduct(${product.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete-btn" onclick="showDeleteModal(${product.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                
                productTableBody.appendChild(row);
            });
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
                document.getElementById('productDescription').value = product.description;
                
                productModal.style.display = 'flex';
            }
        }

        // View produk (hanya contoh, bisa dikembangkan)
        function viewProduct(id) {
            const product = products.find(p => p.id === id);
            if (product) {
                alert(`Detail Produk:\n\nNama: ${product.name}\nKategori: ${product.category}\nHarga: ${formatRupiah(product.price)}\nStok: ${product.stock}\nStatus: ${product.status === 'active' ? 'Aktif' : 'Tidak Aktif'}\nDeskripsi: ${product.description}`);
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
                        description 
                    };
                }
            } else {
                // Tambah produk baru
                const newId = products.length > 0 ? Math.max(...products.map(p => p.id)) + 1 : 1;
                products.push({ 
                    id: newId, 
                    name, 
                    category, 
                    price, 
                    stock, 
                    status, 
                    description 
                });
            }
            
            renderProducts();
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
            renderProducts();
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
        renderProducts();
    </script>
</body>
</html>