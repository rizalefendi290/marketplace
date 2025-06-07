<?php
// controllers/pengajuan_dana/save.php
require 'config/database.php';
$userId = $_SESSION['user_id'];
$judul = $_POST['judul'];
$deskripsi = $_POST['deskripsi'];
$jumlah = $_POST['jumlah'];
$file = $_FILES['lampiran']['name'];

// upload file
move_uploaded_file($_FILES['lampiran']['tmp_name'], "uploads/$file");

$stmt = $pdo->prepare("INSERT INTO pengajuan_dana (user_id, judul, deskripsi, jumlah_dana, pdf_file) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$userId, $judul, $deskripsi, $jumlah, $file]);
header("Location: index.php?page=pengajuan-dana");

