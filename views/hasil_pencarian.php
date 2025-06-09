<?php
// filepath: c:\xampp\htdocs\marketplace\views\hasil_pencarian.php
require 'config/database.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($q === '') {
    header('Location: index.php?page=home');
    exit;
}

// Cari produk
$stmt = $pdo->prepare("SELECT b.*, t.nama_toko FROM barang b JOIN toko t ON b.toko_id = t.id WHERE b.nama_barang LIKE ? OR t.nama_toko LIKE ? ORDER BY b.id DESC");
$stmt->execute(['%' . $q . '%', '%' . $q . '%']);
$produk_list = $stmt->fetchAll();

// Cari toko
$stmt = $pdo->prepare("SELECT id, nama_toko, logo FROM toko WHERE nama_toko LIKE ? ORDER BY id DESC");
$stmt->execute(['%' . $q . '%']);
$toko_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Hasil Pencarian: "<?= htmlspecialchars($q) ?>"</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-black min-h-screen">
    <?php include 'components/header.php'; ?>

    <div class="max-w-6xl mx-auto px-4 py-12">
        <h2 class="text-2xl font-bold mb-6 text-white">Hasil Pencarian: "<?= htmlspecialchars($q) ?>"</h2>

        <h3 class="text-lg font-semibold mt-6 mb-2 text-green-400">Produk</h3>
        <?php if ($produk_list): ?>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <?php foreach ($produk_list as $produk): ?>
                    <?php
                    // Ambil rata-rata rating dan jumlah ulasan produk
                    $stmtUlasan = $pdo->prepare("SELECT AVG(rating) as rata2, COUNT(*) as total FROM ulasan WHERE barang_id = ?");
                    $stmtUlasan->execute([$produk['id']]);
                    $ulasan = $stmtUlasan->fetch();
                    $rata2 = $ulasan['rata2'] ? round($ulasan['rata2'], 1) : '-';
                    $total_ulasan = $ulasan['total'];

                    // Ambil jumlah terjual
                    $stmtTerjual = $pdo->prepare("
                SELECT SUM(td.jumlah) as terjual
                FROM transaksi_detail td
                JOIN transaksi t ON td.transaksi_id = t.id
                WHERE td.barang_id = ? AND t.status = 'selesai'
            ");
                    $stmtTerjual->execute([$produk['id']]);
                    $terjual = $stmtTerjual->fetchColumn();
                    if (!$terjual) $terjual = 0;
                    ?>
                    <a href="index.php?page=detail-barang&id=<?= $produk['id'] ?>" class="block border rounded-lg p-3 bg-white hover:shadow-lg transition">
                        <?php if (!empty($produk['gambar']) && file_exists(__DIR__ . '/../uploads/' . $produk['gambar'])): ?>
                            <img src="/marketplace/uploads/<?= htmlspecialchars($produk['gambar']) ?>" class="w-full h-32 object-cover rounded mb-2" alt="<?= htmlspecialchars($produk['nama_barang']) ?>">
                        <?php else: ?>
                            <div class="w-full h-32 flex items-center justify-center bg-gray-200 text-gray-400 rounded mb-2">Tidak Ada Gambar</div>
                        <?php endif; ?>
                        <div class="font-semibold text-gray-800 truncate"><?= htmlspecialchars($produk['nama_barang']) ?></div>
                        <div class="flex items-center gap-2 text-xs text-gray-500 mt-1">
                            <span class="text-yellow-500 flex items-center">
                                <svg class="w-4 h-4 mr-1 inline" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.175c.969 0 1.371 1.24.588 1.81l-3.38 2.455a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.38-2.454a1 1 0 00-1.175 0l-3.38 2.454c-.784.57-1.838-.196-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118L2.05 9.394c-.783-.57-.38-1.81.588-1.81h4.175a1 1 0 00.95-.69l1.286-3.967z" />
                                </svg>
                                <?= $rata2 ?>
                            </span>
                            <span>(<?= $total_ulasan ?> ulasan)</span>
                            <span class="ml-auto text-blue-500"><?= $terjual ?> terjual</span>
                        </div>
                        <div class="text-green-600 font-bold mt-1">Rp<?= number_format($produk['harga'], 0, ',', '.') ?></div>
                        <div class="text-xs text-gray-500 mt-1">Toko: <?= htmlspecialchars($produk['nama_toko']) ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-gray-400 py-8">Tidak ada produk ditemukan.</div>
        <?php endif; ?>
    </div>

    <footer class="mt-16">
        <?php include 'components/footer.php'; ?>
    </footer>
</body>

</html>