<?php
require __DIR__ . '/../../config/database.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: /marketplace/index.php?page=login");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

if (isset($_POST['daftar_toko'])) {
    $nama_toko = trim($_POST['nama_toko']);
    $alamat_toko = trim($_POST['alamat_toko']);
    $deskripsi = trim($_POST['deskripsi']);

    // Cek apakah user sudah punya toko
    $stmt = $pdo->prepare("SELECT id FROM toko WHERE admin_id = ?");
    $stmt->execute([$user_id]);
    if ($stmt->fetch()) {
        $error = "Anda sudah memiliki toko.";
    } elseif ($nama_toko == '' || $alamat_toko == '') {
        $error = "Nama dan alamat toko wajib diisi.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO toko (admin_id, nama_toko, alamat, deskripsi, status) VALUES (?, ?, ?, ?, 'Aktif')");
        $stmt->execute([$user_id, $nama_toko, $alamat_toko, $deskripsi]);
        $success = "Toko berhasil didaftarkan!";
    }

}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Toko - Marketplace Desa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black min-h-screen">
    <?php include __DIR__ . '/../components/header.php'; ?>
    <div class="max-w-xl mx-auto px-4 py-12">
        <h1 class="text-2xl font-bold text-green-400 mb-6 text-center">Daftar Toko</h1>
        <?php if ($success): ?>
            <div class="bg-green-100 text-green-800 px-4 py-3 rounded mb-4"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="bg-red-100 text-red-800 px-4 py-3 rounded mb-4"><?= $error ?></div>
        <?php endif; ?>
        <form method="post" class="bg-white rounded-lg shadow p-6 space-y-4">
            <div>
                <label class="block font-medium mb-1">Nama Toko <span class="text-red-500">*</span></label>
                <input type="text" name="nama_toko" required class="w-full border border-gray-300 rounded px-3 py-2" value="<?= htmlspecialchars($_POST['nama_toko'] ?? '') ?>">
            </div>
            <div>
                <label class="block font-medium mb-1">Alamat Toko <span class="text-red-500">*</span></label>
                <input type="text" name="alamat_toko" required class="w-full border border-gray-300 rounded px-3 py-2" value="<?= htmlspecialchars($_POST['alamat_toko'] ?? '') ?>">
            </div>
            <div>
                <label class="block font-medium mb-1">Deskripsi Toko</label>
                <textarea name="deskripsi" rows="3" class="w-full border border-gray-300 rounded px-3 py-2"><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
            </div>
            <div class="text-center">
                <button type="submit" name="daftar_toko" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-8 py-2 rounded-lg">Daftar Toko</button>
            </div>
        </form>
        <div class="mt-6 text-center">
            <a href="index.php?page=customer-dashboard" class="text-blue-500 hover:underline">Kembali ke Dashboard</a>
        </div>
    </div>
    <footer class="mt-16">
        <?php include __DIR__ . '/../components/footer.php'; ?>
    </footer>
</body>
</html>