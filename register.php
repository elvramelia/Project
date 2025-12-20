<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$email = $_POST['email'] ?? '';
$phone_number = $_POST['phone_number'] ?? '';
$password = $_POST['password'] ?? '';

// Validasi
if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Format email tidak valid']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password minimal 6 karakter']);
    exit;
}

try {
    // Cek apakah email sudah terdaftar
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar']);
        exit;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user baru
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone_number, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $first_name, $last_name, $email, $phone_number, $hashed_password);
    
    if ($stmt->execute()) {
        // Set session untuk auto login setelah registrasi
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['user_name'] = $first_name . ' ' . $last_name;
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;
        $_SESSION['user_email'] = $email;
        $_SESSION['phone_number'] = $phone_number;
        $_SESSION['logged_in'] = true;
        
        echo json_encode([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'user' => [
                'id' => $stmt->insert_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal melakukan registrasi']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}
?>