<?php
require '../config/database.php';

$nama = $_POST['nama'] ?? '';
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$nomor_hp = $_POST['nomor_hp'] ?? '';
$password = $_POST['password'] ?? '';

// Tetapkan role secara langsung ke 'customer'
$role = 'customer';

// Cek username sudah ada
$cek = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$cek->execute([$username]);

if ($cek->rowCount() > 0) {
    header("Location: ../views/register.php?error=1");
    exit;
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Simpan ke DB
$stmt = $pdo->prepare("INSERT INTO users (nama, username, email, nomor_hp, password, role) VALUES (?, ?, ?, ?, ?, ?)");
$sukses = $stmt->execute([$nama, $username, $email, $nomor_hp, $hashedPassword, $role]);

if ($sukses) {
    header("Location: ../views/login.php");
} else {
    header("Location: ../views/register.php?error=1");
}
