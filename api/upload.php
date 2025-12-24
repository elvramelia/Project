<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$response = ['success' => false, 'message' => '', 'image_url' => ''];

try {
    if($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method tidak diizinkan');
    }
    
    if(!isset($_FILES['image'])) {
        throw new Exception('Tidak ada file yang diunggah');
    }
    
    $file = $_FILES['image'];
    
    // Validasi error
    if($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error dalam mengunggah file');
    }
    
    // Validasi tipe file
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    if(!in_array($file['type'], $allowed_types)) {
        throw new Exception('Format file tidak didukung. Gunakan JPG, PNG, atau WebP.');
    }
    
    // Validasi ukuran file (maks 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB
    if($file['size'] > $max_size) {
        throw new Exception('Ukuran file terlalu besar. Maksimal 5MB.');
    }
    
    // Buat nama file unik
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'product_' . time() . '_' . uniqid() . '.' . $extension;
    $upload_dir = '../uploads/';
    
    // Pastikan folder uploads ada
    if(!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $filepath = $upload_dir . $filename;
    
    // Pindahkan file
    if(move_uploaded_file($file['tmp_name'], $filepath)) {
        $response['success'] = true;
        $response['message'] = 'File berhasil diunggah';
        $response['image_url'] = 'uploads/' . $filename;
    } else {
        throw new Exception('Gagal menyimpan file');
    }
    
} catch(Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>