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

// Ambil produk-produk toko
$stmt = $pdo->prepare("SELECT * FROM barang WHERE toko_id = ? ORDER BY id DESC");
$stmt->execute([$toko_id]);
$produk_list = $stmt->fetchAll();

// Ambil penilaian rata-rata dan jumlah ulasan
$stmt = $pdo->prepare("SELECT AVG(rating) as rata2, COUNT(*) as total FROM ulasan WHERE toko_id = ?");
$stmt->execute([$toko_id]);
$ulasan = $stmt->fetch();
$rata2 = $ulasan['rata2'] ? round($ulasan['rata2'], 2) : '-';
$total_ulasan = $ulasan['total'];

// Ambil jumlah pengikut
$stmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE toko_id = ?");
$stmt->execute([$toko_id]);
$pengikut = $stmt->fetchColumn();

// Tanggal bergabung
$bergabung = $toko['created_at'] ? date('d M Y', strtotime($toko['created_at'])) : '-';

$is_following = false;
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE toko_id = ? AND user_id = ?");
    $stmt->execute([$toko_id, $_SESSION['user_id']]);
    $is_following = $stmt->fetchColumn() > 0;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($toko['nama_toko']) ?> - Detail Toko</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-black min-h-screen">
    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="max-w-5xl mx-auto px-4 py-12">
        <div class="bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-t-2xl p-6 shadow">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="flex items-center gap-5">
                    <img src="<?= !empty($toko['logo']) && file_exists(__DIR__ . '/../../uploads/' . $toko['logo'])
                                    ? '/marketplace/uploads/' . htmlspecialchars($toko['logo'])
                                    : 'https://ui-avatars.com/api/?name=' . urlencode($toko['nama_toko']) . '&background=10b981&color=fff&size=128' ?>"
                        class="w-24 h-24 rounded-full border-4 border-white shadow-lg bg-white object-cover" alt="Logo Toko">

                    <div>
                        <h1 class="text-3xl font-bold mb-1"><?= htmlspecialchars($toko['nama_toko']) ?></h1>
                        <div class="flex flex-wrap gap-4 text-sm mt-1">
                            <div>
                                <span class="font-semibold"><?= $pengikut ?></span> Pengikut
                            </div>
                            <div>
                                <span class="font-semibold"><?= $rata2 ?></span> (<?= $total_ulasan ?> Penilaian)
                            </div>
                            <div>
                                <span class="font-semibold">Bergabung:</span> <?= $bergabung ?>
                            </div>
                        </div>
                        <?php if (!empty($toko['deskripsi'])): ?>
                            <div class="mt-2 text-white/80 text-sm"><?= nl2br(htmlspecialchars($toko['deskripsi'])) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex gap-2 mt-4 md:mt-0">
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
                        <?php if ($is_following): ?>
                            <form method="post" action="/marketplace/index.php?page=unfollow-toko&id=<?= $toko_id ?>">
                                <button type="submit" class="bg-gray-900 text-yellow-300 font-semibold px-5 py-2 rounded-full shadow hover:bg-yellow-500 hover:text-black transition">Mengikuti</button>
                            </form>
                        <?php else: ?>
                            <form method="post" action="/marketplace/index.php?page=follow-toko&id=<?= $toko_id ?>">
                                <button type="submit" class="bg-gray-900 text-red-600 font-semibold px-5 py-2 rounded-full shadow hover:bg-red-50 transition">+ Ikuti</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                    <button class="bg-yellow-300 text-black font-semibold px-5 py-2 rounded-full shadow hover:bg-gray-900 hover:text-yellow-500 transition">Chat</button>
                </div>
            </div>
        </div>

        <div class="bg-black border-b border-yellow-300">
            <div class="max-w-5xl mx-auto px-4 py-3 flex flex-wrap gap-4 text-yellow-300 font-medium">
                <a href="#" class="hover:text-yellow-500 transition">Halaman Utama</a>
                <a href="/marketplace/index.php?page=produk-toko&id=<?= $toko_id ?>" class="hover:text-yellow-500 transition">Semua Produk</a>
            </div>
        </div>

        <div class="bg-gray-900 border border-yellow-300 rounded-2xl shadow p-6 mb-8 mt-6">
            <h2 class="text-xl font-semibold mb-4 text-yellow-300">Produk dari Toko Ini</h2>
            <?php if ($produk_list): ?>
                <div class="bg-gray-900 grid grid-cols-2 md:grid-cols-5 gap-6">
                    <?php foreach ($produk_list as $produk): ?>
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
                        <a href="/marketplace/index.php?page=detail-barang&id=<?= $produk['id'] ?>" class="block border border-yellow-300 rounded-xl overflow-hidden hover:shadow-lg transition bg-white">
                            <?php if (!empty($produk['gambar']) && file_exists(__DIR__ . '/../../uploads/' . $produk['gambar'])): ?>
                                <img src="/marketplace/uploads/<?= htmlspecialchars($produk['gambar']) ?>" class="w-full h-32 object-cover bg-gray-100" alt="<?= htmlspecialchars($produk['nama_barang']) ?>">
                            <?php else: ?>
                                <div class="w-full h-32 flex items-center justify-center bg-gray-100 text-gray-400">Tidak Ada Gambar</div>
                            <?php endif; ?>
                            <div class="bg-gray-900 px-2 py-2">
                                <div class="text-sm font-medium text-white truncate"><?= htmlspecialchars($produk['nama_barang']) ?></div>
                                <div class="flex items-center gap-2 text-xs text-gray-500 mt-1">
                                    <span class="text-yellow-500 flex items-center">
                                        <svg class="w-4 h-4 mr-1 inline" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.175c.969 0 1.371 1.24.588 1.81l-3.38 2.455a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.38-2.454a1 1 0 00-1.175 0l-3.38 2.454c-.784.57-1.838-.196-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118L2.05 9.394c-.783-.57-.38-1.81.588-1.81h4.175a1 1 0 00.95-.69l1.286-3.967z" />
                                        </svg>
                                        <?= $rata2 ?>
                                    </span>
                                    <span>(<?= $total_ulasan ?> ulasan)</span>
                                    <span class="ml-auto text-yellow-300"><?= $terjual ?> terjual</span>
                                </div>
                                <div class="text-yellow-300 font-bold mt-1 text-sm">Rp<?= number_format($produk['harga'], 0, ',', '.') ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-gray-400 text-center py-8">Belum ada produk di toko ini.</div>
            <?php endif; ?>
        </div>

        <div class="text-center mb-10">
            <a href="/marketplace/index.php?page=home" class="inline-block bg-gray-900 text-yellow-300 hover:text-black font-semibold px-6 py-2 rounded-full shadow hover:bg-yellow-500 transition">Kembali ke Beranda</a>
        </div>

        <footer class="mt-16">
            <?php include __DIR__ . '/../components/footer.php'; ?>
        </footer>
</body>

</html>