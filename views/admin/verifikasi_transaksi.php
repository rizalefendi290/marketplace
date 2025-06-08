<?php
session_start();
require __DIR__ . '/../../config/database.php';

// Hanya admin_toko yang boleh mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin_toko') {
    header("Location: ../../index.php?page=login");
    exit;
}

$admin_id = $_SESSION['user_id'];

// Ambil toko milik admin ini
$stmt = $pdo->prepare("SELECT id FROM toko WHERE admin_id = ?");
$stmt->execute([$admin_id]);
$toko = $stmt->fetch();

if (!$toko) {
    echo "Toko tidak ditemukan.";
    exit;
}

$toko_id = $toko['id'];

// Jika ada request verifikasi
if (isset($_POST['verifikasi'])) {
    $transaksi_id = $_POST['transaksi_id'];
    $status = $_POST['status']; // 'Diverifikasi' atau 'Ditolak'

    $stmt = $pdo->prepare("UPDATE transaksi SET status = ? WHERE id = ?");
    $stmt->execute([$status, $transaksi_id]);

    echo "<script>alert('Status transaksi diperbarui.'); window.location.href='verifikasi_transaksi.php';</script>";
    exit;
}

// Ambil transaksi untuk produk di toko ini
$stmt = $pdo->prepare("
    SELECT 
        t.id AS transaksi_id, t.tanggal_transaksi, t.status, u.nama AS pembeli,
        b.nama_barang, td.jumlah, td.harga_satuan
    FROM transaksi t
    JOIN users u ON t.user_id = u.id
    JOIN transaksi_detail td ON t.id = td.transaksi_id
    JOIN barang b ON td.barang_id = b.id
    WHERE b.toko_id = ?
    ORDER BY t.tanggal_transaksi DESC
");
$stmt->execute([$toko_id]);
$transaksi = $stmt->fetchAll();
?>

<h2>Verifikasi Transaksi</h2>
<hr>

<?php if (empty($transaksi)): ?>
    <p>Tidak ada transaksi.</p>
<?php else: ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Nama Pembeli</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Total Harga</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transaksi as $tr): ?>
                <tr>
                    <td><?= $tr['tanggal_transaksi'] ?></td>
                    <td><?= htmlspecialchars($tr['pembeli']) ?></td>
                    <td><?= htmlspecialchars($tr['nama_barang']) ?></td>
                    <td><?= $tr['jumlah'] ?></td>
                    <td>Rp<?= number_format($tr['jumlah'] * $tr['harga_satuan'], 0, ',', '.') ?></td>
                    <td><?= $tr['status'] ?></td>
                    <td>
                        <?php if ($tr['status'] === 'Menunggu Verifikasi'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="transaksi_id" value="<?= $tr['transaksi_id'] ?>">
                                <select name="status" required>
                                    <option value="Diverifikasi">Diverifikasi</option>
                                    <option value="Ditolak">Ditolak</option>
                                </select>
                                <button type="submit" name="verifikasi">Update</button>
                            </form>
                        <?php else: ?>
                            <em>Selesai</em>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
