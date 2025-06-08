<?php
require __DIR__ . '/../../config/database.php';

// Pastikan user sudah login dan berperan sebagai customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: /marketplace/index.php?page=login");
    exit;
}

if (!isset($_GET['id'])) {
    echo "Transaksi tidak ditemukan.";
    exit;
}

$user_id = $_SESSION['user_id'];
$transaksi_id = (int)$_GET['id'];

// Ambil data transaksi dan user (tambahkan alamat, telepon, dst)
$stmt = $pdo->prepare("
    SELECT t.*, u.nama, u.email, u.alamat, u.no_telp
    FROM transaksi t
    JOIN users u ON t.user_id = u.id
    WHERE t.id = ? AND t.user_id = ?
");
$stmt->execute([$transaksi_id, $user_id]);
$transaksi = $stmt->fetch();

if (!$transaksi) {
    echo "Transaksi tidak ditemukan.";
    exit;
}

// Ambil detail barang pada transaksi
$stmt = $pdo->prepare("
    SELECT td.*, b.nama_barang, b.gambar, b.harga, t.nama_toko
    FROM transaksi_detail td
    JOIN barang b ON td.barang_id = b.id
    JOIN toko t ON b.toko_id = t.id
    WHERE td.transaksi_id = ?
");
$stmt->execute([$transaksi_id]);
$detail = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Pembelian #<?= $transaksi_id ?> - Marketplace Desa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black min-h-screen">
    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="max-w-3xl mx-auto px-4 py-12">
        <h1 class="text-2xl font-bold text-green-400 mb-6 text-center">Detail Pembelian</h1>
        <div class="bg-gray-900 rounded-2xl shadow-xl p-8 border border-green-800 mb-8">
            <div class="mb-4 flex flex-col md:flex-row md:justify-between md:items-center gap-2">
                <div>
                    <div class="text-gray-300 text-sm">ID Transaksi</div>
                    <div class="font-bold text-white mb-2">#<?= $transaksi['id'] ?></div>
                    <div class="text-gray-300 text-sm">Tanggal</div>
                    <div class="mb-2 text-white"><?= date('d/m/Y H:i', strtotime($transaksi['tanggal_transaksi'])) ?></div>
                    <div class="text-gray-300 text-sm">Status</div>
                    <span class="px-2 py-1 rounded text-xs font-semibold
                        <?php
                        if ($transaksi['status'] === 'Menunggu Verifikasi') echo 'bg-yellow-500 text-white';
                        elseif ($transaksi['status'] === 'Diverifikasi') echo 'bg-green-600 text-white';
                        elseif ($transaksi['status'] === 'Ditolak') echo 'bg-red-600 text-white';
                        elseif ($transaksi['status'] === 'Selesai') echo 'bg-blue-600 text-white';
                        else echo 'bg-gray-600 text-white';
                        ?>">
                        <?= htmlspecialchars($transaksi['status']) ?>
                    </span>
                </div>
                <div>
                    <div class="text-gray-300 text-sm">Metode Pembayaran</div>
                    <div class="mb-2 text-white"><?= htmlspecialchars($transaksi['metode_pembayaran']) ?></div>
                    <div class="text-gray-300 text-sm">Nama Pemesan</div>
                    <div class="mb-2 text-white"><?= htmlspecialchars($transaksi['nama']) ?></div>
                    <div class="text-gray-300 text-sm">Email</div>
                    <div class="mb-2 text-white"><?= htmlspecialchars($transaksi['email']) ?></div>
                    <div class="text-gray-300 text-sm">Alamat</div>
                    <div class="mb-2 text-white"><?= htmlspecialchars($transaksi['alamat']) ?></div>
                    <div class="text-gray-300 text-sm">No. Telepon</div>
                    <div class="mb-2 text-white"><?= htmlspecialchars($transaksi['no_telp']) ?></div>
                </div>
            </div>
            <hr class="my-4 border-green-800">
            <div>
                <div class="text-lg font-semibold text-green-400 mb-3">Produk</div>
                <div class="space-y-4">
                    <?php foreach ($detail as $item): ?>
                        <div class="flex items-center gap-4 bg-gray-800 rounded-lg p-3">
                            <?php if ($item['gambar']): ?>
                                <img src="/marketplace/uploads/<?= htmlspecialchars($item['gambar']) ?>" class="w-16 h-16 object-cover rounded border" alt="">
                            <?php endif; ?>
                            <div class="flex-1">
                                <div class="font-bold text-white"><?= htmlspecialchars($item['nama_barang']) ?></div>
                                <div class="text-gray-400 text-sm">Toko: <?= htmlspecialchars($item['nama_toko']) ?></div>
                                <div class="text-gray-400 text-sm">Jumlah: <?= $item['jumlah'] ?></div>
                                <div class="text-gray-400 text-sm">Harga Satuan: Rp <?= number_format($item['harga_satuan'], 0, ',', '.') ?></div>
                                <div class="text-green-400 font-semibold">Subtotal: Rp <?= number_format($item['jumlah'] * $item['harga_satuan'], 0, ',', '.') ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-6 text-right">
                    <span class="text-gray-300">Total:</span>
                    <span class="text-2xl font-bold text-green-400">
                        Rp <?= number_format(array_sum(array_map(function($i){return $i['jumlah']*$i['harga_satuan'];}, $detail)), 0, ',', '.') ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="text-center">
            <a href="/marketplace/index.php?page=riwayat-pembelian" class="inline-block bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white font-semibold px-8 py-3 rounded-full shadow-lg text-lg transition">Kembali ke Riwayat</a>
        </div>
    </div>

    <footer class="mt-16">
        <?php include __DIR__ . '/../components/footer.php'; ?>
    </footer>
</body>
</html>