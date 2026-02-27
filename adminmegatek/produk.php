<?php
// Konfigurasi Error Reporting (Hanya untuk debugging, matikan saat live)
// Kita matikan display_errors agar error PHP tidak merusak respons JSON
ini_set('display_errors', 0); 
error_reporting(E_ALL);

// Mulai session dan koneksi database
session_start();
// Pastikan path ini benar sesuai struktur folder Anda
require_once '../config/database.php'; 

// --- HELPER FUNCTIONS ---

// Fungsi untuk mendapatkan semua produk
function getAllProducts($conn) {
    $products = [];
    $sql = "SELECT * FROM products ORDER BY created_at DESC";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    return $products;
}

// Fungsi untuk mendapatkan produk by ID
function getProductById($conn, $id) {
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Fungsi untuk tambah/edit produk
function saveProduct($conn, $data, $file = null) {
    $id = !empty($data['productId']) ? $data['productId'] : null; // Perbaikan pengambilan ID dari form
    
    // HAPUS fungsi escape() karena kita menggunakan Prepared Statement (bind_param)
    // Prepared Statement sudah aman dari SQL Injection.
    $name = $data['name'];
    $description = $data['description'];
    $category = $data['category'];
    $price = floatval($data['price']);
    $stock = intval($data['stock']);
    $status = $data['status'];
    $featured = isset($data['featured']) ? 1 : 0;
    $popular = isset($data['popular']) ? 1 : 0;
    
    // Handle upload gambar
    $image_url = $data['existing_image'] ?? '';
    
    if ($file && isset($file['productImageFile']) && $file['productImageFile']['error'] == 0) {
        $uploadResult = uploadImage($file['productImageFile']);
        if ($uploadResult['success']) {
            // Jika ada gambar baru dan ini edit, hapus gambar lama (opsional, hati-hati jika gambar default)
            if ($id && !empty($image_url) && file_exists($image_url)) {
                 // unlink($image_url); // Uncomment jika ingin menghapus gambar lama
            }
            $image_url = $uploadResult['filepath'];
        } else {
             return [
                'success' => false,
                'message' => $uploadResult['message']
            ];
        }
    }
    
    if ($id) {
        // Update produk
        $sql = "UPDATE products SET 
                name = ?, description = ?, category = ?, price = ?, 
                stock = ?, status = ?, image_url = ?, featured = ?, 
                popular = ?, updated_at = NOW() 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        // Perbaiki tipe data bind_param: d untuk double (price), i untuk integer
        $stmt->bind_param("sssdisssii", 
            $name, $description, $category, $price, $stock, 
            $status, $image_url, $featured, $popular, $id
        );
    } else {
        // Insert produk baru
        $sql = "INSERT INTO products (name, description, category, price, 
                stock, status, image_url, featured, popular, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdisssi", 
            $name, $description, $category, $price, $stock, 
            $status, $image_url, $featured, $popular
        );
    }
    
    if ($stmt->execute()) {
        return [
            'success' => true,
            'message' => $id ? 'Produk berhasil diperbarui' : 'Produk berhasil ditambahkan',
            'id' => $id ?: $stmt->insert_id
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Gagal menyimpan produk: ' . $conn->error // Mengambil error SQL asli
        ];
    }
}

// Fungsi untuk hapus produk
function deleteProduct($conn, $id) {
    // Ambil data produk untuk menghapus gambar
    $product = getProductById($conn, $id);
    if ($product && !empty($product['image_url']) && file_exists($product['image_url'])) {
        unlink($product['image_url']);
    }
    
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        return [
            'success' => true,
            'message' => 'Produk berhasil dihapus'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Gagal menghapus produk: ' . $conn->error
        ];
    }
}

// Fungsi untuk upload gambar
function uploadImage($file) {
    // Pastikan path folder upload benar dan folder sudah ada/writable
    $uploadDir = '../Project/gambar/'; 
    
    // Buat folder jika belum ada
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    // Validasi Tipe
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Format file tidak didukung (Hanya JPG, PNG, WEBP)'];
    }
    
    // Validasi Ukuran
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Ukuran file terlalu besar (maks 5MB)'];
    }
    
    // Generate nama unik agar tidak bentrok
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'product_' . time() . '_' . uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Pindahkan file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'url' => $filepath
        ];
    } else {
        return ['success' => false, 'message' => 'Gagal mengupload file ke server'];
    }
}

// --- HANDLE REQUEST AJAX ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set header agar browser tahu ini respon JSON
    header('Content-Type: application/json');
    
    try {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'save':
                $result = saveProduct($conn, $_POST, $_FILES);
                echo json_encode($result);
                exit;
                
            case 'delete':
                $id = intval($_POST['id']);
                $result = deleteProduct($conn, $id);
                echo json_encode($result);
                exit;
                
            case 'upload_image':
                if (isset($_FILES['image'])) {
                    $result = uploadImage($_FILES['image']);
                    echo json_encode($result);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Tidak ada gambar diupload']);
                }
                exit;
        }
    } catch (Exception $e) {
        // Tangkap error fatal dan kirim sebagai JSON
        echo json_encode([
            'success' => false, 
            'message' => 'Server Error: ' . $e->getMessage()
        ]);
        exit;
    }
}

// Get all products for display (HTML View)
$products = getAllProducts($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Produk - Admin Hardjadinata Karya Utama</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            /* WARNA DISESUAIKAN DENGAN LOGO HKU SEPERTI INDEX.PHP */
            --primary: #0021A5; 
            --primary-light: #1A3DBF; 
            --secondary: #333333;
            --accent: #E30613; 
            --light: #f5f5f5;
            --danger: #E30613; 
            --success: #2e7d32;
            --warning: #f57c00;
            --gray: #7f8c8d;
            --light-gray: #ecf0f1;
            --border-radius: 6px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --card-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
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
            min-height: 100vh;
        }

        .container {
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
            /* Garis bawah logo menggunakan warna aksen merah */
            border-bottom: 3px solid var(--accent);
            margin-bottom: 20px;
        }

        .logo h1 {
            font-size: 22px;
            font-weight: 700;
            color: white;
        }

        .logo h2 {
            font-size: 14px;
            font-weight: 600;
            /* Teks KARYA UTAMA diberi warna merah agar selaras dengan logo */
            color: white;
            margin-top: 5px;
        }

        .nav-menu {
            list-style: none;
            padding: 0 15px;
            margin-top: 15px;
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
            /* Tambahan efek border kiri merah untuk menu aktif */
            border-left: 4px solid var(--accent); 
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


        /* Header */
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
            background-color: var(--accent);
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
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: end;
        }

        .filter-group {
            flex: 1;
            min-width: 0;
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
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 14px;
            transition: all 0.3s;
            background-color: white;
        }

        .filter-input:focus, .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 33, 165, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            align-items: center;
        }

        /* Button Styles */
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
            font-size: 14px;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 33, 165, 0.2);
        }

        .btn-success {
            background-color: var(--success);
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
            background-color: rgba(0, 33, 165, 0.05);
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
        }

        .section-title {
            font-size: 22px;
            color: var(--primary);
            font-weight: 600;
        }

        .product-count {
            margin-right: 15px;
            color: var(--gray);
            font-size: 14px;
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .product-card {
            border: 1px solid #e0e0e0;
            border-radius: var(--border-radius);
            overflow: hidden;
            transition: all 0.3s;
            background-color: white;
            box-shadow: var(--card-shadow);
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-color: var(--primary);
        }

        .product-header {
            padding: 20px 20px 15px;
            border-bottom: 1px solid #f0f0f0;
            background-color: #f8f9fa;
        }

        .product-category {
            color: var(--primary);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .product-name {
            font-size: 18px;
            font-weight: 700;
            color: var(--secondary);
            line-height: 1.4;
        }

        .product-body {
            padding: 20px;
        }

        .product-description {
            color: var(--gray);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-price {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 20px;
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
            gap: 8px;
        }

        .stock-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .stock-high { background-color: var(--success); }
        .stock-low { background-color: var(--warning); }
        .stock-none { background-color: var(--danger); }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active { background-color: rgba(39, 174, 96, 0.15); color: var(--success); }
        .status-inactive { background-color: rgba(227, 6, 19, 0.15); color: var(--danger); }

        .product-actions {
            display: flex;
            gap: 10px;
            border-top: 1px solid #f0f0f0;
            padding-top: 20px;
        }

        .action-btn {
            flex: 1;
            padding: 10px 15px;
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

        .view-btn { background-color: #f8f9fa; color: var(--secondary); border: 1px solid #ddd; }
        .view-btn:hover { background-color: #e9ecef; }

        .edit-btn { background-color: var(--primary); color: white; }
        .edit-btn:hover { background-color: var(--primary-light); }

        .delete-btn { background-color: var(--danger); color: white; }
        .delete-btn:hover { background-color: #c0392b; }

        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            color: var(--gray);
            font-size: 14px;
            margin-top: 20px;
            border-top: 1px solid #eee;
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
            animation: fadeIn 0.3s ease-out;
        }

        .modal-content {
            background-color: white;
            width: 90%;
            max-width: 800px;
            border-radius: var(--border-radius);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease-out;
        }

        .modal-header {
            padding: 20px 25px;
            background-color: var(--primary);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title { font-size: 20px; font-weight: 600; }

        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            line-height: 1;
            transition: all 0.3s;
        }
        .close-modal:hover { transform: scale(1.1); }

        .modal-body { padding: 25px; }

        .form-group { margin-bottom: 20px; }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--secondary);
            font-size: 14px;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 15px;
            transition: all 0.3s;
            background-color: white;
        }
        
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 33, 165, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .modal-footer {
            padding: 20px 25px;
            background-color: #f8f9fa;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            border-top: 1px solid #eee;
        }

        /* File Upload Styles */
        .file-upload-container { margin-top: 10px; }
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }
        .file-input-wrapper input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }
        .file-input-label {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 2px dashed #ddd;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }
        .file-input-label:hover {
            border-color: var(--primary);
            background-color: rgba(0, 33, 165, 0.05);
        }
        .upload-icon { color: var(--primary); font-size: 20px; }
        .file-name {
            flex-grow: 1;
            color: var(--gray);
            font-size: 14px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .image-preview-container { margin-top: 15px; display: none; }
        .image-preview {
            max-width: 200px;
            max-height: 150px;
            border-radius: var(--border-radius);
            border: 1px solid #ddd;
            padding: 5px;
            background-color: white;
        }

        /* Progress Bar */
        .progress-container { display: none; margin-top: 15px; }
        .progress-bar {
            width: 100%;
            height: 8px;
            background-color: #eee;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 8px;
        }
        .progress-fill {
            height: 100%;
            background-color: var(--primary);
            width: 0%;
            transition: width 0.3s;
        }
        .progress-text {
            font-size: 12px;
            color: var(--gray);
            text-align: center;
        }

        /* Loading */
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
            color: var(--primary);
        }
        .loading i {
            font-size: 24px;
            animation: spin 1s linear infinite;
            margin-bottom: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .fade-in { animation: fadeIn 0.5s ease-out; }

        /* Price Input Style */
        .price-input-wrapper { position: relative; }
        .price-input-wrapper::before {
            content: 'Rp';
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-weight: 600;
            z-index: 1;
        }
        .price-input { padding-left: 45px !important; }

        /* Stock Badge */
        .stock-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: white;
            z-index: 1;
        }
        .stock-badge.high { background-color: var(--success); }
        .stock-badge.low { background-color: var(--warning); }
        .stock-badge.none { background-color: var(--danger); }

        /* Notification */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: var(--border-radius);
            color: white;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 300px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            z-index: 1001;
            animation: slideIn 0.3s ease-out;
        }
        .notification.success { background-color: var(--success); }
        .notification.error { background-color: var(--danger); }
        .notification.warning { background-color: var(--warning); }
        .close-notification {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            margin-left: 15px;
            line-height: 1;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .products-grid { grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); }
        }

        @media (max-width: 992px) {
            .sidebar { width: 80px; }
            .sidebar .logo h1, .sidebar .logo h2, .nav-link span { display: none; }
            .sidebar .logo { padding: 20px 10px; text-align: center; }
            .nav-link i { margin-right: 0; font-size: 20px; }
            .nav-link { justify-content: center; padding: 15px; }
            .main-content { margin-left: 80px; }
        }

        @media (max-width: 768px) {
            .main-content { padding: 15px; }
            .header { flex-direction: column; align-items: flex-start; gap: 15px; }
            .user-info { align-self: flex-end; margin-top: 10px; }
            .filter-section { grid-template-columns: 1fr; }
            .form-row { grid-template-columns: 1fr; }
            .products-grid { grid-template-columns: 1fr; }
            .section-header { flex-direction: column; align-items: flex-start; gap: 15px; }
            .filter-actions { justify-content: flex-start; }
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
        <div class="logo">
            <h1>HARDJADINATA</h1>
            <h2>KARYA UTAMA</h2>
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
            
            <li class="nav-item">
                <a href="pesanan.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Pesanan</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="pelanggan.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
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
                    <i class="fas fa-upload"></i>
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

        <main class="main-content">
            <header class="header">
            <h1>Manajemen Produk</h1>
            <div class="user-info">
                <span>Admin HKU</span>
                <div class="avatar">AM</div>
            </div>
        </header>

            <section class="filter-section fade-in">
                <div class="filter-group">
                    <label class="filter-label">Cari Produk</label>
                    <input type="text" class="filter-input" id="searchProduct" placeholder="Nama produk, kategori, atau deskripsi">
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
                        <option value="none">Habis</option>
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button class="btn btn-outline" id="resetFilterBtn">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                    <button class="btn btn-primary" id="applyFilterBtn">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </section>

            <section class="products-section fade-in">
                <div class="section-header">
                    <h2 class="section-title">Daftar Produk</h2>
                    <div>
                        <span class="product-count">
                            <i class="fas fa-box"></i> Total: <strong id="totalProducts"><?php echo count($products); ?></strong> produk
                        </span>
                        <button class="btn btn-primary" id="addProductBtn">
                            <i class="fas fa-plus"></i> Tambah Produk Baru
                        </button>
                    </div>
                </div>

                <div class="products-grid" id="productsGrid">
                    <?php if (empty($products)): ?>
                        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: var(--gray);">
                            <i class="fas fa-box-open" style="font-size: 64px; margin-bottom: 20px; opacity: 0.3;"></i>
                            <h3 style="margin-bottom: 10px; font-weight: 600;">Belum ada produk</h3>
                            <p style="margin-bottom: 20px;">Tambahkan produk pertama Anda untuk mulai menjual.</p>
                            <button class="btn btn-primary" id="addFirstProductBtn">
                                <i class="fas fa-plus"></i> Tambah Produk Pertama
                            </button>
                        </div>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <?php
                            // Tentukan badge stok
                            $stock = $product['stock'];
                            $stockBadgeClass = '';
                            $stockBadgeText = '';
                            $stockIndicatorClass = '';
                            $stockIndicatorText = '';
                            
                            if ($stock === 0) {
                                $stockBadgeClass = 'none';
                                $stockBadgeText = 'HABIS';
                                $stockIndicatorClass = 'stock-none';
                                $stockIndicatorText = 'Habis';
                            } elseif ($stock <= 5) {
                                $stockBadgeClass = 'low';
                                $stockBadgeText = 'STOK SEDIKIT';
                                $stockIndicatorClass = 'stock-low';
                                $stockIndicatorText = 'Stok Menipis';
                            } else {
                                $stockBadgeClass = 'high';
                                $stockBadgeText = 'STOK TERSEDIA';
                                $stockIndicatorClass = 'stock-high';
                                $stockIndicatorText = 'Stok Tersedia';
                            }
                            
                            // Format harga
                            $formattedPrice = 'Rp ' . number_format($product['price'], 0, ',', '.');
                            ?>
                            <div class="product-card fade-in">
                                <div class="stock-badge <?php echo $stockBadgeClass; ?>">
                                    <?php echo $stockBadgeText; ?>
                                </div>
                                
                                <div class="product-header">
                                    <div class="product-category"><?php echo htmlspecialchars($product['category']); ?></div>
                                    <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                </div>
                                
                                <div class="product-body">
                                    <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                                    
                                    <div class="product-price"><?php echo $formattedPrice; ?></div>
                                    
                                    <div class="product-meta">
                                        <div class="stock-info">
                                            <div class="stock-indicator <?php echo $stockIndicatorClass; ?>"></div>
                                            <span class="stock-text">Stok: <?php echo $stock; ?> unit (<?php echo $stockIndicatorText; ?>)</span>
                                        </div>
                                        <div class="status-badge <?php echo $product['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $product['status'] === 'active' ? 'Aktif' : 'Nonaktif'; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="product-actions">
                                        <button class="action-btn view-btn" onclick="viewProduct(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-eye"></i> Lihat
                                        </button>
                                        <button class="action-btn edit-btn" onclick="editProduct(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="action-btn delete-btn" onclick="showDeleteModal(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <footer class="footer">
                <p>&copy; 2026 Hardjadinata Karya Utama - Your Trusted Industrial Partner</p>
            </footer>
        </main>
    </div>

    <div class="modal" id="productModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Tambah Produk Baru</h3>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="loading" id="loadingIndicator">
                    <i class="fas fa-spinner"></i>
                    <p>Memproses...</p>
                </div>

                <form id="productForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="productName" class="form-label">Nama Produk *</label>
                        <input type="text" id="productName" name="name" class="form-control" 
                               placeholder="Contoh: Sparepart Pro X200" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="productCategory" class="form-label">Kategori *</label>
                            <select id="productCategory" name="category" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                <option value="Sparepart">Sparepart</option>
                                <option value="FBR Burner">FBR Burner</option>
                                <option value="Boiler">Boiler</option>
                                <option value="Valve & Instrumentation">Valve & Instrumentation</option>
                            </select>
                        </div>
                        <div class="form-group price-input-wrapper">
                            <label for="productPrice" class="form-label">Harga (Rp) *</label>
                            <input type="text" id="productPrice" class="form-control price-input" 
                                   placeholder="12.500.000" required oninput="formatPrice(this)">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="productStock" class="form-label">Stok *</label>
                            <input type="number" id="productStock" name="stock" class="form-control" 
                                   placeholder="15" required min="0">
                        </div>
                        <div class="form-group">
                            <label for="productStatus" class="form-label">Status *</label>
                            <select id="productStatus" name="status" class="form-select" required>
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Gambar Produk</label>
                        <div class="file-upload-container">
                            <div class="file-input-wrapper">
                                <div class="file-input-label" id="fileInputLabel">
                                    <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                    <span class="file-name" id="fileName">Pilih file gambar (PNG, JPG, JPEG)</span>
                                    <i class="fas fa-file-image"></i>
                                </div>
                                <input type="file" id="productImageFile" name="productImageFile" 
                                     accept=".png,.jpg,.jpeg,.webp">
                            </div>
                            
                            <div class="progress-container" id="progressContainer">
                                <div class="progress-bar">
                                    <div class="progress-fill" id="progressFill"></div>
                                </div>
                                <div class="progress-text" id="progressText">0%</div>
                            </div>
                            
                            <div class="image-preview-container" id="imagePreviewContainer">
                                <label class="preview-label">Preview:</label>
                                <img id="imagePreview" class="image-preview" src="" alt="Preview gambar">
                            </div>
                        </div>
                        <small style="color: var(--gray); font-size: 12px; display: block; margin-top: 5px;">
                            Ukuran maksimal: 5MB. Format yang didukung: PNG, JPG, JPEG, WebP
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="productDescription" class="form-label">Deskripsi Produk</label>
                        <textarea id="productDescription" name="description" class="form-control form-textarea" 
                                  rows="4" placeholder="Deskripsi lengkap produk..."></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" id="productFeatured" name="featured" value="1" style="margin-right: 10px;">
                                <span>Produk Unggulan</span>
                            </label>
                            <small style="color: var(--gray); font-size: 12px; display: block; margin-top: 5px;">
                                Produk akan ditampilkan di halaman utama
                            </small>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" id="productPopular" name="popular" value="1" style="margin-right: 10px;">
                                <span>Produk Populer</span>
                            </label>
                            <small style="color: var(--gray); font-size: 12px; display: block; margin-top: 5px;">
                                Produk akan ditampilkan di bagian populer
                            </small>
                        </div>
                    </div>
                    
                    <input type="hidden" id="productId" name="productId">
                    <input type="hidden" id="existingImage" name="existing_image">
                    <input type="hidden" name="action" value="save">
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelBtn">Batal</button>
                <button class="btn btn-primary" id="saveProductBtn">Simpan Produk</button>
            </div>
        </div>
    </div>

    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Konfirmasi Hapus</h3>
                <button class="close-modal" id="closeDeleteModal">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size: 16px; line-height: 1.6;">
                    <i class="fas fa-exclamation-triangle" style="color: var(--danger); margin-right: 10px;"></i>
                    Apakah Anda yakin ingin menghapus produk ini? Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelDeleteBtn">Batal</button>
                <button class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash"></i> Hapus Produk
                </button>
                <input type="hidden" id="deleteProductId">
            </div>
        </div>
    </div>

    <script>
        // Data dari PHP
        let products = <?php echo json_encode($products); ?>;
        let filteredProducts = [...products];
        let currentProductId = null;
        let isEditMode = false;

        // DOM Elements
        const productsGrid = document.getElementById('productsGrid');
        const totalProductsElement = document.getElementById('totalProducts');
        const productModal = document.getElementById('productModal');
        const deleteModal = document.getElementById('deleteModal');
        const addProductBtn = document.getElementById('addProductBtn');
        const addFirstProductBtn = document.getElementById('addFirstProductBtn');
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
        const fileInputLabel = document.getElementById('fileInputLabel');
        const productImageFile = document.getElementById('productImageFile');
        const fileName = document.getElementById('fileName');
        const imagePreviewContainer = document.getElementById('imagePreviewContainer');
        const imagePreview = document.getElementById('imagePreview');
        const progressContainer = document.getElementById('progressContainer');
        const progressFill = document.getElementById('progressFill');
        const progressText = document.getElementById('progressText');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const deleteProductId = document.getElementById('deleteProductId');

        // Format harga menjadi format Indonesia
        function formatPrice(input) {
            // Hapus semua karakter non-digit
            let value = input.value.replace(/\D/g, '');
            
            // Format dengan titik pemisah ribuan
            if (value.length > 0) {
                value = parseInt(value).toLocaleString('id-ID');
            }
            
            // Update nilai input
            input.value = value;
            
            // Return nilai numerik untuk database (optional usage)
            return value.replace(/\./g, '');
        }

        // Fungsi untuk mendapatkan nilai numerik dari input harga
        function getNumericPrice(priceString) {
            return parseInt(priceString.replace(/\./g, '')) || 0;
        }

        // Tentukan badge stok
        function getStockBadge(stock) {
            if (stock === 0) return { class: 'none', text: 'HABIS' };
            if (stock <= 5) return { class: 'low', text: 'STOK SEDIKIT' };
            return { class: 'high', text: 'STOK TERSEDIA' };
        }

        // Tentukan indikator stok
        function getStockIndicator(stock) {
            if (stock === 0) return { class: 'stock-none', text: 'Habis' };
            if (stock <= 5) return { class: 'stock-low', text: 'Stok Menipis' };
            return { class: 'stock-high', text: 'Stok Tersedia' };
        }

        // Format harga untuk display
        function formatRupiah(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
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
                } else if (stockFilter === 'none') {
                    matchesStock = product.stock === 0;
                }
                
                return matchesSearch && matchesCategory && matchesStock;
            });
            
            renderProducts();
        }

        // Render produk
        function renderProducts() {
            const productsGrid = document.getElementById('productsGrid');
            
            if (filteredProducts.length === 0) {
                productsGrid.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: var(--gray);">
                        <i class="fas fa-box-open" style="font-size: 64px; margin-bottom: 20px; opacity: 0.3;"></i>
                        <h3 style="margin-bottom: 10px; font-weight: 600;">Tidak ada produk ditemukan</h3>
                        <p style="margin-bottom: 20px;">Coba ubah filter pencarian atau tambahkan produk baru.</p>
                        <button class="btn btn-primary" id="addFirstProductBtnJs">
                            <i class="fas fa-plus"></i> Tambah Produk Pertama
                        </button>
                    </div>
                `;
                
                // Add event listener to the dynamically created button
                document.getElementById('addFirstProductBtnJs')?.addEventListener('click', addProduct);
                totalProductsElement.textContent = '0';
                return;
            }
            
            let html = '';
            filteredProducts.forEach(product => {
                const stockBadge = getStockBadge(product.stock);
                const stockIndicator = getStockIndicator(product.stock);
                const formattedPrice = formatRupiah(product.price);
                
                html += `
                    <div class="product-card fade-in">
                        <div class="stock-badge ${stockBadge.class}">
                            ${stockBadge.text}
                        </div>
                        
                        <div class="product-header">
                            <div class="product-category">${product.category}</div>
                            <h3 class="product-name">${product.name}</h3>
                        </div>
                        
                        <div class="product-body">
                            <p class="product-description">${product.description}</p>
                            
                            <div class="product-price">${formattedPrice}</div>
                            
                            <div class="product-meta">
                                <div class="stock-info">
                                    <div class="stock-indicator ${stockIndicator.class}"></div>
                                    <span class="stock-text">Stok: ${product.stock} unit (${stockIndicator.text})</span>
                                </div>
                                <div class="status-badge ${product.status === 'active' ? 'status-active' : 'status-inactive'}">
                                    ${product.status === 'active' ? 'Aktif' : 'Nonaktif'}
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
                    </div>
                `;
            });
            
            productsGrid.innerHTML = html;
            totalProductsElement.textContent = filteredProducts.length.toString();
        }

        // Reset filter
        function resetFilter() {
            searchProductInput.value = '';
            filterCategorySelect.value = '';
            filterStockSelect.value = '';
            filteredProducts = [...products];
            renderProducts();
        }

        // Tambah produk baru
        function addProduct() {
            isEditMode = false;
            modalTitle.textContent = 'Tambah Produk Baru';
            productForm.reset();
            document.getElementById('productId').value = '';
            document.getElementById('existingImage').value = '';
            imagePreviewContainer.style.display = 'none';
            fileName.textContent = 'Pilih file gambar (PNG, JPG, JPEG)';
            progressContainer.style.display = 'none';
            productModal.style.display = 'flex';
        }

        // Edit produk
        function editProduct(id) {
            // Kita ambil data dari array products saja agar cepat, tidak perlu fetch lagi jika data sudah ada
            const product = products.find(p => p.id == id);
            
            if (product) {
                isEditMode = true;
                modalTitle.textContent = 'Edit Produk';
                
                document.getElementById('productId').value = product.id;
                document.getElementById('productName').value = product.name;
                document.getElementById('productCategory').value = product.category;
                document.getElementById('productPrice').value = new Intl.NumberFormat('id-ID').format(product.price);
                document.getElementById('productStock').value = product.stock;
                document.getElementById('productStatus').value = product.status;
                document.getElementById('productDescription').value = product.description;
                document.getElementById('productFeatured').checked = product.featured == 1;
                document.getElementById('productPopular').checked = product.popular == 1;
                document.getElementById('existingImage').value = product.image_url || '';
                
                // Tampilkan preview gambar jika ada
                if (product.image_url) {
                    // Fix path gambar jika relatif
                    imagePreview.src = product.image_url;
                    imagePreviewContainer.style.display = 'block';
                    fileName.textContent = 'Gambar saat ini: ' + product.image_url.split('/').pop();
                } else {
                     imagePreviewContainer.style.display = 'none';
                     fileName.textContent = 'Pilih file gambar (PNG, JPG, JPEG)';
                }
                
                productModal.style.display = 'flex';
            } else {
                 showNotification('Data produk tidak ditemukan di list lokal', 'error');
            }
        }

        // View produk
        function viewProduct(id) {
            const product = products.find(p => p.id == id);
            if (product) {
                const stockIndicator = getStockIndicator(product.stock);
                
                alert(`DETAIL PRODUK:\n\n` +
                      `Nama: ${product.name}\n` +
                      `Kategori: ${product.category}\n` +
                      `Harga: ${formatRupiah(product.price)}\n` +
                      `Stok: ${product.stock} unit (${stockIndicator.text})\n` +
                      `Status: ${product.status === 'active' ? 'Aktif' : 'Tidak Aktif'}\n` +
                      `Unggulan: ${product.featured ? 'Ya' : 'Tidak'}\n` +
                      `Populer: ${product.popular ? 'Ya' : 'Tidak'}\n\n` +
                      `Deskripsi:\n${product.description}`);
            }
        }

        // Simpan produk
        async function saveProduct(e) {
            e.preventDefault(); // Mencegah reload form
            
            const id = document.getElementById('productId').value;
            const name = document.getElementById('productName').value;
            const category = document.getElementById('productCategory').value;
            const price = getNumericPrice(document.getElementById('productPrice').value);
            const stock = parseInt(document.getElementById('productStock').value);
            const status = document.getElementById('productStatus').value;
            const description = document.getElementById('productDescription').value;
            const featured = document.getElementById('productFeatured').checked ? 1 : 0;
            const popular = document.getElementById('productPopular').checked ? 1 : 0;
            const existingImage = document.getElementById('existingImage').value;
            const imageFile = productImageFile.files[0];
            
            // Validasi
            if (!name || !category || isNaN(price) || isNaN(stock)) {
                showNotification('Harap lengkapi semua field yang wajib diisi!', 'warning');
                return;
            }
            
            if (price < 0) {
                showNotification('Harga tidak boleh negatif!', 'warning');
                return;
            }
            
            if (stock < 0) {
                showNotification('Stok tidak boleh negatif!', 'warning');
                return;
            }
            
            // Tampilkan loading
            loadingIndicator.style.display = 'block';
            saveProductBtn.disabled = true;
            
            const formData = new FormData();
            formData.append('action', 'save');
            formData.append('productId', id);
            formData.append('name', name);
            formData.append('category', category);
            formData.append('price', price); // Kirim integer murni
            formData.append('stock', stock);
            formData.append('status', status);
            formData.append('description', description);
            formData.append('featured', featured);
            formData.append('popular', popular);
            formData.append('existing_image', existingImage);
            
            if (imageFile) {
                formData.append('productImageFile', imageFile);
            }
            
            try {
                const response = await fetch('produk.php', {
                    method: 'POST',
                    body: formData
                });
                
                // Parse sebagai text dulu untuk debugging jika bukan JSON valid
                const textResult = await response.text();
                
                try {
                     const result = JSON.parse(textResult);
                     if (result.success) {
                        showNotification(result.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showNotification('Gagal menyimpan produk: ' + result.message, 'error');
                         loadingIndicator.style.display = 'none';
                         saveProductBtn.disabled = false;
                    }
                } catch (e) {
                     console.error("Server Error Response:", textResult);
                     showNotification('Terjadi kesalahan server (Invalid JSON). Cek Console.', 'error');
                     loadingIndicator.style.display = 'none';
                     saveProductBtn.disabled = false;
                }

            } catch (error) {
                showNotification('Terjadi kesalahan koneksi: ' + error.message, 'error');
                loadingIndicator.style.display = 'none';
                saveProductBtn.disabled = false;
            }
        }

        // Tampilkan modal konfirmasi hapus
        function showDeleteModal(id) {
            currentProductId = id;
            deleteProductId.value = id;
            deleteModal.style.display = 'flex';
        }

        // Hapus produk
        async function deleteProduct() {
            const id = deleteProductId.value;
            
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            
            try {
                const response = await fetch('produk.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('Produk berhasil dihapus', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showNotification('Gagal menghapus produk: ' + result.message, 'error');
                    closeDeleteModal();
                }
            } catch (error) {
                showNotification('Terjadi kesalahan: ' + error.message, 'error');
                closeDeleteModal();
            }
        }

        // Tutup modal produk
        function closeProductModal() {
            productModal.style.display = 'none';
            productForm.reset();
            imagePreviewContainer.style.display = 'none';
            fileName.textContent = 'Pilih file gambar (PNG, JPG, JPEG)';
            progressContainer.style.display = 'none';
        }

        // Tutup modal hapus
        function closeDeleteModal() {
            deleteModal.style.display = 'none';
            currentProductId = null;
            deleteProductId.value = '';
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
            
            // Tombol tutup notifikasi
            const closeBtn = notification.querySelector('.close-notification');
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

        // Preview gambar
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validasi
                if (file.size > 5 * 1024 * 1024) {
                    showNotification('Ukuran file terlalu besar (maks 5MB)', 'warning');
                    input.value = '';
                    return;
                }
                
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    showNotification('Format file tidak didukung', 'warning');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreviewContainer.style.display = 'block';
                    fileName.textContent = file.name;
                };
                
                reader.readAsDataURL(file);
            }
        }

        // Event Listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Render data awal
            renderProducts();
            
            // Event listeners
            addProductBtn.addEventListener('click', addProduct);
            if (addFirstProductBtn) {
                addFirstProductBtn.addEventListener('click', addProduct);
            }
            
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
            
            // File upload
            fileInputLabel.addEventListener('click', function() {
                productImageFile.click();
            });
            
            productImageFile.addEventListener('change', function(e) {
                previewImage(this);
            });
            
            // Tutup modal jika klik di luar konten modal
            window.addEventListener('click', (e) => {
                if (e.target === productModal) {
                    closeProductModal();
                }
                if (e.target === deleteModal) {
                    closeDeleteModal();
                }
            });
            
            // Enter untuk search
            searchProductInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    filterProducts();
                }
            });
            
            // Auto format harga saat blur
            document.getElementById('productPrice').addEventListener('blur', function() {
                formatPrice(this);
            });
        });
    </script>
</body>
</html>