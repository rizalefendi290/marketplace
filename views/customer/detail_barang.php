<?php
require '../config/database.php';
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM barang WHERE id = ?");
$stmt->execute([$id]);
$barang = $stmt->fetch();

if (!$barang) {
    echo "Barang tidak ditemukan.";
    exit;
}
?>

<h2><?= htmlspecialchars($barang['nama_barang']) ?></h2>
<p>Deskripsi: <?= htmlspecialchars($barang['deskripsi']) ?></p>
<p>Harga: Rp<?= number_format($barang['harga']) ?></p>
<a href="checkout.php?id=<?= $barang['id'] ?>">Checkout Sekarang</a>
