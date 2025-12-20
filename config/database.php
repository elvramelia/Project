<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "megatek_industrial";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Function untuk escape string
function escape($string) {
    global $conn;
    return $conn->real_escape_string($string);
}
?>