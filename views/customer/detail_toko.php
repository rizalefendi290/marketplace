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
                                <button type="submit" class="bg-white text-gray-600 font-semibold px-5 py-2 rounded-full shadow hover:bg-gray-100 transition">Mengikuti</button>
                            </form>
                        <?php else: ?>
                            <form method="post" action="/marketplace/index.php?page=follow-toko&id=<?= $toko_id ?>">
                                <button type="submit" class="bg-white text-red-600 font-semibold px-5 py-2 rounded-full shadow hover:bg-red-50 transition">+ Ikuti</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                    <button class="bg-white text-orange-600 font-semibold px-5 py-2 rounded-full shadow hover:bg-orange-50 transition">Chat</button>
                </div>
            </div>
        </div>

        <div class="bg-white border-b border-gray-200">
            <div class="max-w-5xl mx-auto px-4 py-3 flex flex-wrap gap-4 text-gray-700 font-medium">
                <a href="#" class="hover:text-red-600 transition">Halaman Utama</a>
                <a href="#" class="hover:text-red-600 transition">Semua Produk</a>
                <a href="#" class="hover:text-red-600 transition">Topi Baseball</a>
                <a href="#" class="hover:text-red-600 transition">Bucket Hat</a>
                <a href="#" class="hover:text-red-600 transition">Lainnya</a>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow p-6 mb-8 mt-6">
            <h2 class="text-xl font-semibold mb-4">Produk dari Toko Ini</h2>
            <?php if ($produk_list): ?>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
                    <?php foreach ($produk_list as $produk): ?>
                        <a href="/marketplace/index.php?page=detail-barang&id=<?= $produk['id'] ?>" class="block border rounded-xl overflow-hidden hover:shadow-lg transition bg-white">
                            <?php if (!empty($produk['gambar']) && file_exists(__DIR__ . '/../../uploads/' . $produk['gambar'])): ?>
                                <img src="/marketplace/uploads/<?= htmlspecialchars($produk['gambar']) ?>" class="w-full h-32 object-cover bg-gray-100" alt="<?= htmlspecialchars($produk['nama_barang']) ?>">
                            <?php else: ?>
                                <div class="w-full h-32 flex items-center justify-center bg-gray-100 text-gray-400">Tidak Ada Gambar</div>
                            <?php endif; ?>
                            <div class="px-2 py-2">
                                <div class="text-sm font-medium text-gray-800 truncate"><?= htmlspecialchars($produk['nama_barang']) ?></div>
                                <div class="text-red-500 font-bold mt-1 text-sm">Rp<?= number_format($produk['harga'], 0, ',', '.') ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-gray-400 text-center py-8">Belum ada produk di toko ini.</div>
            <?php endif; ?>
        </div>

        <div class="text-center mb-10">
            <a href="/marketplace/index.php?page=home" class="inline-block bg-white text-green-700 font-semibold px-6 py-2 rounded-full shadow hover:bg-green-50 transition">Kembali ke Beranda</a>
        </div>

        <footer class="mt-16">
            <?php include __DIR__ . '/../components/footer.php'; ?>
        </footer>
</body>

</html>