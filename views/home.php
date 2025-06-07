<?php
require 'config/database.php';

// Ambil 4 barang terbaru sebagai contoh "unggulan"
$stmt = $pdo->query("SELECT * FROM barang ORDER BY id DESC LIMIT 4");
$barangUnggulan = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Marketplace Desa</title>
  <style>
    body { font-family: Arial; margin: 0; padding: 0; }
    header, footer { background: #4CAF50; color: white; padding: 1em; text-align: center; }
    .content { padding: 20px; }
    .barang { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
    .card { border: 1px solid #ccc; padding: 15px; border-radius: 8px; text-align: center; }
    a.button { background: #4CAF50; color: white; padding: 10px 15px; border-radius: 4px; text-decoration: none; }
  </style>
</head>
<body>

<header>
  <h1>Marketplace Desa</h1>
  <p>Selamat datang di marketplace desa. Belanja mudah, aman, dan dekat!</p>
  <div>
    <a href="views/login.php" class="button">Login</a>
    <a href="views/register.php" class="button">Daftar</a>
    <a href="views/katalog.php" class="button">Lihat Katalog</a>
  </div>
</header>

<div class="content">
  <h2>Barang Unggulan</h2>
  <div class="barang">
    <?php foreach ($barangUnggulan as $barang): ?>
      <div class="card">
        <h3><?= htmlspecialchars($barang['nama_barang']) ?></h3>
        <p>Rp<?= number_format($barang['harga']) ?></p>
        <a href="views/detail_barang.php?id=<?= $barang['id'] ?>">Lihat Detail</a>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<footer>
  <p>&copy; <?= date('Y') ?> Marketplace Desa</p>
</footer>

</body>
</html>
