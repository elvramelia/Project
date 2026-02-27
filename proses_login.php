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
        
        // Cek password
        if (password_verify($password, $user['password'])) {
            
            // TAMBAHKAN SESSION INI AGAR SESUAI DENGAN check_login.php
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Redirect berdasarkan role
            if ($user['role'] == 'admin') {
                header("Location: adminmegatek/index.php"); 
            } else {
                header("Location: beranda.php"); 
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