<?php
require __DIR__ . '/../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin_toko') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID produk tidak ditemukan.");
}

// Cek apakah produk milik toko admin ini
$stmt = $pdo->prepare("SELECT b.id FROM barang b JOIN toko t ON b.toko_id = t.id WHERE b.id = ? AND t.admin_id = ?");
$stmt->execute([$id, $user_id]);
$produk = $stmt->fetch();

if (!$produk) {
    die("Produk tidak ditemukan atau bukan milik Anda.");
}

// Cek apakah produk ada di transaksi_detail
$stmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi_detail WHERE barang_id = ?");
$stmt->execute([$id]);
$jumlah_transaksi = $stmt->fetchColumn();

if ($jumlah_transaksi > 0) {
    // Jika ada transaksi, tampilkan popup error
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal Menghapus',
            text: 'Produk ini tidak dapat dihapus karena sudah memiliki riwayat transaksi.',
        }).then(() => {
            window.location.href = '../../index.php?page=admin-dashboard';
        });
    </script>
    ";
    exit;
}

// Jika tidak ada transaksi, lanjut hapus
$stmt = $pdo->prepare("DELETE FROM barang WHERE id = ?");
$stmt->execute([$id]);

echo "
<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: 'Produk berhasil dihapus.',
    }).then(() => {
        window.location.href = '../../index.php?page=admin-dashboard';
    });
</script>
";
exit;
?>
