<?php
session_start();
require '../config/database.php';

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    die("Username tidak ditemukan di database.");
}

if ($user && password_verify(password: $password, hash: $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    // Redirect sesuai role
    if ($user['role'] == 'admin_toko') {
        header("Location: /marketplace/index.php?page=admin-dashboard");
    } elseif ($user['role'] == 'customer') {
        header("Location: /marketplace/index.php?page=customer-dashboard");
    } elseif ($user['role'] == 'petugas_desa') {
        header("Location: /marketplace/index.php?page=petugas-dashboard");
    } else {
        header("Location: /marketplace/views/login.php?error=1");
    }
    exit;
} else {
    header("Location: ../views/login.php?error=1");
}
