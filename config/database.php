<?php
// config/database.php
$host = "localhost";
$username = "root";
$password = "";
$database = "megatek_industrial";

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Koneksi database gagal: '. $conn->connect_error
    ]));
}

// Set charset
$conn->set_charset("utf8mb4");

// Function untuk escape string
function escape($conn, $string) {
    return $conn->real_escape_string($string);
}

// Buat juga koneksi PDO untuk fleksibilitas
try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Koneksi PDO gagal: " . $e->getMessage());
}
?>