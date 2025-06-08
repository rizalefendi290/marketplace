<?php
require __DIR__ . '/../../config/database.php';

// Cek login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: index.php?page=login");
    exit;
}

if (!isset($_POST['barang_id']) || !isset($_POST['jumlah'])) {
    echo "Data tidak lengkap.";
    exit;
}

$barang_id = (int) $_POST['barang_id'];
$jumlah = (int) $_POST['jumlah'];
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT b.*, t.nama_toko 
    FROM barang b 
    JOIN toko t ON b.toko_id = t.id 
    WHERE b.id = ?
");
$stmt->execute([$barang_id]);
$barang = $stmt->fetch();

if (!$barang) {
    echo "Produk tidak ditemukan.";
    exit;
}

$total_harga = $barang['harga'] * $jumlah;

if (isset($_POST['konfirmasi'])) {
    $metode = $_POST['metode_pembayaran'] ?? '';

    if ($metode === '') {
        echo "<script>alert('Pilih metode pembayaran!'); window.history.back();</script>";
        exit;
    }

    // Simpan transaksi
    $stmt = $pdo->prepare("INSERT INTO transaksi (user_id, tanggal_transaksi, metode_pembayaran, status) VALUES (?, NOW(), ?, 'Menunggu Verifikasi')");
    $stmt->execute([$user_id, $metode]);
    $transaksi_id = $pdo->lastInsertId();

    // Simpan detail
    $stmt = $pdo->prepare("INSERT INTO transaksi_detail (transaksi_id, barang_id, jumlah, harga_satuan) VALUES (?, ?, ?, ?)");
    $stmt->execute([$transaksi_id, $barang_id, $jumlah, $barang['harga']]);

    // Update stok
    $stmt = $pdo->prepare("UPDATE barang SET stok = stok - ? WHERE id = ?");
    $stmt->execute([$jumlah, $barang_id]);

    echo "<script>alert('Pesanan berhasil dibuat! Menunggu verifikasi pembayaran.'); window.location.href='index.php?page=customer-dashboard';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout - <?= htmlspecialchars($barang['nama_barang']) ?> | Marketplace Desa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black min-h-screen">
    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="max-w-2xl mx-auto px-4 py-12">
        <div class="bg-gray-900 rounded-2xl shadow-xl border border-green-800 p-8 relative overflow-hidden">
            <!-- Decorative SVG -->
            <svg class="absolute left-0 top-0 w-32 h-32 opacity-10" viewBox="0 0 200 200" fill="none">
                <circle cx="100" cy="100" r="100" fill="#fff" />
            </svg>
            <svg class="absolute right-0 bottom-0 w-40 h-40 opacity-5" viewBox="0 0 220 220" fill="none">
                <rect width="220" height="220" rx="110" fill="#fff" />
            </svg>
            <h2 class="text-2xl font-bold text-green-400 mb-6 text-center">Checkout Produk</h2>
            <div class="flex flex-col md:flex-row gap-6 items-center mb-6">
                <?php if ($barang['gambar']): ?>
                    <img src="/marketplace/uploads/<?= htmlspecialchars($barang['gambar']) ?>" alt="<?= htmlspecialchars($barang['nama_barang']) ?>" class="rounded-xl w-36 h-36 object-cover border border-gray-700 bg-white shadow">
                <?php else: ?>
                    <div class="w-36 h-36 flex items-center justify-center bg-gray-800 text-gray-400 rounded-xl border border-gray-700">Tidak Ada Gambar</div>
                <?php endif; ?>
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-white mb-1"><?= htmlspecialchars($barang['nama_barang']) ?></h3>
                    <p class="text-green-400 font-semibold text-lg mb-1">Rp<?= number_format($barang['harga'], 0, ',', '.') ?></p>
                    <p class="text-gray-300 mb-1"><span class="font-semibold">Jumlah:</span> <?= $jumlah ?></p>
                    <p class="text-gray-300 mb-1"><span class="font-semibold">Toko:</span> <span class="text-green-400"><?= htmlspecialchars($barang['nama_toko']) ?></span></p>
                    <p class="text-gray-300 mb-1"><span class="font-semibold">Total Harga:</span> <span class="text-green-400 font-bold">Rp<?= number_format($total_harga, 0, ',', '.') ?></span></p>
                </div>
            </div>
            <form method="POST" class="space-y-5 z-10 relative">
                <input type="hidden" name="barang_id" value="<?= $barang_id ?>">
                <input type="hidden" name="jumlah" value="<?= $jumlah ?>">

                <div>
                    <label for="metode" class="block text-gray-200 mb-1 font-medium">Metode Pembayaran</label>
                    <select name="metode_pembayaran" id="metode" required class="w-full px-3 py-2 rounded border border-gray-600 bg-gray-800 text-white focus:outline-none focus:ring focus:ring-green-200">
                        <option value="">-- Pilih Metode --</option>
                        <option value="Transfer Bank">Transfer Bank</option>
                        <option value="e-Wallet">e-Wallet</option>
                        <option value="COD">Bayar di Tempat (COD)</option>
                    </select>
                </div>
                <button type="submit" name="konfirmasi" class="w-full bg-gradient-to-r from-green-500 to-blue-500 hover:from-green-600 hover:to-blue-600 text-white font-semibold px-6 py-2 rounded-lg shadow transition">
                    Konfirmasi & Verifikasi Pembayaran
                </button>
            </form>
        </div>
        <div class="mt-8 text-center">
            <a href="javascript:history.back()" class="inline-block bg-white text-green-700 font-semibold px-6 py-2 rounded-full shadow hover:bg-green-50 transition">← Kembali</a>
        </div>
    </div>

    <footer class="mt-16">
        <?php include __DIR__ . '/../components/footer.php'; ?>
    </footer>
</body>
</html>