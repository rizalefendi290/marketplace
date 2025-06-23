<?php
require __DIR__ . '/../../config/database.php';

// Cek login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: /marketplace/index.php?page=home");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data user dari database
$stmt = $pdo->prepare("SELECT nama, alamat, nomor_hp FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Jika form ubah alamat/nama disubmit
if (isset($_POST['ubah_alamat'])) {
    $nama_baru = $_POST['nama_pengiriman'];
    $alamat_baru = $_POST['alamat_pengiriman'];
    $nomor_hp_baru = $_POST['nomor_hp_pengiriman'];

    $stmt = $pdo->prepare("UPDATE users SET nama = ?, alamat = ?, nomor_hp = ? WHERE id = ?");
    $stmt->execute([$nama_baru, $alamat_baru, $nomor_hp_baru, $user_id]);

    // Update session juga jika perlu
    $_SESSION['nama'] = $nama_baru;
    $_SESSION['alamat'] = $alamat_baru;
    $_SESSION['nomor_hp'] = $nomor_hp_baru;

    // Refresh data user
    $user['nama'] = $nama_baru;
    $user['alamat'] = $alamat_baru;
    $user['nomor_hp'] = $nomor_hp_baru;
}

// Ambil data produk dari keranjang (multi-produk)
if (!isset($_POST['barang_id']) || !isset($_POST['jumlah'])) {
    echo "Data tidak lengkap.";
    exit;
}

$barang_ids = $_POST['barang_id'];
$jumlahs = $_POST['jumlah'];

if (!is_array($barang_ids) || !is_array($jumlahs) || count($barang_ids) == 0) {
    echo "Data tidak lengkap.";
    exit;
}

// Ambil semua produk yang dipilih
$placeholders = implode(',', array_fill(0, count($barang_ids), '?'));
$stmt = $pdo->prepare("SELECT b.*, t.nama_toko FROM barang b JOIN toko t ON b.toko_id = t.id WHERE b.id IN ($placeholders)");
$stmt->execute($barang_ids);
$barangs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($barangs) == 0) {
    echo "Produk tidak ditemukan.";
    exit;
}

// Buat array jumlah per barang_id
$jumlah_map = [];
foreach ($barang_ids as $idx => $bid) {
    $jumlah_map[$bid] = (int)$jumlahs[$idx];
}

// Hitung total
$total_harga = 0;
foreach ($barangs as &$barang) {
    $barang['jumlah'] = $jumlah_map[$barang['id']] ?? 1;
    $barang['subtotal'] = $barang['jumlah'] * $barang['harga'];
    $total_harga += $barang['subtotal'];
}
unset($barang);

$ongkir = 3500;
$proteksi = isset($_POST['proteksi']) ? 500 : 0;
$total_pembayaran = $total_harga + $ongkir + $proteksi;

// Proses checkout
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

    // Simpan detail & update stok
    foreach ($barangs as $barang) {
        $stmt = $pdo->prepare("INSERT INTO transaksi_detail (transaksi_id, barang_id, jumlah, harga_satuan) VALUES (?, ?, ?, ?)");
        $stmt->execute([$transaksi_id, $barang['id'], $barang['jumlah'], $barang['harga']]);
        $stmt = $pdo->prepare("UPDATE barang SET stok = stok - ? WHERE id = ?");
        $stmt->execute([$barang['jumlah'], $barang['id']]);
    }

    echo "<script>alert('Pesanan berhasil dibuat! Menunggu verifikasi pembayaran.'); window.location.href='index.php?page=customer-dashboard';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Checkout | Marketplace Desa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-black min-h-screen">
    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="max-w-2xl mx-auto px-4 py-12">
        <header class="bg-gray-900 border border-yellow-400 shadow px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold text-yellow-500">Checkout</h1>
        </header>

        <main class="max-w-5xl mx-auto mt-6 space-y-6">

            <!-- Alamat Pengiriman -->
            <section class="bg-gray-900 border border-yellow-300 p-6 rounded-lg shadow">
                <h2 class="text-lg text-yellow-500 font-semibold mb-4">📍 Alamat Pengiriman</h2>
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-medium text-white">
                            <?= htmlspecialchars($user['nama'] ?? 'Nama belum diatur') ?>
                            <span class="text-white">(<?= htmlspecialchars($user['nomor_hp'] ?? '-') ?>)</span>
                        </p>
                        <p class="text-white text-sm">
                            <?= htmlspecialchars($user['alamat'] ?? 'Alamat belum diatur.') ?>
                        </p>
                    </div>
                    <!-- Tombol Ubah -->
                    <button onclick="document.getElementById('modal-ubah-alamat').classList.remove('hidden')" class="text-yellow-500 font-semibold hover:underline">Ubah</button>
                </div>
                <div class="mt-3">
                    <label class="inline-flex items-center space-x-2">
                        <input type="checkbox" class="accent-red-500" name="dropshipper" disabled>
                        <span class="text-sm text-gray-700">Kirim sebagai Dropshipper</span>
                    </label>
                </div>
            </section>

            <!-- Modal Ubah Alamat -->
            <div id="modal-ubah-alamat" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
                    <button onclick="document.getElementById('modal-ubah-alamat').classList.add('hidden')" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
                    <h2 class="text-xl font-bold mb-4 text-center">Ubah Alamat Pengiriman</h2>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block font-medium mb-1">Nama Penerima</label>
                            <input type="text" name="nama_pengiriman" value="<?= htmlspecialchars($user['nama'] ?? '') ?>" required class="w-full border border-gray-300 rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block font-medium mb-1">Nomor HP</label>
                            <input type="text" name="nomor_hp_pengiriman" value="<?= htmlspecialchars($user['nomor_hp'] ?? '') ?>" required class="w-full border border-gray-300 rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block font-medium mb-1">Alamat Lengkap</label>
                            <textarea name="alamat_pengiriman" required rows="3" class="w-full border border-gray-300 rounded px-3 py-2"><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
                        </div>
                        <div class="text-center">
                            <button type="submit" name="ubah_alamat" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-lg transition">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Produk Dipesan -->
            <section class="bg-gray-900 border border-yellow-300 p-6 rounded-lg shadow">
                <h2 class="text-lg text-yellow-500 font-semibold mb-4">🛒 Produk Dipesan</h2>
                <div class="space-y-4">
                    <?php foreach ($barangs as $barang): ?>
                    <div class="border-t pt-4">
                        <div class="flex justify-between items-center">
                            <div class="space-y-1">
                                <p class="font-semibold text-white"><?= htmlspecialchars($barang['nama_toko']) ?></p>
                                <div class="flex items-center space-x-2 text-sm text-white">
                                    <?php if ($barang['gambar']): ?>
                                        <img src="/marketplace/uploads/<?= htmlspecialchars($barang['gambar']) ?>" class="w-12 h-12 object-cover rounded border" alt="">
                                    <?php else: ?>
                                        <div class="w-12 h-12 flex items-center justify-center bg-gray-200 text-white rounded border">-</div>
                                    <?php endif; ?>
                                    <div>
                                        <p><?= htmlspecialchars($barang['nama_barang']) ?></p>
                                        <p class="text-xs">Jumlah: <?= $barang['jumlah'] ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right text-yellow-500">
                                <p class="text-sm">Harga Satuan</p>
                                <p class="font-semibold">Rp<?= number_format($barang['harga'], 0, ',', '.') ?></p>
                                <p class="text-sm mt-2">Subtotal: Rp<?= number_format($barang['subtotal'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <!-- Proteksi -->
                    <div class="mt-4 text-sm">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" class="accent-red-500" name="proteksi" form="checkout-form" value="1" <?= isset($_POST['proteksi']) ? 'checked' : '' ?>>
                            <span class="text-gray-400">Proteksi Kerusakan + <span class="text-gray-100">(Rp500)</span></span>
                        </label>
                        <p class="text-xs text-gray-50">Melindungi produkmu dari kerusakan/kerugian total selama 6 bulan.</p>
                    </div>
                </div>
            </section>

            <!-- Total & Bayar -->
            <section class="bg-gray-900 border border-yellow-300 p-6 rounded-lg shadow text-right space-y-4">
                <div class="text-lg text-white font-semibold">
                    Total Pembayaran: <span class="text-yellow-500">Rp<?= number_format($total_pembayaran, 0, ',', '.') ?></span>
                </div>
                <form id="checkout-form" method="POST" class="space-y-5 z-10 relative">
                    <?php foreach ($barangs as $barang): ?>
                        <input type="hidden" name="barang_id[]" value="<?= $barang['id'] ?>">
                        <input type="hidden" name="jumlah[]" value="<?= $barang['jumlah'] ?>">
                    <?php endforeach; ?>
                    <div>
                        <label for="metode" class="block text-gray-50 mb-1 font-medium">Metode Pembayaran</label>
                        <select name="metode_pembayaran" id="metode" required class="w-full px-3 py-2 rounded border border-yellow-300 bg-gray-900 text-yellow-500 focus:outline-none focus:ring focus:ring-green-200">
                            <option value="">-- Pilih Metode --</option>
                            <option value="Transfer Bank">Transfer Bank</option>
                            <option value="e-Wallet">e-Wallet</option>
                            <option value="COD">Bayar di Tempat (COD)</option>
                        </select>
                    </div>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" class="accent-red-500" name="proteksi" value="1" <?= isset($_POST['proteksi']) ? 'checked' : '' ?>>
                        <span class="text-gray-50">Proteksi Kerusakan + <span class="text-gray-50">(Rp500)</span></span>
                    </label>
                    <button type="submit" name="konfirmasi" class="w-full bg-gradient-to-r from-green-500 to-blue-500 hover:from-green-600 hover:to-blue-600 text-yellow-500 font-semibold px-6 py-2 rounded-lg shadow transition">
                        Konfirmasi & Verifikasi Pembayaran
                    </button>
                </form>
            </section>
            <div class="mt-8 text-center">
                <a href="javascript:history.back()" class="inline-block bg-gray-900 text-yellow-500 border border-yellow-300 font-semibold px-6 py-2 rounded-full shadow hover:bg-yellow-600 hover:text-black transition">← Kembali</a>
            </div>
        </main>
    </div>

    <footer class="mt-16">
        <?php include __DIR__ . '/../components/footer.php'; ?>
    </footer>
    <script>
        // Tutup modal jika klik di luar modal
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('modal-ubah-alamat');
            if (modal && !modal.classList.contains('hidden') && !modal.contains(e.target) && e.target.closest('.bg-white') === null) {
                modal.classList.add('hidden');
            }
        });
    </script>
</body>
</html>