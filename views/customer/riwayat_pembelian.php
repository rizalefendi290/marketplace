<?php
require __DIR__ . '/../../config/database.php';

// Pastikan user sudah login dan berperan sebagai customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: /marketplace/index.php?page=login");
    exit;
}

$user_id = $_SESSION['user_id'];

// Proses konfirmasi terima barang
if (isset($_POST['terima_id'])) {
    $terima_id = (int)$_POST['terima_id'];
    $pdo->prepare("UPDATE transaksi SET status = 'Selesai' WHERE id = ? AND user_id = ? AND status = 'Diverifikasi'")->execute([$terima_id, $user_id]);
    echo "<script>window.location.reload();</script>";
    exit;
}

// Proses pembatalan pesanan
if (isset($_POST['batal_id'])) {
    $batal_id = (int)$_POST['batal_id'];
    // Ambil detail transaksi untuk mengembalikan stok
    $stmt = $pdo->prepare("SELECT barang_id, jumlah FROM transaksi_detail WHERE transaksi_id = ?");
    $stmt->execute([$batal_id]);
    $details = $stmt->fetchAll();
    foreach ($details as $d) {
        $pdo->prepare("UPDATE barang SET stok = stok + ? WHERE id = ?")->execute([$d['jumlah'], $d['barang_id']]);
    }
    // Hapus transaksi detail dan transaksi
    $pdo->prepare("DELETE FROM transaksi_detail WHERE transaksi_id = ?")->execute([$batal_id]);
    $pdo->prepare("DELETE FROM transaksi WHERE id = ? AND user_id = ? AND status = 'Menunggu Verifikasi'")->execute([$batal_id, $user_id]);
    echo "<script>window.location.reload();</script>";
    exit;
}

// Ambil riwayat transaksi user
$stmt = $pdo->prepare("
    SELECT t.id, t.tanggal_transaksi, t.status, t.metode_pembayaran,
           b.nama_barang, b.gambar, td.jumlah, td.harga_satuan, t.id as transaksi_id
    FROM transaksi t
    JOIN transaksi_detail td ON t.id = td.transaksi_id
    JOIN barang b ON td.barang_id = b.id
    WHERE t.user_id = ?
    ORDER BY t.tanggal_transaksi DESC
");
$stmt->execute([$user_id]);
$riwayat = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Riwayat Pembelian - Marketplace Desa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-black min-h-screen">
    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="max-w-5xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold text-green-400 mb-8 text-center">Riwayat Pembelian</h1>
        <div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <!-- Filter Status -->
            <form method="get" class="flex gap-2 items-center">
                <input type="hidden" name="page" value="riwayat-pembelian">
                <select name="filter_status" class="rounded px-3 py-2 bg-gray-800 text-white border border-green-700">
                    <option value="">Semua Status</option>
                    <option value="Menunggu Verifikasi" <?= (isset($_GET['filter_status']) && $_GET['filter_status'] == 'Menunggu Verifikasi') ? 'selected' : ''; ?>>Menunggu Verifikasi</option>
                    <option value="Diverifikasi" <?= (isset($_GET['filter_status']) && $_GET['filter_status'] == 'Diverifikasi') ? 'selected' : ''; ?>>Diverifikasi</option>
                    <option value="Selesai" <?= (isset($_GET['filter_status']) && $_GET['filter_status'] == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                    <option value="Ditolak" <?= (isset($_GET['filter_status']) && $_GET['filter_status'] == 'Ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                </select>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Filter</button>
            </form>
            <!-- Search -->
            <form method="get" class="flex gap-2 items-center">
                <input type="hidden" name="page" value="riwayat-pembelian">
                <input type="text" name="q" placeholder="Cari produk..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>" class="rounded px-3 py-2 bg-gray-800 text-white border border-green-700">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Cari</button>
            </form>
        </div>
        <div class="bg-gray-900 rounded-2xl shadow-xl p-8 border border-green-800">
            <?php
            // Filter dan search
            $filtered = [];
            foreach ($riwayat as $row) {
                $statusOk = empty($_GET['filter_status']) || $row['status'] === $_GET['filter_status'];
                $qOk = empty($_GET['q']) || stripos($row['nama_barang'], $_GET['q']) !== false;
                if ($statusOk && $qOk) $filtered[] = $row;
            }
            ?>
            <?php if (count($filtered) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left border">
                        <thead class="bg-green-800 text-white">
                            <tr>
                                <th class="p-3 border-b">Tanggal</th>
                                <th class="p-3 border-b">Produk</th>
                                <th class="p-3 border-b">Jumlah</th>
                                <th class="p-3 border-b">Harga Satuan</th>
                                <th class="p-3 border-b">Total</th>
                                <th class="p-3 border-b">Status</th>
                                <th class="p-3 border-b">Aksi</th>
                                <th class="p-3 border-b">Invoice</th>
                                <th class="p-3 border-b"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filtered as $row): ?>
                                <tr class="hover:bg-gray-800 border-b">
                                    <td class="p-3 text-white"><?= date('d/m/Y H:i', strtotime($row['tanggal_transaksi'])) ?></td>
                                    <td class="p-3 flex items-center gap-2">
                                        <?php if ($row['gambar']): ?>
                                            <img src="/marketplace/uploads/<?= htmlspecialchars($row['gambar']) ?>" class="w-10 h-10 object-cover rounded border" alt="">
                                        <?php endif; ?>
                                        <span class="text-white"><?= htmlspecialchars($row['nama_barang']) ?></span>
                                    </td>
                                    <td class="p-3 text-white"><?= $row['jumlah'] ?></td>
                                    <td class="p-3 text-white">Rp <?= number_format($row['harga_satuan'], 0, ',', '.') ?></td>
                                    <td class="p-3 font-semibold text-green-400">Rp <?= number_format($row['harga_satuan'] * $row['jumlah'], 0, ',', '.') ?></td>
                                    <td class="p-3">
                                        <span class="px-2 py-1 rounded text-xs font-semibold
                                            <?php
                                            if ($row['status'] === 'Menunggu Verifikasi') echo 'bg-yellow-500 text-white';
                                            elseif ($row['status'] === 'Diverifikasi') echo 'bg-green-600 text-white';
                                            elseif ($row['status'] === 'Ditolak') echo 'bg-red-600 text-white';
                                            elseif ($row['status'] === 'Selesai') echo 'bg-blue-600 text-white';
                                            else echo 'bg-gray-600 text-white';
                                            ?>">
                                            <?= htmlspecialchars($row['status']) ?>
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <?php if ($row['status'] === 'Menunggu Verifikasi'): ?>
                                            <form method="POST" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?')">
                                                <input type="hidden" name="batal_id" value="<?= $row['transaksi_id'] ?>">
                                                <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded text-xs hover:bg-red-700">Batalkan</button>
                                            </form>
                                        <?php elseif ($row['status'] === 'Diverifikasi'): ?>
                                            <form method="POST" onsubmit="return confirm('Konfirmasi barang sudah diterima?')">
                                                <input type="hidden" name="terima_id" value="<?= $row['transaksi_id'] ?>">
                                                <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-700">Konfirmasi Terima</button>
                                            </form>
                                        <?php elseif ($row['status'] === 'Selesai'): ?>
                                            <a href="/marketplace/index.php?page=review&barang_id=<?= $row['transaksi_id'] ?>" class="px-3 py-1 bg-green-700 text-white rounded text-xs hover:bg-green-800">Beri Ulasan</a>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3">
                                        <a href="/marketplace/invoice.php?id=<?= $row['transaksi_id'] ?>" target="_blank" class="px-3 py-1 bg-gray-700 text-white rounded text-xs hover:bg-gray-800">Invoice</a>
                                    </td>
                                    <td>
                                        <a href="/marketplace/index.php?page=detail-pembelian&id=<?= $row['transaksi_id'] ?>" class="px-3 py-1 bg-gray-700 text-white rounded text-xs hover:bg-gray-800">Detail</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center text-gray-400 py-12">Belum ada riwayat pembelian.</div>
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