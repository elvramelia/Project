<?php
require_once 'config/database.php';
require_once 'config/check_login.php';

$response = ['success' => false, 'count' => 0];

if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $cart_count_query = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE user_id = $user_id");
    $cart_count = $cart_count_query->fetch_assoc()['total'] ?? 0;
    
    $response['success'] = true;
    $response['count'] = $cart_count;
} else {
    $response['success'] = true;
    $response['count'] = 0;
}

header('Content-Type: application/json');
echo json_encode($response);
?>