<?php
// --- BAGIAN 1: PHP BACKEND (PENGGANTI API_PELANGGAN.PHP) ---
require_once '../config/database.php'; // Pastikan file ini ada

// Cek apakah ada request 'op' (operation) dari JavaScript
if (isset($_GET['op'])) {
    header('Content-Type: application/json');
    $op = $_GET['op'];
    $method = $_SERVER['REQUEST_METHOD'];
    // Ambil input JSON
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);

    // 1. GET DATA (READ)
    if ($op == 'list') {
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        $result = mysqli_query($conn, $sql);
        $data = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = [
                    'id' => $row['id'],
                    'firstName' => $row['first_name'],
                    'lastName' => $row['last_name'],
                    'email' => $row['email'],
                    'phone' => $row['phone_number'], // Sesuai tabel users
                    'address' => $row['address'],
                    'city' => $row['city'],
                    'province' => $row['province'],
                    'postalCode' => $row['postal_code'],
                    'status' => 'active', // Dummy status
                    'notes' => '-',       // Dummy notes
                    'createdAt' => $row['created_at']
                ];
            }
        }
        echo json_encode($data);
        exit; // PENTING: Berhenti di sini agar HTML di bawah tidak ikut terkirim
    }

    // 2. TAMBAH DATA (CREATE)
    if ($op == 'create' && $method == 'POST') {
        $firstName = $input['firstName'] ?? '';
        $lastName = $input['lastName'] ?? '';
        $email = $input['email'] ?? '';
        $phone = $input['phone'] ?? '';
        $address = $input['address'] ?? '';
        $city = $input['city'] ?? '';
        $province = $input['province'] ?? '';
        $postalCode = $input['postalCode'] ?? '';
        $password = password_hash("123456", PASSWORD_DEFAULT); // Default password

        $sql = "INSERT INTO users (first_name, last_name, email, phone_number, password, address, city, province, postal_code) 
                VALUES ('$firstName', '$lastName', '$email', '$phone', '$password', '$address', '$city', '$province', '$postalCode')";

        if (mysqli_query($conn, $sql)) {
            echo json_encode(['status' => 'success', 'message' => 'Data berhasil ditambahkan']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
        exit;
    }

    // 3. UPDATE DATA (UPDATE)
    if ($op == 'update' && $method == 'POST') { // Kita pakai POST agar lebih aman di beberapa server
        $id = $input['id'] ?? '';
        $firstName = $input['firstName'] ?? '';
        $lastName = $input['lastName'] ?? '';
        $email = $input['email'] ?? '';
        $phone = $input['phone'] ?? '';
        $address = $input['address'] ?? '';
        $city = $input['city'] ?? '';
        $province = $input['province'] ?? '';
        $postalCode = $input['postalCode'] ?? '';

        $sql = "UPDATE users SET 
                first_name='$firstName', last_name='$lastName', email='$email', phone_number='$phone', 
                address='$address', city='$city', province='$province', postal_code='$postalCode'
                WHERE id=$id";

        if (mysqli_query($conn, $sql)) {
            echo json_encode(['status' => 'success', 'message' => 'Data berhasil diperbarui']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
        exit;
    }

    // 4. HAPUS DATA (DELETE)
    if ($op == 'delete' && $method == 'POST') {
        $id = $input['id'] ?? '';
        $sql = "DELETE FROM users WHERE id=$id";

        if (mysqli_query($conn, $sql)) {
            echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
        exit;
    }
}
// --- AKHIR BAGIAN PHP BACKEND ---
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pelanggan - PT Megatek Industrial Persada</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- CSS TIDAK DIUBAH SAMA SEKALI DARI VERSI SEBELUMNYA --- */
        :root { --primary: #004080; --primary-light: #0066cc; --secondary: #333333; --accent: #e6b800; --light: #f5f5f5; --danger: #d32f2f; --success: #2e7d32; --warning: #f57c00; --info: #0288d1; --gray: #757575; --light-gray: #e0e0e0; --border-radius: 6px; --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f9f9f9; color: var(--secondary); display: flex; min-height: 100vh; }
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
        .header h1 { color: var(--primary); font-size: 28px; display: flex; align-items: center; gap: 10px; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .user-info span { font-weight: 600; color: var(--secondary); }
        .avatar { width: 40px; height: 40px; border-radius: 50%; background-color: var(--primary-light); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; }
        .stats-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .card { background: white; border-radius: var(--border-radius); padding: 20px; box-shadow: var(--box-shadow); border-left: 5px solid var(--primary); }
        .card-title { font-size: 16px; color: var(--gray); margin-bottom: 10px; }
        .card-value { font-size: 28px; font-weight: 700; color: var(--primary); margin-bottom: 5px; }
        .card-change { font-size: 14px; color: var(--success); }
        .card i { float: right; font-size: 40px; color: rgba(0, 64, 128, 0.1); margin-top: 10px; }
        .search-filter { background: white; border-radius: var(--border-radius); box-shadow: var(--box-shadow); padding: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .search-box { position: relative; flex: 1; min-width: 300px; }
        .search-box input { width: 100%; padding: 12px 15px 12px 45px; border: 1px solid var(--light-gray); border-radius: var(--border-radius); font-size: 16px; transition: border 0.3s; }
        .search-box input:focus { outline: none; border-color: var(--primary); }
        .search-box i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--gray); }
        .filter-options { display: flex; gap: 10px; flex-wrap: wrap; }
        .filter-select { padding: 10px 15px; border: 1px solid var(--light-gray); border-radius: var(--border-radius); background-color: white; color: var(--secondary); font-size: 15px; cursor: pointer; }
        .crud-section { background: white; border-radius: var(--border-radius); box-shadow: var(--box-shadow); padding: 25px; margin-bottom: 30px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .section-title { font-size: 22px; color: var(--primary); font-weight: 600; }
        .btn { padding: 10px 20px; border: none; border-radius: var(--border-radius); cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s; font-size: 15px; }
        .btn-primary { background-color: var(--primary); color: white; }
        .btn-primary:hover { background-color: var(--primary-light); transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0, 64, 128, 0.2); }
        .btn-danger { background-color: var(--danger); color: white; }
        .btn-outline { background-color: transparent; color: var(--primary); border: 1px solid var(--primary); }
        .btn-outline:hover { background-color: rgba(0, 64, 128, 0.05); }
        .table-container { overflow-x: auto; border-radius: var(--border-radius); border: 1px solid var(--light-gray); }
        table { width: 100%; border-collapse: collapse; min-width: 1000px; }
        thead { background-color: var(--primary); color: white; }
        th { padding: 16px 15px; text-align: left; font-weight: 600; font-size: 15px; }
        tbody tr { border-bottom: 1px solid var(--light-gray); transition: background-color 0.2s; }
        tbody tr:hover { background-color: rgba(0, 64, 128, 0.03); }
        td { padding: 15px; color: var(--secondary); }
        .status { padding: 5px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; display: inline-block; }
        .status.active { background-color: rgba(46, 125, 50, 0.15); color: var(--success); }
        .status.inactive { background-color: rgba(211, 47, 47, 0.15); color: var(--danger); }
        .status.pending { background-color: rgba(245, 124, 0, 0.15); color: var(--warning); }
        .customer-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px; }
        .customer-info { display: flex; align-items: center; gap: 12px; }
        .customer-details h4 { font-weight: 600; color: var(--secondary); margin-bottom: 3px; }
        .customer-details p { color: var(--gray); font-size: 13px; }
        .actions { display: flex; gap: 8px; }
        .action-btn { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s; border: none; color: white; font-size: 16px; }
        .edit-btn { background-color: var(--warning); }
        .delete-btn { background-color: var(--danger); }
        .view-btn { background-color: var(--primary); }
        .action-btn:hover { opacity: 0.9; transform: translateY(-2px); }
        .pagination { display: flex; justify-content: center; align-items: center; margin-top: 25px; gap: 8px; }
        .pagination button { padding: 10px 16px; border: 1px solid var(--light-gray); background-color: white; border-radius: var(--border-radius); cursor: pointer; transition: all 0.3s; font-weight: 500; color: var(--secondary); }
        .pagination button:hover { background-color: #f5f5f5; border-color: #ccc; }
        .pagination button.active { background-color: var(--primary); color: white; border-color: var(--primary); }
        .pagination button:disabled { opacity: 0.5; cursor: not-allowed; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal-content { background-color: white; width: 90%; max-width: 700px; border-radius: var(--border-radius); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); overflow: hidden; max-height: 90vh; overflow-y: auto; }
        .modal-header { padding: 20px 25px; background-color: var(--primary); color: white; display: flex; justify-content: space-between; align-items: center; }
        .modal-title { font-size: 20px; font-weight: 600; }
        .close-modal { background: none; border: none; color: white; font-size: 24px; cursor: pointer; line-height: 1; }
        .modal-body { padding: 25px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--secondary); }
        .form-control { width: 100%; padding: 12px 15px; border: 1px solid var(--light-gray); border-radius: var(--border-radius); font-size: 16px; transition: border 0.3s; }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(0, 64, 128, 0.1); }
        .form-select { width: 100%; padding: 12px 15px; border: 1px solid var(--light-gray); border-radius: var(--border-radius); font-size: 16px; background-color: white; cursor: pointer; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .modal-footer { padding: 20px 25px; background-color: #f9f9f9; display: flex; justify-content: flex-end; gap: 15px; border-top: 1px solid var(--light-gray); }
        /* Notification CSS sudah ada di sini */
        .notification { position: fixed; top: 20px; right: 20px; padding: 15px 20px; border-radius: var(--border-radius); color: white; font-weight: 600; display: flex; align-items: center; justify-content: space-between; min-width: 300px; box-shadow: var(--box-shadow); z-index: 1001; animation: fadeIn 0.3s ease-out; }
        .notification.success { background-color: var(--success); }
        .notification.danger { background-color: var(--danger); }
        .close-notification { background: none; border: none; color: white; font-size: 20px; cursor: pointer; margin-left: 15px; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { animation: fadeIn 0.5s ease-out; }
        .footer { text-align: center; padding: 20px; color: var(--gray); font-size: 14px; border-top: 1px solid var(--light-gray); margin-top: 20px; }
        @media (max-width: 992px) { .sidebar { width: 80px; } .sidebar .logo h1, .sidebar .logo h2, .nav-link span { display: none; } .sidebar .logo { text-align: center; padding: 20px 10px; } .nav-link i { margin-right: 0; font-size: 22px; } .nav-link { justify-content: center; padding: 15px; } .main-content { margin-left: 80px; } }
        @media (max-width: 768px) { .header, .search-filter, .section-header, .filter-options { flex-direction: column; align-items: stretch; } .user-info { align-self: flex-end; } .form-row { grid-template-columns: 1fr; } .stats-cards { grid-template-columns: 1fr; } }
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
            <li class="nav-item"><a href="pelanggan.php" class="nav-link active"><i class="fas fa-users"></i><span>Pelanggan</span></a></li>
            <li class="nav-item"><a href="laporan.php" class="nav-link"><i class="fas fa-chart-bar"></i><span>Laporan</span></a></li>
            <li class="nav-item"><a href="uploadbaner.php" class="nav-link"><i class="fa-solid fa-download"></i><span>Upload Banner</span></a></li>
            <li class="nav-item"><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="header">
            <h1>Manajemen Pelanggan (Users)</h1>
            <div class="user-info">
                <span>Admin Megatek</span>
                <div class="avatar">AM</div>
            </div>
        </header>

        <div class="stats-cards">
            <div class="card fade-in">
                <div class="card-title">Total Users</div>
                <div class="card-value" id="statTotal">0</div>
                <div class="card-change">Terdaftar di Sistem</div>
                <i class="fas fa-users"></i>
            </div>
            <div class="card fade-in" style="animation-delay: 0.1s;">
                <div class="card-title">Status Default</div>
                <div class="card-value">Aktif</div>
                <div class="card-change">Semua User</div>
                <i class="fas fa-user-check"></i>
            </div>
        </div>

        <div class="search-filter fade-in">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Cari user (nama, email, telepon)...">
            </div>
            <div class="filter-options">
                <select class="filter-select" id="sortBy">
                    <option value="newest">Terbaru</option>
                    <option value="oldest">Terlama</option>
                    <option value="name">Nama (A-Z)</option>
                </select>
            </div>
        </div>

        <section class="crud-section fade-in" style="animation-delay: 0.4s;">
            <div class="section-header">
                <h2 class="section-title">Daftar Users</h2>
                <button class="btn btn-primary" id="addCustomerBtn">
                    <i class="fas fa-plus"></i> Tambah User Baru
                </button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama User</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Alamat</th>
                            <th>Status*</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="customerTableBody">
                        </tbody>
                </table>
            </div>

            <div class="pagination" id="pagination"></div>
            <p style="margin-top: 10px; font-size: 12px; color: #888;">*Status ditampilkan default 'Active' karena tidak ada kolom status di database.</p>
        </section>

        <footer class="footer">
            <p>&copy; 2025 PT Megatek Industrial Persada</p>
        </footer>
    </main>

    <div class="modal" id="customerModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Tambah User Baru</h3>
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
                        <textarea id="address" class="form-control" rows="3"></textarea>
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
                    <div class="form-group">
                        <label for="postalCode" class="form-label">Kode Pos</label>
                        <input type="text" id="postalCode" class="form-control">
                    </div>
                    <p style="font-size: 12px; color: red;"><i>*Password otomatis di-set default: 123456</i></p>
                    <input type="hidden" id="customerId">
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelBtn">Batal</button>
                <button class="btn btn-primary" id="saveCustomerBtn">Simpan User</button>
            </div>
        </div>
    </div>

    <div class="modal" id="deleteModal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3 class="modal-title">Konfirmasi Hapus</h3>
                <button class="close-modal" id="closeDeleteModal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Yakin ingin menghapus user ini dari database?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelDeleteBtn">Batal</button>
                <button class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
            </div>
        </div>
    </div>

    <div class="modal" id="detailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Detail User</h3>
                <button class="close-modal" id="closeDetailModal">&times;</button>
            </div>
            <div class="modal-body" id="detailModalBody"></div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="closeDetailBtn">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        let customers = []; 
        let currentPage = 1;
        let rowsPerPage = 10;
        let filteredCustomers = [];
        let currentCustomerId = null;
        let isEditMode = false;

        const els = {
            tableBody: document.getElementById('customerTableBody'),
            modal: document.getElementById('customerModal'),
            deleteModal: document.getElementById('deleteModal'),
            detailModal: document.getElementById('detailModal'),
            form: document.getElementById('customerForm'),
            modalTitle: document.getElementById('modalTitle'),
            searchInput: document.getElementById('searchInput'),
            sortBy: document.getElementById('sortBy'),
            pagination: document.getElementById('pagination'),
            customerId: document.getElementById('customerId'),
            statTotal: document.getElementById('statTotal')
        };

        // --- FUNGSI NOTIFIKASI POP-UP (SUDAH ADA DI KODE ANDA) ---
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `<span>${message}</span><button class="close-notification">&times;</button>`;
            document.body.appendChild(notification);
            
            const closeBtn = notification.querySelector('.close-notification');
            closeBtn.addEventListener('click', () => notification.remove());
            
            setTimeout(() => notification.remove(), 3000);
        }
        // ---------------------------------------------------------

        async function loadCustomers() {
            try {
                const response = await fetch('pelanggan.php?op=list');
                customers = await response.json();
                els.statTotal.textContent = customers.length;
                filterAndSortCustomers();
            } catch (error) {
                console.error('Error:', error);
                // Ganti alert dengan notifikasi error
                showNotification('Gagal memuat data dari tabel users.', 'danger');
            }
        }

        async function saveCustomer() {
            const data = {
                firstName: document.getElementById('firstName').value.trim(),
                lastName: document.getElementById('lastName').value.trim(),
                email: document.getElementById('email').value.trim(),
                phone: document.getElementById('phone').value.trim(),
                address: document.getElementById('address').value.trim(),
                city: document.getElementById('city').value.trim(),
                province: document.getElementById('province').value.trim(),
                postalCode: document.getElementById('postalCode').value.trim()
            };

            if (!data.firstName || !data.lastName || !data.email || !data.phone) {
                // Ganti alert dengan notifikasi error
                showNotification('Nama dan Kontak wajib diisi!', 'danger');
                return;
            }

            const operation = isEditMode ? 'update' : 'create';
            if (isEditMode) data.id = els.customerId.value;

            try {
                const response = await fetch(`pelanggan.php?op=${operation}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.status === 'success') {
                    // GANTI ALERT DENGAN NOTIFIKASI SUKSES
                    showNotification('Berhasil menyimpan data!', 'success');
                    closeAllModals();
                    loadCustomers();
                } else {
                    // Ganti alert dengan notifikasi error
                    showNotification('Error: ' + result.message, 'danger');
                }
            } catch (error) {
                // Ganti alert dengan notifikasi error
                showNotification('Terjadi kesalahan server', 'danger');
            }
        }

        async function deleteCustomer() {
            if (!currentCustomerId) return;
            try {
                const response = await fetch('pelanggan.php?op=delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: currentCustomerId })
                });
                const result = await response.json();
                if (result.status === 'success') {
                    // GANTI ALERT DENGAN NOTIFIKASI SUKSES
                    showNotification('Data berhasil dihapus!', 'success');
                    closeAllModals();
                    loadCustomers();
                } else {
                    // Ganti alert dengan notifikasi error
                    showNotification('Gagal menghapus: ' + result.message, 'danger');
                }
            } catch (error) {
                // Ganti alert dengan notifikasi error
                showNotification('Terjadi kesalahan server', 'danger');
            }
        }

        function filterAndSortCustomers() {
            const term = els.searchInput.value.toLowerCase();
            const sort = els.sortBy.value;

            filteredCustomers = customers.filter(c => {
                const fullName = `${c.firstName} ${c.lastName}`.toLowerCase();
                return fullName.includes(term) || c.email.toLowerCase().includes(term) || c.phone.includes(term);
            });

            if (sort === 'name') {
                filteredCustomers.sort((a, b) => (`${a.firstName} ${a.lastName}`).localeCompare(`${b.firstName} ${b.lastName}`));
            } else if (sort === 'newest') {
                filteredCustomers.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
            } else if (sort === 'oldest') {
                filteredCustomers.sort((a, b) => new Date(a.createdAt) - new Date(b.createdAt));
            }

            currentPage = 1;
            renderCustomers();
        }

        function renderCustomers() {
            els.tableBody.innerHTML = '';
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            const data = filteredCustomers.slice(start, end);

            if (data.length === 0) {
                els.tableBody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding: 30px; color: #757575;">Tidak ada data ditemukan</td></tr>`;
                els.pagination.innerHTML = '';
                return;
            }

            data.forEach(c => {
                const initials = (c.firstName[0] + c.lastName[0]).toUpperCase();
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${c.id}</td>
                    <td>
                        <div class="customer-info">
                            <div class="customer-avatar">${initials}</div>
                            <div class="customer-details">
                                <h4>${c.firstName} ${c.lastName}</h4>
                                <p>ID: ${c.id}</p>
                            </div>
                        </div>
                    </td>
                    <td>${c.email}</td>
                    <td>${c.phone}</td>
                    <td>${c.address ? c.address.substring(0, 20) + '...' : '-'}</td>
                    <td><span class="status active">Active</span></td>
                    <td>${formatDate(c.createdAt)}</td>
                    <td>
                        <div class="actions">
                            <button class="action-btn view-btn" onclick="openDetail(${c.id})"><i class="fas fa-eye"></i></button>
                            <button class="action-btn edit-btn" onclick="openEdit(${c.id})"><i class="fas fa-edit"></i></button>
                            <button class="action-btn delete-btn" onclick="openDelete(${c.id})"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                `;
                els.tableBody.appendChild(row);
            });
            renderPagination();
        }

        function renderPagination() {
            els.pagination.innerHTML = '';
            const totalPages = Math.ceil(filteredCustomers.length / rowsPerPage);
            if (totalPages <= 1) return;
            for(let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.innerText = i;
                if (i === currentPage) btn.classList.add('active');
                btn.onclick = () => { currentPage = i; renderCustomers(); };
                els.pagination.appendChild(btn);
            }
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        }

        function openEdit(id) {
            const c = customers.find(x => x.id == id);
            if (!c) return;
            isEditMode = true;
            els.modalTitle.textContent = 'Edit User';
            els.customerId.value = c.id;
            document.getElementById('firstName').value = c.firstName;
            document.getElementById('lastName').value = c.lastName;
            document.getElementById('email').value = c.email;
            document.getElementById('phone').value = c.phone;
            document.getElementById('address').value = c.address || '';
            document.getElementById('city').value = c.city || '';
            document.getElementById('province').value = c.province || '';
            document.getElementById('postalCode').value = c.postalCode || '';
            els.modal.style.display = 'flex';
        }

        function openDetail(id) {
            const c = customers.find(x => x.id == id);
            if(!c) return;
            const initials = (c.firstName[0] + c.lastName[0]).toUpperCase();
            document.getElementById('detailModalBody').innerHTML = `
                <div style="display:flex; gap:15px; align-items:center; margin-bottom:20px;">
                    <div class="customer-avatar" style="width:60px; height:60px; font-size:24px;">${initials}</div>
                    <div>
                        <h3 style="color:var(--primary);">${c.firstName} ${c.lastName}</h3>
                        <p>ID: ${c.id} | Terdaftar: ${formatDate(c.createdAt)}</p>
                    </div>
                </div>
                <div class="form-row" style="margin-bottom:15px;">
                    <div><strong>Email:</strong><br>${c.email}</div>
                    <div><strong>Telepon:</strong><br>${c.phone}</div>
                </div>
                <div style="margin-bottom:15px;">
                    <strong>Alamat:</strong><br>
                    ${c.address || '-'}, ${c.city || ''}, ${c.province || ''} ${c.postalCode || ''}
                </div>
            `;
            els.detailModal.style.display = 'flex';
        }

        function openDelete(id) {
            currentCustomerId = id;
            els.deleteModal.style.display = 'flex';
        }

        function closeAllModals() {
            els.modal.style.display = 'none';
            els.deleteModal.style.display = 'none';
            els.detailModal.style.display = 'none';
        }

        document.getElementById('addCustomerBtn').onclick = () => {
            isEditMode = false;
            els.modalTitle.textContent = 'Tambah User Baru';
            els.form.reset();
            els.customerId.value = '';
            els.modal.style.display = 'flex';
        };
        document.getElementById('saveCustomerBtn').onclick = saveCustomer;
        document.getElementById('confirmDeleteBtn').onclick = deleteCustomer;
        document.getElementById('closeModal').onclick = closeAllModals;
        document.getElementById('cancelBtn').onclick = closeAllModals;
        document.getElementById('closeDeleteModal').onclick = closeAllModals;
        document.getElementById('cancelDeleteBtn').onclick = closeAllModals;
        document.getElementById('closeDetailModal').onclick = closeAllModals;
        document.getElementById('closeDetailBtn').onclick = closeAllModals;
        els.searchInput.addEventListener('input', filterAndSortCustomers);
        els.sortBy.addEventListener('change', filterAndSortCustomers);
        window.onclick = (e) => { if (e.target.classList.contains('modal')) closeAllModals(); };

        loadCustomers();
    </script>
</body>
</html>