<?php
session_start();
require_once 'config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Mencegah SQL Injection
    $email = $conn->real_escape_string($email);

    // Cari user berdasarkan email
    $query = $conn->query("SELECT * FROM users WHERE email = '$email'");

    if ($query->num_rows > 0) {
        $user = $query->fetch_assoc();
        
        // Cek password (Gunakan password_verify jika kamu menggunakan password_hash saat register)
        // Jika belum di-hash dan masih plain text, gunakan: if ($password == $user['password'])
        if (password_verify($password, $user['password'])) {
           $_SESSION['role'] = $user['role'];

if ($user['role'] == 'admin') {
    header("Location: adminmegatek/index.php"); // Arahkan ke halaman admin
} else {
    header("Location: beranda.php"); // Arahkan ke halaman beranda pelanggan
}
exit();
        } else {
            echo "<script>alert('Password salah!'); window.location.href='beranda.php';</script>";
        }
    } else {
        echo "<script>alert('Email tidak terdaftar!'); window.location.href='beranda.php';</script>";
    }
}
?>