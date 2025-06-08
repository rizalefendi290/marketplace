<?php
require __DIR__ . '/../../config/database.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin_toko') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID produk tidak ditemukan.");
}

// Cek apakah produk milik toko admin ini
$stmt = $pdo->prepare("SELECT b.id FROM barang b JOIN toko t ON b.toko_id = t.id WHERE b.id = ? AND t.admin_id = ?");
$stmt->execute([$id, $user_id]);
$produk = $stmt->fetch();

if (!$produk) {
    die("Produk tidak ditemukan atau bukan milik Anda.");
}

// Hapus produk
$stmt = $pdo->prepare("DELETE FROM barang WHERE id = ?");
$stmt->execute([$id]);

header("Location: ../../index.php?page=admin-dashboard");
exit;
