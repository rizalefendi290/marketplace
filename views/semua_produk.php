<?php
require __DIR__ . '/../config/database.php';

// Ambil semua produk beserta nama toko
$stmt = $pdo->query("
    SELECT b.*, t.nama_toko, t.logo
    FROM barang b
    JOIN toko t ON b.toko_id = t.id
    ORDER BY b.id DESC
");
$produkList = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Semua Produk - Marketplace Desa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black min-h-screen">
    <?php include __DIR__ . '/components/header.php'; ?>

    <div class="max-w-6xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold text-yellow-500 mb-10 text-center">Semua Produk</h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
            <?php if ($produkList): ?>
                <?php foreach ($produkList as $produk): ?>
                    <?php
                    // Ambil rata-rata rating dan jumlah ulasan produk
                    $stmtUlasan = $pdo->prepare("SELECT AVG(rating) as rata2, COUNT(*) as total FROM ulasan WHERE barang_id = ?");
                    $stmtUlasan->execute([$produk['id']]);
                    $ulasan = $stmtUlasan->fetch();
                    $rata2 = $ulasan['rata2'] ? round($ulasan['rata2'], 1) : '-';
                    $total_ulasan = $ulasan['total'];

                    // Ambil jumlah terjual (asumsi tabel transaksi_detail dan status transaksi 'selesai')
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
                    <div class="bg-gray-900 rounded-2xl shadow-xl hover:shadow-2xl transition p-4 flex flex-col items-center border border-yellow-300 group relative overflow-hidden">
                        <a href="index.php?page=detail-barang&id=<?= $produk['id'] ?>">
                            <?php if (!empty($produk['gambar']) && file_exists(__DIR__ . '/../uploads/' . $produk['gambar'])): ?>
                                <img class="rounded-xl w-full h-40 object-cover mb-3 border border-gray-700 group-hover:scale-105 transition" src="/marketplace/uploads/<?= htmlspecialchars($produk['gambar']) ?>" alt="<?= htmlspecialchars($produk['nama_barang']) ?>" />
                            <?php else: ?>
                                <div class="w-full h-40 flex items-center justify-center bg-gray-800 text-gray-400 rounded-xl border border-gray-700 mb-3">Tidak Ada Gambar</div>
                            <?php endif; ?>
                        </a>
                        <div class="flex-1 w-full">
                            <a href="index.php?page=detail-barang&id=<?= $produk['id'] ?>">
                                <h5 class="mb-2 text-lg font-bold tracking-tight text-white hover:text-green-400 transition"><?= htmlspecialchars($produk['nama_barang']) ?></h5>
                            </a>
                            <p class="mb-1 text-sm text-gray-400">Toko: <span class="font-semibold text-yellow-500"><?= htmlspecialchars($produk['nama_toko']) ?></span></p>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-yellow-400 flex items-center">
                                    <svg class="w-4 h-4 mr-1 inline" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.175c.969 0 1.371 1.24.588 1.81l-3.38 2.455a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.38-2.454a1 1 0 00-1.175 0l-3.38 2.454c-.784.57-1.838-.196-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118L2.05 9.394c-.783-.57-.38-1.81.588-1.81h4.175a1 1 0 00.95-.69l1.286-3.967z" />
                                    </svg>
                                    <?= $rata2 ?>
                                </span>
                                <span class="text-gray-400 text-xs">(<?= $total_ulasan ?> ulasan)</span>
                                <span class="text-yellow-500 text-xs ml-auto"><?= $terjual ?> terjual</span>
                            </div>
                            <p class="mb-3 font-semibold text-yellow-500 text-xl">Rp<?= number_format($produk['harga'], 0, ',', '.') ?></p>
                        </div>
                        <a href="index.php?page=detail-barang&id=<?= $produk['id'] ?>" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-green-500 to-blue-500 rounded-lg shadow hover:from-green-600 hover:to-blue-600 focus:ring-4 focus:outline-none focus:ring-green-200 transition">
                            Lihat Detail
                            <svg class="w-4 h-4 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9" />
                            </svg>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center text-gray-400 py-8">Belum ada produk terdaftar.</div>
            <?php endif; ?>
        </div>
        <div class="mt-12 text-center">
            <a href="index.php?page=home" class="inline-block bg-gradient-to-r from-blue-600 to-green-600 hover:from-blue-700 hover:to-green-700 text-white font-semibold px-10 py-4 rounded-full shadow-lg text-lg transition">
                Kembali ke Beranda
            </a>
        </div>
    </div>

    <footer class="mt-16">
        <?php include __DIR__ . '/components/footer.php'; ?>
    </footer>
</body>
</html>