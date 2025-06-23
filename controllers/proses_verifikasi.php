<?php
require __DIR__ . '/../config/database.php';

if (!isset($_GET['id']) || !isset($_GET['aksi'])) {
    die("Parameter tidak lengkap.");
}

$id = (int) $_GET['id'];
$aksi = $_GET['aksi'];
$status = 'Menunggu';

if ($aksi === 'proses') {
    $status = 'Diproses';
} elseif ($aksi === 'setujui') {
    $status = 'Disetujui';
} elseif ($aksi === 'tolak') {
    $status = 'Ditolak';
}


$stmt = $pdo->prepare("UPDATE pengajuan_dana SET status = ? WHERE id = ?");
$stmt->execute([$status, $id]);

header("Location: /marketplace/index.php?page=daftar-pengajuan&berhasil=1");
exit;
