<?php
require __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin_toko') {
    header("Location: /marketplace/index.php?page=login");
    exit;
}

// PAGINATION SETUP
$perPage = 5;
$page = isset($_GET['page_num']) && is_numeric($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
$start = ($page - 1) * $perPage;

// Hitung total data
$countQuery = "
    SELECT COUNT(*) as total
    FROM transaksi t
    JOIN users u ON t.user_id = u.id
    JOIN transaksi_detail td ON t.id = td.transaksi_id
    JOIN barang b ON td.barang_id = b.id
";
$stmt = $pdo->prepare($countQuery);
$stmt->execute();
$totalData = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalData / $perPage);

// Ambil data transaksi dengan LIMIT dan OFFSET
$query = "
    SELECT 
        t.id,
        u.nama AS nama_pelanggan,
        b.nama_barang AS produk,
        td.jumlah,
        td.harga_satuan,
        (td.jumlah * td.harga_satuan) AS total,
        t.tanggal_transaksi
    FROM transaksi t
    JOIN users u ON t.user_id = u.id
    JOIN transaksi_detail td ON t.id = td.transaksi_id
    JOIN barang b ON td.barang_id = b.id
    ORDER BY t.tanggal_transaksi DESC
    LIMIT $perPage OFFSET $start
";
$stmt = $pdo->prepare($query);
$stmt->execute();
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total keseluruhan halaman ini
$totalSeluruh = array_sum(array_column($transaksi, 'total'));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Transaksi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

<?php include __DIR__ . '/components/header.php'; ?>

<div class="p-4 sm:ml-64">
    <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg mt-14 bg-white">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Riwayat Transaksi</h1>

        <!-- Filter dan Search -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium">Pilih Tanggal</label>
                <input type="date" class="border rounded px-2 py-1" />
                <span>-</span>
                <input type="date" class="border rounded px-2 py-1" />
                <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-1 rounded ml-2">Tampilkan Hasil</button>
            </div>
            <div class="flex items-center gap-2">
                <input type="text" class="border rounded px-3 py-1" placeholder="Cari..." />
            </div>
        </div>

        <!-- Tabel -->
        <div class="overflow-x-auto rounded-xl shadow">
            <table class="min-w-full text-sm text-left border">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="p-3 border-b">No</th>
                        <th class="p-3 border-b">Nama Pelanggan</th>
                        <th class="p-3 border-b">Produk</th>
                        <th class="p-3 border-b">Jumlah</th>
                        <th class="p-3 border-b">Harga Satuan</th>
                        <th class="p-3 border-b">Total</th>
                        <th class="p-3 border-b">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($transaksi) > 0): ?>
                        <?php $no = $start + 1; foreach ($transaksi as $row): ?>
                        <tr class="hover:bg-gray-50 border-b">
                            <td class="p-3"><?= $no++ ?></td>
                            <td class="p-3"><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($row['produk']) ?></td>
                            <td class="p-3"><?= $row['jumlah'] ?></td>
                            <td class="p-3">Rp <?= number_format($row['harga_satuan'], 0, ',', '.') ?></td>
                            <td class="p-3 font-semibold text-green-600">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                            <td class="p-3"><?= date('d/m/Y H:i:s', strtotime($row['tanggal_transaksi'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="p-4 text-center text-gray-500">Tidak ada data transaksi.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 font-semibold text-gray-700">
                        <td colspan="5" class="p-3 text-right">Summary</td>
                        <td class="p-3 text-green-600">Rp <?= number_format($totalSeluruh, 0, ',', '.') ?></td>
                        <td class="p-3"><?= count($transaksi) ?> records</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-between items-center mt-4">
            <div class="text-sm text-gray-600">
                Menampilkan <?= $start + 1 ?> - <?= $start + count($transaksi) ?> dari <?= $totalData ?> data
            </div>
            <div class="flex items-center gap-1">
                <!-- Previous -->
                <a href="/marketplace/index.php?page=admin-transaksi&page_num=<?= max(1, $page - 1) ?>" class="px-2 py-1 border rounded hover:bg-gray-100 <?= $page == 1 ? 'pointer-events-none opacity-50' : '' ?>">&laquo;</a>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="/marketplace/index.php?page=admin-transaksi&page_num=<?= $i ?>" class="px-2 py-1 border rounded <?= $i == $page ? 'bg-gray-200 font-bold' : 'hover:bg-gray-100' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <!-- Next -->
                <a href="/marketplace/index.php?page=admin-transaksi&page_num=<?= min($totalPages, $page + 1) ?>" class="px-2 py-1 border rounded hover:bg-gray-100 <?= $page == $totalPages ? 'pointer-events-none opacity-50' : '' ?>">&raquo;</a>
            </div>
        </div>

    </div>
</div>

</body>
</html>