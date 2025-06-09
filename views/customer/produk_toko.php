<?php
require __DIR__ . '/../../config/database.php';

// Ambil id toko dari URL
if (!isset($_GET['id'])) {
    echo "Toko tidak ditemukan.";
    exit;
}
$toko_id = (int)$_GET['id'];

// Ambil detail toko
$stmt = $pdo->prepare("SELECT * FROM toko WHERE id = ?");
$stmt->execute([$toko_id]);
$toko = $stmt->fetch();

if (!$toko) {
    echo "Toko tidak ditemukan.";
    exit;
}

// Ambil semua produk toko
$stmt = $pdo->prepare("SELECT * FROM barang WHERE toko_id = ? ORDER BY id DESC");
$stmt->execute([$toko_id]);
$produk_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Semua Produk - <?= htmlspecialchars($toko['nama_toko']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-black min-h-screen">
    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="max-w-5xl mx-auto px-4 py-12">
        <!-- Header Toko -->
        <div class="flex items-center gap-4 mb-8">
            <?php
            $logo_path = __DIR__ . '/../../uploads/' . $toko['logo'];
            ?>
            <img src="<?= (!empty($toko['logo']) && file_exists($logo_path))
                            ? '/marketplace/uploads/' . htmlspecialchars($toko['logo'])
                            : 'https://ui-avatars.com/api/?name=' . urlencode($toko['nama_toko']) . '&background=10b981&color=fff&size=128' ?>"
                class="w-16 h-16 rounded-full border-4 border-white shadow bg-white object-cover" alt="Logo Toko">
            <div>
                <h1 class="text-2xl font-bold text-white"><?= htmlspecialchars($toko['nama_toko']) ?></h1>
                <div class="text-gray-300 text-sm">Semua Produk</div>
            </div>
        </div>

        <!-- Navigasi -->
        <div class="bg-white border-b border-gray-200 rounded-t-xl mb-6">
            <div class="max-w-5xl mx-auto px-4 py-3 flex flex-wrap gap-4 text-gray-700 font-medium">
                <a href="/marketplace/index.php?page=detail-toko&id=<?= $toko_id ?>" class="hover:text-red-600 transition">Kembali ke Detail Toko</a>
                <span class="text-gray-400">|</span>
                <span class="text-red-600">Semua Produk</span>
            </div>
        </div>

        <!-- Daftar Produk -->
        <div class="bg-white rounded-2xl shadow p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Daftar Produk</h2>
            <?php if ($produk_list): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
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
                        <a href="/marketplace/index.php?page=detail-barang&id=<?= $produk['id'] ?>" class="block border rounded-xl overflow-hidden hover:shadow-lg transition bg-white group">
                            <?php
                            $gambar_path = __DIR__ . '/../../uploads/' . $produk['gambar'];
                            ?>
                            <?php if (!empty($produk['gambar']) && file_exists($gambar_path)): ?>
                                <img src="/marketplace/uploads/<?= htmlspecialchars($produk['gambar']) ?>" class="w-full h-36 object-cover bg-gray-100 group-hover:scale-105 transition rounded-t-xl" alt="<?= htmlspecialchars($produk['nama_barang']) ?>">
                            <?php else: ?>
                                <div class="w-full h-36 flex items-center justify-center bg-gray-100 text-gray-400 rounded-t-xl">Tidak Ada Gambar</div>
                            <?php endif; ?>
                            <div class="px-3 py-3">
                                <div class="text-base font-semibold text-gray-800 truncate mb-1"><?= htmlspecialchars($produk['nama_barang']) ?></div>
                                <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                                    <span class="text-yellow-500 flex items-center">
                                        <svg class="w-4 h-4 mr-1 inline" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.175c.969 0 1.371 1.24.588 1.81l-3.38 2.455a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.38-2.454a1 1 0 00-1.175 0l-3.38 2.454c-.784.57-1.838-.196-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118L2.05 9.394c-.783-.57-.38-1.81.588-1.81h4.175a1 1 0 00.95-.69l1.286-3.967z" />
                                        </svg>
                                        <?= $rata2 ?>
                                    </span>
                                    <span>(<?= $total_ulasan ?> ulasan)</span>
                                    <span class="ml-auto text-blue-500"><?= $terjual ?> terjual</span>
                                </div>
                                <div class="text-green-600 font-bold text-lg">Rp<?= number_format($produk['harga'], 0, ',', '.') ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-gray-400 text-center py-8">Belum ada produk di toko ini.</div>
            <?php endif; ?>
        </div>

        <div class="text-center mb-10">
            <a href="/marketplace/index.php?page=detail-toko&id=<?= $toko_id ?>" class="inline-block bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white font-semibold px-8 py-3 rounded-full shadow-lg text-lg transition">Kembali ke Detail Toko</a>
        </div>
    </div>

    <footer class="mt-16">
        <?php include __DIR__ . '/../components/footer.php'; ?>
    </footer>
</body>
</html>