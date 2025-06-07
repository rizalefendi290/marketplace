<?php
require '../config/database.php';
$stmt = $pdo->query("SELECT * FROM barang"); // asumsi ada tabel barang
$barang = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Katalog Barang</title>
</head>
<body>
  <h2>🛍️ Katalog Barang</h2>
  <ul>
    <?php foreach ($barang as $b): ?>
      <li>
        <h3><?= htmlspecialchars($b['nama_barang']) ?></h3>
        <p>Harga: Rp<?= number_format($b['harga']) ?></p>
        <a href="detail_barang.php?id=<?= $b['id'] ?>">Lihat Detail</a>
      </li>
    <?php endforeach; ?>
  </ul>

  <hr>
  <a href="login.php">Login untuk Checkout</a>
</body>
</html>
