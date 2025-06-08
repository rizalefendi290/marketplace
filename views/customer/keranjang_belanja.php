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
                <form method="post" action="/marketplace/index.php?page=checkout" onsubmit="return prepareCheckoutData();">
                    <div class="space-y-6" id="keranjang-list">
                        <?php foreach ($items as $item): ?>
                            <div class="flex items-center gap-4 bg-gray-800 rounded-lg p-4 shadow">
                                <input type="checkbox" name="pilih[]" value="<?= $item['keranjang_id'] ?>" checked class="accent-green-500 pilih-barang">
                                <img src="/marketplace/uploads/<?= htmlspecialchars($item['gambar']) ?>" class="w-20 h-20 object-cover rounded border border-gray-600" alt="">
                                <div class="flex-1">
                                    <div class="text-white font-semibold"><?= htmlspecialchars($item['nama_barang']) ?></div>
                                    <div class="text-gray-400 text-sm">Toko: <?= htmlspecialchars($item['nama_toko']) ?></div>
                                    <div class="text-gray-400 text-sm">Harga: <span class="line-through">Rp<?= number_format($item['harga'] + 5000, 0, ',', '.') ?></span> <span class="text-orange-400 font-bold">Rp<?= number_format($item['harga'], 0, ',', '.') ?></span></div>
                                    <div class="flex items-center mt-2 gap-2">
                                        <label class="text-sm text-gray-300">Jumlah:</label>
                                        <div class="flex items-center border border-gray-600 rounded px-2 py-1">
                                            <button type="button" class="text-white px-1" onclick="ubahJumlah(this, -1)">−</button>
                                            <input type="number" min="1" max="<?= $item['stok'] ?>" value="<?= $item['jumlah'] ?>" class="w-12 bg-transparent text-center text-white jumlah-barang">
                                            <button type="button" class="text-white px-1" onclick="ubahJumlah(this, 1)">+</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-green-400 font-bold subtotal">Subtotal: Rp<?= number_format($item['jumlah'] * $item['harga'], 0, ',', '.') ?></div>
                                    <a href="/marketplace/index.php?page=hapus-keranjang&id=<?= $item['keranjang_id'] ?>" class="text-red-500 hover:underline text-xs mt-2 block">Hapus</a>
                                </div>
                                <!-- Hidden input untuk barang_id dan keranjang_id -->
                                <input type="hidden" class="barang-id" value="<?= $item['barang_id'] ?>">
                                <input type="hidden" class="keranjang-id" value="<?= $item['keranjang_id'] ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Hidden input untuk checkout -->
                    <div id="checkout-hidden-inputs"></div>
                    <div class="flex justify-between items-center bg-gray-800 text-white p-4 mt-6 rounded-lg">
                        <div class="text-lg">
                            Total (<span><?= count($items) ?></span> produk): <span class="text-green-400 font-bold total-nominal">Rp<?= number_format($total, 0, ',', '.') ?></span>
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
    <script>
        // Fungsi tombol +/-
        function ubahJumlah(btn, delta) {
            const input = btn.parentElement.querySelector('input[type=number]');
            let val = parseInt(input.value) || 1;
            const min = parseInt(input.min) || 1;
            const max = parseInt(input.max) || 99;
            val += delta;
            if (val < min) val = min;
            if (val > max) val = max;
            input.value = val;
            updateSubtotal(btn);
            updateTotal();
        }

        // Update subtotal per produk
        function updateSubtotal(btn) {
            const parent = btn.closest('.flex');
            const jumlahInput = parent.querySelector('.jumlah-barang');
            const hargaText = parent.querySelector('.text-orange-400.font-bold');
            const subtotalDiv = parent.querySelector('.subtotal');
            if (!hargaText || !subtotalDiv) return;
            const harga = parseInt(hargaText.textContent.replace(/[^\d]/g, '')) || 0;
            const jumlah = parseInt(jumlahInput.value) || 1;
            const subtotal = harga * jumlah;
            subtotalDiv.textContent = 'Subtotal: Rp' + subtotal.toLocaleString('id-ID');
        }

        // Update total semua produk yang dicentang
        function updateTotal() {
            let total = 0;
            document.querySelectorAll('.pilih-barang:checked').forEach(cb => {
                const parent = cb.closest('.flex');
                const jumlahInput = parent.querySelector('.jumlah-barang');
                const hargaText = parent.querySelector('.text-orange-400.font-bold');
                const harga = parseInt(hargaText.textContent.replace(/[^\d]/g, '')) || 0;
                const jumlah = parseInt(jumlahInput.value) || 1;
                total += harga * jumlah;
            });
            document.querySelectorAll('.total-nominal').forEach(el => {
                el.textContent = 'Rp' + total.toLocaleString('id-ID');
            });
        }

        // Jika jumlah diubah manual, update subtotal & total
        document.querySelectorAll('.jumlah-barang').forEach(input => {
            input.addEventListener('input', function() {
                updateSubtotal(this);
                updateTotal();
            });
        });

        // Jika checkbox dicentang/di-uncheck, update total
        document.querySelectorAll('.pilih-barang').forEach(cb => {
            cb.addEventListener('change', updateTotal);
        });

        // Inisialisasi total saat halaman dimuat
        window.addEventListener('DOMContentLoaded', updateTotal);

        // Saat submit, hanya kirim barang_id & jumlah yang dicentang
        function prepareCheckoutData() {
            const checked = document.querySelectorAll('.pilih-barang:checked');
            const checkoutDiv = document.getElementById('checkout-hidden-inputs');
            checkoutDiv.innerHTML = '';
            if (checked.length === 0) {
                alert('Pilih minimal satu produk untuk checkout!');
                return false;
            }
            checked.forEach(cb => {
                const parent = cb.closest('.flex');
                const barangId = parent.querySelector('.barang-id').value;
                const jumlah = parent.querySelector('.jumlah-barang').value;
                checkoutDiv.innerHTML += `<input type="hidden" name="barang_id[]" value="${barangId}">`;
                checkoutDiv.innerHTML += `<input type="hidden" name="jumlah[]" value="${jumlah}">`;
            });
            return true;
        }
    </script>
</body>
</html>