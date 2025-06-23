<?php
require '../config/database.php';

$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];
$confirm = $_POST['confirm_password'];

if ($password !== $confirm) {
    die("Konfirmasi password tidak cocok.");
}

// Cek username unik
$check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$check->execute([$username]);
if ($check->fetch()) {
    die("Username sudah digunakan.");
}

// Hash dan simpan
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'petugas_desa')");
$stmt->execute([$username, $email, $hash]);

header("Location: /marketplace/index.php?page=login&success=register");
exit;
