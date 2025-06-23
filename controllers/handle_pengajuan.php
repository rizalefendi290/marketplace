<?php
session_start();
require __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin_toko') {
    die("Akses ditolak.");
}

$user_id = $_SESSION['user_id'];

// Ambil data dari form
$nama = $_POST['nama'] ?? '';
$alamat = $_POST['alamat'] ?? '';
$nik = $_POST['nik'] ?? '';
$jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
$no_telepon = $_POST['no_telepon'] ?? '';
$email = $_POST['email'] ?? '';
$nama_usaha = $_POST['nama_usaha'] ?? '';
$alamat_usaha = $_POST['alamat_usaha'] ?? '';
$status_usaha = $_POST['status_usaha'] ?? '';
$legalitas_usaha = $_POST['legalitas_usaha'] ?? '';
$no_rekening = $_POST['no_rekening'] ?? '';
$tanggal_pengajuan = date('Y-m-d');

$filename = '';
if (isset($_FILES['dokumentasi']) && $_FILES['dokumentasi']['error'] === 0) {
    $upload_dir = __DIR__ . '/../uploads/pengajuan/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $filename = time() . '-' . basename($_FILES['dokumentasi']['name']);
    $filepath = $upload_dir . $filename;
    move_uploaded_file($_FILES['dokumentasi']['tmp_name'], $filepath);
}

// Simpan ke database
$stmt = $pdo->prepare("INSERT INTO pengajuan_dana 
(user_id, nama, alamat, nik, jenis_kelamin, no_telepon, email, nama_usaha, alamat_usaha, status_usaha, legalitas_usaha, no_rekening, file_bukti, tanggal_pengajuan, status) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu')");

$stmt->execute([
    $user_id, $nama, $alamat, $nik, $jenis_kelamin, $no_telepon, $email,
    $nama_usaha, $alamat_usaha, $status_usaha, $legalitas_usaha,
    $no_rekening, $filename, $tanggal_pengajuan
]);

header("Location: /marketplace/index.php?page=admin-dashboard&success=1");
exit;
?>
