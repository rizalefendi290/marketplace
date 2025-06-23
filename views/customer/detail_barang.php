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
        toko.nama_toko,
        toko.id as toko_id,
        toko.logo as logo,
        toko.created_at as toko_created
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

// Ambil 5 produk dari toko yang sama, kecuali produk ini
$stmt = $pdo->prepare("SELECT * FROM barang WHERE toko_id = ? AND id != ? LIMIT 5");
$stmt->execute([$produk['toko_id'], $id]);
$produk_lain = $stmt->fetchAll();

// --- Info toko dinamis ---
// Jumlah produk toko
$stmt = $pdo->prepare("SELECT COUNT(*) FROM barang WHERE toko_id = ?");
$stmt->execute([$produk['toko_id']]);
$jumlah_produk = $stmt->fetchColumn();

// Penilaian rata-rata (misal dari tabel ulasan, jika ada)
$stmt = $pdo->prepare("SELECT AVG(rating) FROM ulasan WHERE toko_id = ?");
$stmt->execute([$produk['toko_id']]);
$penilaian = $stmt->fetchColumn();
$penilaian = $penilaian ? round($penilaian, 2) : '-';

// Jumlah pengikut (misal dari tabel followers, jika ada)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE toko_id = ?");
$stmt->execute([$produk['toko_id']]);
$pengikut = $stmt->fetchColumn();

// Persentase chat dibalas & waktu chat dibalas (dummy jika tidak ada data)
$persen_chat = '81%';
$waktu_chat = 'Hitungan Jam';

// Tanggal bergabung
$tahun_bergabung = $produk['toko_created'] ? date('Y', strtotime($produk['toko_created'])) : '';
$bergabung = $tahun_bergabung ? (date('Y') - $tahun_bergabung) . ' Tahun Lalu' : '-';

// Tambah ke keranjang
if (
    isset($_POST['tambah_keranjang'], $_POST['barang_id'], $_POST['jumlah'])
    && isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'
) {
    $barang_id = (int)$_POST['barang_id'][0];
    $jumlah = (int)$_POST['jumlah'][0];
    $user_id = $_SESSION['user_id'];

    // Cek apakah barang sudah ada di keranjang
    $cek = $pdo->prepare("SELECT id, jumlah FROM keranjang WHERE user_id = ? AND barang_id = ?");
    $cek->execute([$user_id, $barang_id]);
    $row = $cek->fetch();
    if ($row) {
        // Update jumlah
        $new_jumlah = $row['jumlah'] + $jumlah;
        $update = $pdo->prepare("UPDATE keranjang SET jumlah = ? WHERE id = ?");
        $update->execute([$new_jumlah, $row['id']]);
    } else {
        // Insert baru
        $insert = $pdo->prepare("INSERT INTO keranjang (user_id, barang_id, jumlah) VALUES (?, ?, ?)");
        $insert->execute([$user_id, $barang_id, $jumlah]);
    }
    // Redirect agar tidak resubmit
    header("Location: /marketplace/index.php?page=detail-barang&id=" . $barang_id . "&success=keranjang");
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
        <div class="bg-gray-900 rounded-2xl shadow-xl flex flex-col md:flex-row gap-10 p-8 border border-yellow-300 relative overflow-hidden">
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
                <p class="text-yellow-400 text-2xl font-bold mb-2">Rp<?= number_format($produk['harga'], 0, ',', '.') ?></p>
                <p class="mb-2 text-gray-300"><span class="font-semibold">Stok:</span> <?= $produk['stok'] ?></p>
                <div class="mb-4 text-gray-200">
                    <span class="font-semibold">Deskripsi:</span>
                    <div class="mt-1 whitespace-pre-line"><?= nl2br(htmlspecialchars($produk['deskripsi'])) ?></div>
                </div>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
                    <form action="" method="post" class="mt-4 flex flex-col gap-3">
                        <!-- Perbaikan: gunakan array agar checkout tidak error -->
                        <input type="hidden" name="barang_id[]" value="<?= $produk['id'] ?>">
                        <label class="text-gray-200">Jumlah:
                            <input type="number" name="jumlah[]" value="1" min="1" max="<?= $produk['stok'] ?>" class="ml-2 w-20 px-2 py-1 rounded border border-gray-600 bg-gray-800 text-white">
                        </label>
                        <div class="flex gap-2">
                            <button type="submit" name="tambah_keranjang" class="bg-yellow-500 hover:bg-orange-600 text-black font-semibold px-6 py-2 rounded-lg shadow transition">+ Keranjang</button>
                            <button type="submit" formaction="/marketplace/index.php?page=checkout" class="bg-gradient-to-r from-green-500 to-blue-500 hover:from-green-600 hover:to-blue-600 text-white font-semibold px-6 py-2 rounded-lg shadow transition">Checkout</button>
                        </div>
                    </form>
                    <?php if (isset($_GET['success']) && $_GET['success'] === 'keranjang'): ?>
                        <div class="mt-2 text-green-400 font-semibold">Berhasil ditambahkan ke keranjang!</div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="mt-4">
                        <a href="/marketplace/index.php?page=login" class="inline-block bg-white text-green-700 font-semibold px-6 py-2 rounded-full shadow hover:bg-green-50 transition">Login untuk membeli</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="bg-gray-900 border border-yellow-300 p-6 shadow-md rounded-lg mt-10">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-4">
                    <?php if (!empty($produk['logo']) && file_exists(__DIR__ . '/../../uploads/' . $produk['logo'])): ?>
                        <img src="/marketplace/uploads/<?= htmlspecialchars($produk['logo']) ?>" alt="Logo Toko" class="w-12 h-12 rounded-full">
                    <?php else: ?>
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($produk['nama_toko']) ?>&background=10b981&color=fff&size=128" alt="Logo Toko" class="w-12 h-12 rounded-full">
                    <?php endif; ?>
                    <div>
                        <h3 class="text-lg text-white font-semibold"><?= htmlspecialchars($produk['nama_toko']) ?></h3>
                        <p class="text-sm text-gray-400">Aktif 3 menit lalu</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="#" class="bg-yellow-400 hover:bg-yellow-500 text-black px-4 py-2 rounded">Chat Sekarang</a>
                    <a href="/marketplace/index.php?page=detail-toko&id=<?= $produk['toko_id'] ?>" class="border border-yellow-400 text-white px-4 py-2 rounded hover:bg-gray-900">Kunjungi Toko</a>
                </div>
            </div>

            <div class="grid grid-cols-3 md:grid-cols-6 gap-4 text-sm text-gray-700">
                <div>
                    <div class="text-gray-400">Penilaian</div>
                    <div class="font-medium text-gray-400"><?= $penilaian ?></div>
                </div>
                <div>
                    <div class="text-gray-400">Produk</div>
                    <div class="font-medium text-gray-400"><?= $jumlah_produk ?></div>
                </div>
                <div>
                    <div class="text-gray-400">Persentase Chat Dibalas</div>
                    <div class="font-medium text-gray-400"><?= $persen_chat ?></div>
                </div>
                <div>
                    <div class="text-gray-400">Waktu Chat Dibalas</div>
                    <div class="font-medium text-gray-400"><?= $waktu_chat ?></div>
                </div>
                <div>
                    <div class="text-gray-400">Bergabung</div>
                    <div class="font-medium text-gray-400"><?= $bergabung ?></div>
                </div>
                <div>
                    <div class="text-gray-400">Pengikut</div>
                    <div class="font-medium text-gray-400"><?= $pengikut ?></div>
                </div>
            </div>
        </div>

        <div class="bg-gray-900 border border-yellow-300 p-6 shadow-md rounded-lg mt-8">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl text-yellow-400 font-semibold">Produk Lainnya dari Toko Ini</h3>
                <a href="#" class="text-yellow-400 hover:underline text-sm">Lihat Semua</a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
                <?php foreach ($produk_lain as $p): ?>
                    <div class="border border-yellow-400 rounded-xl p-3 bg-gray-900 hover:shadow-lg transition flex flex-col items-center">
                        <a href="/marketplace/index.php?page=detail-barang&id=<?= $p['id'] ?>">
                            <?php if (!empty($p['gambar']) && file_exists(__DIR__ . '/../../uploads/' . $p['gambar'])): ?>
                                <img src="/marketplace/uploads/<?= htmlspecialchars($p['gambar']) ?>" class="w-full h-32 object-cover rounded-lg border mb-2" alt="<?= htmlspecialchars($p['nama_barang']) ?>">
                            <?php else: ?>
                                <div class="w-full h-32 flex items-center justify-center bg-gray-200 text-gray-400 rounded-lg border mb-2">Tidak Ada Gambar</div>
                            <?php endif; ?>
                            <p class="text-sm font-semibold text-yellow-400 truncate"><?= htmlspecialchars($p['nama_barang']) ?></p>
                        </a>
                        <div class="text-yellow-400 font-bold mt-1">Rp<?= number_format($p['harga'], 0, ',', '.') ?></div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($produk_lain)): ?>
                    <div class="col-span-full text-center text-gray-400 py-8">Tidak ada produk lain dari toko ini.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ulasan & Komentar Produk -->
        <div class="bg-gray-900 border border-yellow-300 p-6 shadow-md rounded-lg mt-8">
            <h3 class="text-xl text-yellow-400 font-semibold mb-4">Ulasan & Komentar Produk</h3>
            <?php
            // Ambil ulasan produk
            $stmt = $pdo->prepare("
                SELECT u.rating, u.komentar, u.created_at, us.nama, us.foto_profil
                FROM ulasan u
                JOIN users us ON u.user_id = us.id
                WHERE u.barang_id = ?
                ORDER BY u.created_at DESC
                LIMIT 10
            ");
            $stmt->execute([$produk['id']]);
            $ulasan_produk = $stmt->fetchAll();
            ?>
            <?php if ($ulasan_produk): ?>
                <div class="space-y-6">
                    <?php foreach ($ulasan_produk as $ulasan): ?>
                        <div class="flex items-start gap-3 border-b pb-4">
                            <?php if (!empty($ulasan['foto_profil']) && file_exists(__DIR__ . '/../../uploads/' . $ulasan['foto_profil'])): ?>
                                <img src="/marketplace/uploads/<?= htmlspecialchars($ulasan['foto_profil']) ?>" class="w-10 h-10 rounded-full object-cover border" alt="<?= htmlspecialchars($ulasan['nama']) ?>">
                            <?php else: ?>
                                <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-500 font-bold"><?= strtoupper(substr($ulasan['nama'], 0, 1)) ?></div>
                            <?php endif; ?>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-white"><?= htmlspecialchars($ulasan['nama']) ?></span>
                                    <span class="text-yellow-500 text-xs">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?= $i <= round($ulasan['rating']) ? '★' : '☆' ?>
                                        <?php endfor; ?>
                                    </span>
                                    <span class="text-xs text-gray-400"><?= date('d M Y', strtotime($ulasan['created_at'])) ?></span>
                                </div>
                                <div class="text-white mt-1"><?= nl2br(htmlspecialchars($ulasan['komentar'])) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-yellow-500 text-center py-8">Belum ada ulasan untuk produk ini.</div>
            <?php endif; ?>

            <!-- Form tambah ulasan -->
            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
                <div class="mt-8">
                    <form method="post" action="/marketplace/index.php?page=proses-ulasan" class="space-y-3">
                        <input type="hidden" name="barang_id" value="<?= $produk['id'] ?>">
                        <div>
                            <label class="block text-yellow-500 font-medium mb-1">Rating</label>
                            <select name="rating" required class="rounded border px-3 py-2 bg-gray-100">
                                <option value="">Pilih rating</option>
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <option value="<?= $i ?>"><?= $i ?> ★</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-yellow-500 font-medium mb-1">Komentar</label>
                            <textarea name="komentar" rows="2" required class="w-full rounded border px-3 py-2 bg-gray-100"></textarea>
                        </div>
                        <button type="submit" class="bg-yellow-500 hover:bg-yellow-700 text-black px-6 py-2 rounded font-semibold">Kirim Ulasan</button>
                    </form>
                </div>
            <?php endif; ?>
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