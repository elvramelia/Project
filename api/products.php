<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

// Membuat koneksi database
$database = new Database();
$db = $database->getConnection();

$response = ['success' => false, 'message' => ''];

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch($action) {
        case 'getAll':
            $query = "SELECT * FROM products ORDER BY created_at DESC";
            $stmt = $db->prepare($query);
            $stmt->execute();
            
            $products = [];
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $products[] = $row;
            }
            
            $response['success'] = true;
            $response['products'] = $products;
            break;
            
        case 'get':
            $id = $_GET['id'] ?? '';
            if(empty($id)) {
                throw new Exception('ID produk tidak valid');
            }
            
            $query = "SELECT * FROM products WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($product) {
                $response['success'] = true;
                $response['product'] = $product;
            } else {
                $response['message'] = 'Produk tidak ditemukan';
            }
            break;
            
        case 'create':
        case 'update':
            $id = $_POST['id'] ?? '';
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $category = $_POST['category'] ?? '';
            $price = $_POST['price'] ?? 0;
            $stock = $_POST['stock'] ?? 0;
            $status = $_POST['status'] ?? 'active';
            $image_url = $_POST['image_url'] ?? '';
            $featured = $_POST['featured'] ?? 0;
            $popular = $_POST['popular'] ?? 0;
            
            if(empty($name) || empty($category) || empty($price)) {
                throw new Exception('Data tidak lengkap');
            }
            
            if($action == 'update' && !empty($id)) {
                $query = "UPDATE products SET 
                          name = ?, description = ?, category = ?, price = ?, 
                          stock = ?, status = ?, image_url = ?, featured = ?, 
                          popular = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                $success = $stmt->execute([
                    $name, $description, $category, $price, $stock, 
                    $status, $image_url, $featured, $popular, $id
                ]);
                
                if($success) {
                    $response['success'] = true;
                    $response['message'] = 'Produk berhasil diperbarui';
                    $response['id'] = $id;
                }
            } else {
                $query = "INSERT INTO products (name, description, category, price, 
                          stock, status, image_url, featured, popular, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $db->prepare($query);
                $success = $stmt->execute([
                    $name, $description, $category, $price, $stock, 
                    $status, $image_url, $featured, $popular
                ]);
                
                if($success) {
                    $response['success'] = true;
                    $response['message'] = 'Produk berhasil ditambahkan';
                    $response['id'] = $db->lastInsertId();
                }
            }
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? '';
            if(empty($id)) {
                throw new Exception('ID produk tidak valid');
            }
            
            // Hapus gambar terkait jika ada
            $query = "SELECT image_url FROM products WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($product && !empty($product['image_url'])) {
                $filepath = '../uploads/' . basename($product['image_url']);
                if(file_exists($filepath)) {
                    unlink($filepath);
                }
            }
            
            $query = "DELETE FROM products WHERE id = ?";
            $stmt = $db->prepare($query);
            $success = $stmt->execute([$id]);
            
            if($success) {
                $response['success'] = true;
                $response['message'] = 'Produk berhasil dihapus';
            }
            break;
            
        default:
            $response['message'] = 'Aksi tidak valid';
    }
    
} catch(Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>