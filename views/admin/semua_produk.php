<?php
require __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin_toko') {
    header("Location: /marketplace/index.php?page=login");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil id toko milik admin
$stmt = $pdo->prepare("SELECT id FROM toko WHERE admin_id = ?");
$stmt->execute([$user_id]);
$toko = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$toko) {
    die("Toko tidak ditemukan.");
}

$toko_id = $toko['id'];

// Pagination
$perPage = 10;
$page = isset($_GET['page_num']) && is_numeric($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
$start = ($page - 1) * $perPage;

// Hitung total produk
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM barang WHERE toko_id = ?");
$stmt->execute([$toko_id]);
$totalData = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalData / $perPage);

// Ambil data produk
$stmt = $pdo->prepare("SELECT * FROM barang WHERE toko_id = ? ORDER BY id DESC LIMIT $perPage OFFSET $start");
$stmt->execute([$toko_id]);
$produk = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Semua Produk</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 font-sans">
    <?php include __DIR__ . '/components/header.php'; ?>

    <div class="p-4 sm:ml-64">
        <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg mt-14 bg-white">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Daftar Semua Produk</h1>
            <div class="overflow-x-auto rounded-xl shadow">
                <table class="min-w-full text-sm text-left border">
                    <thead class="bg-green-100 text-gray-700">
                        <tr>
                            <th class="p-3 border-b">No</th>
                            <th class="p-3 border-b">Nama Barang</th>
                            <th class="p-3 border-b">Deskripsi</th>
                            <th class="p-3 border-b">Harga</th>
                            <th class="p-3 border-b">Stok</th>
                            <th class="p-3 border-b">Gambar</th>
                            <th class="p-3 border-b">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($produk) > 0): ?>
                            <?php $no = $start + 1;
                            foreach ($produk as $p): ?>
                                <tr class="hover:bg-gray-50 border-b">
                                    <td class="p-3"><?= $no++ ?></td>
                                    <td class="p-3"><?= htmlspecialchars($p['nama_barang']) ?></td>
                                    <td class="p-3"><?= htmlspecialchars($p['deskripsi']) ?></td>
                                    <td class="p-3">Rp <?= number_format($p['harga'], 0, ',', '.') ?></td>
                                    <td class="p-3"><?= $p['stok'] ?></td>
                                    <td class="p-3">
                                        <?php if ($p['gambar']): ?>
                                            <img src="/marketplace/uploads/<?= htmlspecialchars($p['gambar']) ?>" class="w-16 h-16 object-cover rounded border" alt="">
                                        <?php else: ?>
                                            <span class="text-gray-400 italic">Tidak ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3">
                                        <a href="/marketplace/index.php?page=edit-produk&id=<?= $p['id'] ?>" class="text-blue-600 hover:underline mr-2">Edit</a>
                                        <a href="/marketplace/index.php?page=hapus-produk&id=<?= $p['id'] ?>" onclick="return confirm('Yakin ingin menghapus produk ini?')" class="text-red-600 hover:underline">Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="p-4 text-center text-gray-500">Tidak ada produk.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-4">
                <div class="text-sm text-gray-600">
                    Menampilkan <?= $start + 1 ?> - <?= $start + count($produk) ?> dari <?= $totalData ?> produk
                </div>
                <div class="flex items-center gap-1">
                    <a href="?page_num=<?= max(1, $page - 1) ?>" class="px-2 py-1 border rounded hover:bg-gray-100 <?= $page == 1 ? 'pointer-events-none opacity-50' : '' ?>">&laquo;</a>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page_num=<?= $i ?>" class="px-2 py-1 border rounded <?= $i == $page ? 'bg-green-200 font-bold' : 'hover:bg-gray-100' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    <a href="?page_num=<?= min($totalPages, $page + 1) ?>" class="px-2 py-1 border rounded hover:bg-gray-100 <?= $page == $totalPages ? 'pointer-events-none opacity-50' : '' ?>">&raquo;</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>