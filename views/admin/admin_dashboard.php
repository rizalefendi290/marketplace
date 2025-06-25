<?php
require __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin_toko') {
    header("Location: ../views/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handler update status transaksi
if (isset($_POST['verifikasi']) && isset($_POST['transaksi_id']) && isset($_POST['status'])) {
    $transaksi_id = $_POST['transaksi_id'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE transaksi SET status = ? WHERE id = ?");
    $stmt->execute([$status, $transaksi_id]);
    echo "<script>
    alert('Status transaksi diperbarui.');
    window.location.href='/marketplace/index.php?page=admin-dashboard';
</script>";
    exit;
}

// Ambil data user (pemilik toko)
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$pemilik = $stmt->fetch();
if (!$pemilik) {
    die("User tidak ditemukan.");
}
// Ambil data toko milik admin
$stmt = $pdo->prepare("SELECT * FROM toko WHERE admin_id = ?");
$stmt->execute([$user_id]);
$toko = $stmt->fetch();

if (!$toko) {
    die("Toko belum dibuat.");
}

$toko_id = $toko['id'];

// Ambil data produk toko
$stmt = $pdo->prepare("SELECT * FROM barang WHERE toko_id = ? LIMIT 5");
$stmt->execute([$toko_id]);
$produk = $stmt->fetchAll();
$jumlah_produk = count($produk);

// Ambil data transaksi yang terkait produk toko ini
$stmt = $pdo->prepare("
    SELECT 
        td.*, t.tanggal_transaksi, t.status, b.nama_barang, u.nama AS pembeli
    FROM transaksi_detail td
    JOIN barang b ON td.barang_id = b.id
    JOIN transaksi t ON td.transaksi_id = t.id
    JOIN users u ON t.user_id = u.id
    WHERE b.toko_id = ?
    ORDER BY t.tanggal_transaksi DESC
    LIMIT 5
");
$stmt->execute([$toko_id]);
$transaksi = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin Toko</title>
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css" rel="stylesheet">
    <!-- Flowbite CDN -->
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <!-- Font untuk tampilan lebih profesional -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</head>

<body class="bg-gray-100 font-sans">

    <?php
    include 'views/admin/components/header.php';
    ?>

    <div class="p-4 sm:ml-64">
        <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 mt-14">
            <div class="max-w-7xl mx-auto px-4 py-8">
                <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">Dashboard Admin Toko</h1>
                    <div class="text-gray-600">Halo, Admin</div>
                </div>
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-700">Ringkasan Data</h2>
                    <p class="text-sm text-gray-500">Statistik singkat toko Anda</p>
                </div>

                <!-- Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-6 mb-10">
                    <!-- Card: Total Produk -->
                    <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-sm text-gray-500">Total Produk</h3>
                                <p class="text-3xl font-bold text-indigo-600 mt-1"><?= $jumlah_produk ?> Produk</p>
                            </div>
                            <div class="bg-indigo-100 text-indigo-600 p-2 rounded-full text-4xl">
                                📦
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-sm text-gray-500">Total Transaksi</h3>
                                <p class="text-3xl font-bold text-green-600 mt-1"><?= count($transaksi) ?> Transaksi</p>
                                <?php
                                // Hitung total rupiah dari semua transaksi yang ditampilkan
                                $total_rupiah = 0;
                                foreach ($transaksi as $tr) {
                                    $total_rupiah += $tr['jumlah'] * $tr['harga_satuan'];
                                }
                                ?>
                                <p<span class="font-semibold text-green-700">Rp <?= number_format($total_rupiah, 0, ',', '.') ?></span></p>
                            </div>
                            <div class="bg-green-100 text-green-600 p-2 rounded-full text-4xl">
                                💰
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow-xl rounded-xl p-6 mb-10">
                    <h2 class="text-xl font-semibold text-green-700 mb-4">Transaksi Produk Anda</h2>
                    <?php if (count($transaksi) === 0): ?>
                        <p class="text-gray-500 italic">Belum ada transaksi.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm text-left border border-gray-200 rounded-lg overflow-hidden">
                                <thead class="bg-green-100">
                                    <tr>
                                        <th class="px-4 py-2">Tanggal</th>
                                        <th class="px-4 py-2">Nama Barang</th>
                                        <th class="px-4 py-2">Jumlah</th>
                                        <th class="px-4 py-2">Total Harga</th>
                                        <th class="px-4 py-2">Pembeli</th>
                                        <th class="px-4 py-2">Status</th>
                                        <th class="px-4 py-2">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transaksi as $tr): ?>
                                        <tr class="bg-white border-b hover:bg-green-50">
                                            <td class="px-4 py-2"><?= $tr['tanggal_transaksi'] ?></td>
                                            <td class="px-4 py-2"><?= htmlspecialchars($tr['nama_barang']) ?></td>
                                            <td class="px-4 py-2"><?= $tr['jumlah'] ?></td>
                                            <td class="px-4 py-2">Rp <?= number_format($tr['jumlah'] * $tr['harga_satuan'], 0, ',', '.') ?></td>
                                            <td class="px-4 py-2"><?= htmlspecialchars($tr['pembeli']) ?></td>
                                            <td class="px-4 py-2">
                                                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                                                        <?= $tr['status'] === 'Menunggu Verifikasi' ? 'bg-yellow-100 text-yellow-800' : ($tr['status'] === 'Diverifikasi' ? 'bg-green-100 text-green-800' : ($tr['status'] === 'Ditolak' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) ?>">
                                                    <?= htmlspecialchars($tr['status']) ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-2">
                                                <?php if ($tr['status'] === 'Menunggu Verifikasi'): ?>
                                                    <form method="POST" class="flex gap-2 items-center">
                                                        <input type="hidden" name="transaksi_id" value="<?= $tr['transaksi_id'] ?>">
                                                        <select name="status" class="border rounded px-2 py-1 text-sm">
                                                            <option value="Diverifikasi">Diverifikasi</option>
                                                            <option value="Ditolak">Ditolak</option>
                                                        </select>
                                                        <button type="submit" name="verifikasi" class="bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700 text-sm">Update</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="italic text-gray-400">Selesai</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- Tombol lihat semua transaksi -->
                        <div class="mt-4 flex justify-start">
                            <a href="/marketplace/index.php?page=admin-transaksi" class="inline-block bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-2 rounded-lg shadow transition">
                                Lihat Semua Transaksi
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Produk -->
                <div class="bg-white shadow-xl rounded-xl p-6 mb-8">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-4">
                        <h2 class="text-xl font-semibold text-green-700 mb-2 md:mb-0">Produk Anda (<?= $jumlah_produk ?> Produk)</h2>
                        <a href="/marketplace/index.php?page=tambah-produk" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg shadow transition mb-2 md:mb-0">
                            + Tambah Produk
                        </a>
                    </div>
                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-sm text-left border border-gray-200 rounded-lg">
                            <thead class="bg-green-100">
                                <tr>
                                    <th class="px-4 py-2">Nama</th>
                                    <th class="px-4 py-2">Harga</th>
                                    <th class="px-4 py-2">Stok</th>
                                    <th class="px-4 py-2">Gambar</th>
                                    <th class="px-4 py-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produk as $p): ?>
                                    <tr class="border-b hover:bg-green-50">
                                        <td class="px-4 py-2"><?= htmlspecialchars($p['nama_barang']) ?></td>
                                        <td class="px-4 py-2">Rp <?= number_format($p['harga'], 0, ',', '.') ?></td>
                                        <td class="px-4 py-2"><?= $p['stok'] ?></td>
                                        <td class="px-4 py-2">
                                            <?php if ($p['gambar']): ?>
                                                <img src="/marketplace/uploads/<?= htmlspecialchars($p['gambar']) ?>" class="w-16 h-16 object-cover rounded-lg border" alt="">
                                            <?php else: ?>
                                                <span class="text-gray-400 italic">Tidak ada</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-2">
                                            <a href="/marketplace/index.php?page=edit-produk&id=<?= $p['id'] ?>" class="text-blue-600 hover:underline mr-2">Edit</a>
                                            <a href="/marketplace/index.php?page=hapus-produk&id=<?= $p['id'] ?>" onclick="return confirm('Yakin ingin menghapus produk ini?')" class="text-red-600 hover:underline">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Tombol lihat semua produk -->
                    <div class="mt-4 flex justify-start">
                        <a href="/marketplace/index.php?page=semua-produk" class="inline-block bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-2 rounded-lg shadow transition">
                            Lihat Semua Produk
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>


</body>

<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>

</html>