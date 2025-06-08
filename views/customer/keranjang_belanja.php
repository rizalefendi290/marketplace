<?php
require __DIR__ . '/../../config/database.php';

// Pastikan user sudah login sebagai customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: /marketplace/index.php?page=login");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data keranjang user
$stmt = $pdo->prepare("
    SELECT k.id as keranjang_id, k.jumlah, b.id as barang_id, b.nama_barang, b.harga, b.gambar, t.nama_toko, b.stok
    FROM keranjang k
    JOIN barang b ON k.barang_id = b.id
    JOIN toko t ON b.toko_id = t.id
    WHERE k.user_id = ?
");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();

// Hitung total
$total = 0;
foreach ($items as $item) {
    $total += $item['jumlah'] * $item['harga'];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Keranjang Belanja - Marketplace Desa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-black min-h-screen">
    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="max-w-4xl mx-auto px-4 py-12">
        <h1 class="text-2xl font-bold text-green-400 mb-6 text-center">Keranjang Belanja</h1>
        <div class="bg-gray-900 rounded-2xl shadow-xl p-8 border border-green-800 mb-8">
            <?php if (empty($items)): ?>
                <div class="text-center text-gray-400 py-12">Keranjang belanja Anda kosong.</div>
            <?php else: ?>
                <form method="post" action="/marketplace/index.php?page=checkout">
                    <div class="space-y-6">
                        <?php foreach ($items as $item): ?>
                            <div class="flex items-center gap-4 bg-gray-800 rounded-lg p-4 shadow">
                                <input type="checkbox" checked class="accent-green-500">
                                <img src="/marketplace/uploads/<?= htmlspecialchars($item['gambar']) ?>" class="w-20 h-20 object-cover rounded border border-gray-600" alt="">
                                <div class="flex-1">
                                    <div class="text-white font-semibold"><?= htmlspecialchars($item['nama_barang']) ?></div>
                                    <div class="text-gray-400 text-sm">Variasi: <span class="italic">Default</span></div>
                                    <div class="text-gray-400 text-sm">Harga: <span class="line-through">Rp<?= number_format($item['harga'] + 5000, 0, ',', '.') ?></span> <span class="text-orange-400 font-bold">Rp<?= number_format($item['harga'], 0, ',', '.') ?></span></div>
                                    <div class="flex items-center mt-2 gap-2">
                                        <label class="text-sm text-gray-300">Jumlah:</label>
                                        <div class="flex items-center border border-gray-600 rounded px-2 py-1">
                                            <button type="button" class="text-white px-1">−</button>
                                            <input type="number" name="jumlah[<?= $item['keranjang_id'] ?>]" value="<?= $item['jumlah'] ?>" min="1" max="<?= $item['stok'] ?>" class="w-12 bg-transparent text-center text-white">
                                            <button type="button" class="text-white px-1">+</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-green-400 font-bold">Subtotal: Rp<?= number_format($item['jumlah'] * $item['harga'], 0, ',', '.') ?></div>
                                    <a href="/marketplace/index.php?page=hapus-keranjang&id=<?= $item['keranjang_id'] ?>" class="text-red-500 hover:underline text-xs mt-2 block">Hapus</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="flex justify-between items-center bg-gray-800 text-white p-4 mt-6 rounded-lg">
                        <div class="text-lg">
                            Total (<span><?= count($items) ?></span> produk): <span class="text-green-400 font-bold">Rp<?= number_format($total, 0, ',', '.') ?></span>
                        </div>
                        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold px-8 py-3 rounded-full shadow">Checkout</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        <div class="text-center">
            <a href="/marketplace/index.php?page=home" class="inline-block bg-white text-green-700 font-semibold px-6 py-2 rounded-full shadow hover:bg-green-50 transition">Kembali ke Beranda</a>
        </div>
    </div>

    <footer class="mt-16">
        <?php include __DIR__ . '/../components/footer.php'; ?>
    </footer>
</body>

</html>