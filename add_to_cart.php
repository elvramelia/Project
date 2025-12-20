<?php
require_once 'config/database.php';
require_once 'config/check_login.php';

$response = ['success' => false, 'message' => ''];

if (!isLoggedIn()) {
    $response['message'] = 'Silakan login terlebih dahulu';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if ($product_id <= 0 || $quantity <= 0) {
        $response['message'] = 'Data tidak valid';
        echo json_encode($response);
        exit();
    }
    
    try {
        // Check if product exists
        $product_check = $conn->prepare("SELECT id, stock FROM products WHERE id = ?");
        $product_check->bind_param("i", $product_id);
        $product_check->execute();
        $product_result = $product_check->get_result();
        
        if ($product_result->num_rows === 0) {
            $response['message'] = 'Produk tidak ditemukan';
            echo json_encode($response);
            exit();
        }
        
        $product = $product_result->fetch_assoc();
        
        // Check if product already in cart
        $cart_check = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $cart_check->bind_param("ii", $user_id, $product_id);
        $cart_check->execute();
        $cart_result = $cart_check->get_result();
        
        if ($cart_result->num_rows > 0) {
            // Update quantity
            $cart_item = $cart_result->fetch_assoc();
            $new_quantity = $cart_item['quantity'] + $quantity;
            
            if ($new_quantity > $product['stock']) {
                $response['message'] = 'Stok tidak mencukupi';
                echo json_encode($response);
                exit();
            }
            
            $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $update_stmt->bind_param("ii", $new_quantity, $cart_item['id']);
            
            if ($update_stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Produk berhasil ditambahkan ke keranjang';
            } else {
                $response['message'] = 'Gagal menambah produk';
            }
        } else {
            // Add new item to cart
            if ($quantity > $product['stock']) {
                $response['message'] = 'Stok tidak mencukupi';
                echo json_encode($response);
                exit();
            }
            
            $insert_stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("iii", $user_id, $product_id, $quantity);
            
            if ($insert_stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Produk berhasil ditambahkan ke keranjang';
            } else {
                $response['message'] = 'Gagal menambah produk';
            }
        }
    } catch (Exception $e) {
        $response['message'] = 'Terjadi kesalahan: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Metode request tidak valid';
}

header('Content-Type: application/json');
echo json_encode($response);
?>