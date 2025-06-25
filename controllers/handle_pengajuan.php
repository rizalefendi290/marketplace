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

$upload_dir = __DIR__ . '/../uploads/pengajuan/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Fungsi untuk upload file
function uploadFile($field, $dir) {
    if (isset($_FILES[$field]) && $_FILES[$field]['error'] === 0) {
        $filename = time() . '-' . basename($_FILES[$field]['name']);
        $filepath = $dir . $filename;
        move_uploaded_file($_FILES[$field]['tmp_name'], $filepath);
        return $filename;
    }
    return null;
}

// Upload semua file
$file_bukti = uploadFile('dokumentasi', $upload_dir);
$file_ktp_kk = uploadFile('ktp_kk', $upload_dir);
$file_sku_nib = uploadFile('sku_nib', $upload_dir);
$file_rekening_bank = uploadFile('rekening_bank', $upload_dir);

// Simpan ke database
$stmt = $pdo->prepare("INSERT INTO pengajuan_dana 
(user_id, nama, alamat, nik, jenis_kelamin, no_telepon, email, nama_usaha, alamat_usaha, status_usaha, legalitas_usaha, no_rekening, file_bukti, file_ktp_kk, file_sku_nib, file_rekening_bank, tanggal_pengajuan, status)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu')");

$stmt->execute([
    $user_id, $nama, $alamat, $nik, $jenis_kelamin, $no_telepon, $email,
    $nama_usaha, $alamat_usaha, $status_usaha, $legalitas_usaha,
    $no_rekening, $file_bukti, $file_ktp_kk, $file_sku_nib, $file_rekening_bank,
    $tanggal_pengajuan
]);

header("Location: /marketplace/index.php?page=admin-dashboard&success=1");
exit;
?>
