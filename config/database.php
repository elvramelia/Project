<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "megatek_industrial";

$conn = new mysqli($host, $username, $password, $database);


if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Koneksi database gagal: ' . $conn->connect_error
    ]));
}



// Set charset
$conn->set_charset("utf8mb4");

// Function untuk escape string
function escape($conn, $string) {
    return $conn->real_escape_string($string);
}

?>