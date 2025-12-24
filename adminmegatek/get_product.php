<?php
// get_product.php
require_once '../config/database.php';

$id = $_GET['id'] ?? 0;
$id = intval($id);

if ($id > 0) {
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($product = $result->fetch_assoc()) {
        header('Content-Type: application/json');
        echo json_encode($product);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid ID']);
}
?>