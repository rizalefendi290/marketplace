<?php
require __DIR__ . '/../../config/database.php';

// Cek apakah ID produk ada di URL
if (!isset($_GET['id'])) {
    echo "Produk tidak ditemukan.";
    exit;
}

$id = $_GET['id'];

// Ambil detail produk dan nama toko
$stmt = $pdo->prepare("
    SELECT 
        barang.*,
        toko.nama_toko
    FROM barang
    JOIN toko ON barang.toko_id = toko.id
    WHERE barang.id = ?
");
$stmt->execute([$id]);
$produk = $stmt->fetch();

if (!$produk) {
    echo "Produk tidak ditemukan.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($produk['nama_barang']) ?> - Marketplace Desa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black min-h-screen">
    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="max-w-5xl mx-auto px-4 py-12">
        <div class="bg-gray-900 rounded-2xl shadow-xl flex flex-col md:flex-row gap-10 p-8 border border-green-800 relative overflow-hidden">
            <!-- Decorative SVG -->
            <svg class="absolute left-0 top-0 w-40 h-40 opacity-10" viewBox="0 0 200 200" fill="none">
                <circle cx="100" cy="100" r="100" fill="#fff" />
            </svg>
            <svg class="absolute right-0 bottom-0 w-56 h-56 opacity-5" viewBox="0 0 220 220" fill="none">
                <rect width="220" height="220" rx="110" fill="#fff" />
            </svg>
            <div class="md:w-1/2 flex flex-col items-center z-10">
                <?php if ($produk['gambar']): ?>
                    <img src="/marketplace/uploads/<?= htmlspecialchars($produk['gambar']) ?>" alt="<?= htmlspecialchars($produk['nama_barang']) ?>" class="rounded-xl w-full max-w-xs h-64 object-cover border border-gray-700 shadow mb-4 bg-white">
                <?php else: ?>
                    <div class="w-full max-w-xs h-64 flex items-center justify-center bg-gray-800 text-gray-400 rounded-xl border border-gray-700 mb-4">Tidak Ada Gambar</div>
                <?php endif; ?>
            </div>
            <div class="md:w-1/2 flex flex-col justify-center z-10">
                <h1 class="text-3xl font-bold text-white mb-2"><?= htmlspecialchars($produk['nama_barang']) ?></h1>
                <p class="text-green-400 text-2xl font-bold mb-2">Rp<?= number_format($produk['harga'], 0, ',', '.') ?></p>
                <p class="mb-2 text-gray-300"><span class="font-semibold">Stok:</span> <?= $produk['stok'] ?></p>
                <p class="mb-2 text-gray-300"><span class="font-semibold">Toko:</span> <span class="text-green-400"><?= htmlspecialchars($produk['nama_toko']) ?></span></p>
                <div class="mb-4 text-gray-200">
                    <span class="font-semibold">Deskripsi:</span>
                    <div class="mt-1 whitespace-pre-line"><?= nl2br(htmlspecialchars($produk['deskripsi'])) ?></div>
                </div>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
                    <form action="/marketplace/index.php?page=checkout" method="POST" class="mt-4 flex flex-col gap-3">
                        <input type="hidden" name="barang_id" value="<?= $produk['id'] ?>">
                        <label class="text-gray-200">Jumlah:
                            <input type="number" name="jumlah" value="1" min="1" max="<?= $produk['stok'] ?>" class="ml-2 w-20 px-2 py-1 rounded border border-gray-600 bg-gray-800 text-white">
                        </label>
                        <button type="submit" class="bg-gradient-to-r from-green-500 to-blue-500 hover:from-green-600 hover:to-blue-600 text-white font-semibold px-6 py-2 rounded-lg shadow transition">Checkout</button>
                    </form>
                <?php else: ?>
                    <div class="mt-4">
                        <a href="/marketplace/index.php?page=login" class="inline-block bg-white text-green-700 font-semibold px-6 py-2 rounded-full shadow hover:bg-green-50 transition">Login untuk checkout</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="mt-10 text-center">
            <a href="/marketplace/index.php?page=home" class="inline-block bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white font-semibold px-8 py-3 rounded-full shadow-lg text-lg transition">Kembali ke Beranda</a>
        </div>
    </div>

    <footer class="mt-16">
        <?php include __DIR__ . '/../components/footer.php'; ?>
    </footer>
</body>
</html>