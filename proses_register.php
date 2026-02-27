<?php
require_once 'config/database.php';

// Pastikan data dikirim melalui metode POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Ambil dan bersihkan data input
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name  = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email      = mysqli_real_escape_string($conn, $_POST['email']);
    // Ambil input phone_number dari form
    $phone      = mysqli_real_escape_string($conn, $_POST['phone_number']); 
    $password   = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Validasi: Pastikan semua field terisi
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        header("Location: beranda.php?status=empty");
        exit();
    }

    // 2. Validasi: Cek apakah password dan konfirmasi password cocok
    if ($password !== $confirm_password) {
        header("Location: beranda.php?status=password_mismatch");
        exit();
    }

    // 3. Cek apakah email sudah terdaftar
    $check_email = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $result = $check_email->get_result();

    if ($result->num_rows > 0) {
        header("Location: beranda.php?status=email_taken");
        exit();
    }

    // 4. Hash Password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 5. Insert ke Database 
    // PERBAIKAN: Menggunakan kolom 'phone_number' sesuai dengan yang ada di database Anda
    $sql = "INSERT INTO users (first_name, last_name, email, phone_number, password) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    // Tambahan perlindungan: Jika nama kolom di SQL masih salah, akan muncul pesan error di layar
    if (!$stmt) {
        die("Terjadi kesalahan pada Query SQL: " . $conn->error);
    }

    $stmt->bind_param("sssss", $first_name, $last_name, $email, $phone, $hashed_password);

    if ($stmt->execute()) {
        // Berhasil daftar, arahkan ke halaman utama dengan pesan sukses
        header("Location: beranda.php?status=success_reg");
    } else {
        // Gagal karena error saat eksekusi
        die("Gagal menyimpan ke database: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
} else {
    // Jika mencoba akses file langsung tanpa POST
    header("Location: beranda.php");
    exit();
}
?>