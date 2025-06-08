<?php
require __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin_toko') {
    header("Location: /marketplace/index.php?page=login");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data toko
$stmt = $pdo->prepare("SELECT * FROM toko WHERE admin_id = ?");
$stmt->execute([$user_id]);
$toko = $stmt->fetch();

// Ambil data user (pemilik)
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$pemilik = $stmt->fetch();

if (!$toko) {
    die("Toko tidak ditemukan.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_toko = $_POST['nama_toko'];
    $alamat = $_POST['alamat'];
    $kontak = $_POST['kontak'];
    $nama_pemilik = $_POST['nama_pemilik'];

    // Update toko
    $stmt = $pdo->prepare("UPDATE toko SET nama_toko = ?, alamat = ?, kontak = ? WHERE id = ?");
    $stmt->execute([$nama_toko, $alamat, $kontak, $toko['id']]);

    // Update nama pemilik di tabel users
    $stmt = $pdo->prepare("UPDATE users SET nama = ? WHERE id = ?");
    $stmt->execute([$nama_pemilik, $user_id]);

    echo "<div class='p-4 bg-green-100 text-green-800 rounded mt-4'>
            Data toko berhasil diperbarui. <a href='/marketplace/index.php?page=admin-dashboard' class='underline text-blue-600'>Kembali ke Dashboard</a>
          </div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Informasi Toko</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 p-6">
    <?php include __DIR__ . '/components/header.php'; ?>
    <div class="p-4 sm:ml-64">
        <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg mt-14 bg-white">
            <div class="max-w-2xl mx-auto p-6 bg-white shadow-md mt-8 rounded-lg">
                <h2 class="text-2xl font-bold mb-6 text-center text-green-700">Edit Informasi Toko</h2>
                <form method="POST" class="space-y-5">
                    <div>
                        <label class="block font-medium mb-1">Nama Toko</label>
                        <input type="text" name="nama_toko" value="<?= htmlspecialchars($toko['nama_toko']) ?>" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-green-200">
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Alamat</label>
                        <textarea name="alamat" required rows="3" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-green-200"><?= htmlspecialchars($toko['alamat']) ?></textarea>
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Kontak</label>
                        <input type="text" name="kontak" value="<?= htmlspecialchars($toko['kontak']) ?>" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-green-200">
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Nama Pemilik</label>
                        <input type="text" name="nama_pemilik" value="<?= htmlspecialchars($pemilik['nama']) ?>" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-green-200">
                    </div>
                    <div class="text-center">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-lg transition">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>