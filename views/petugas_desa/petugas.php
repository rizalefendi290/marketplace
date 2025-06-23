<?php
require __DIR__ . '/../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'petugas') {
    header("Location: ../index.php?page=login");
    exit;
}

$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Petugas Desa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php include '../components/header.php'; ?>

    <div class="max-w-6xl mx-auto px-4 py-10">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Dashboard Petugas Desa</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <a href="laporan_form.php" class="bg-white border border-green-500 rounded-xl shadow-md p-6 hover:shadow-lg transition">
                <h2 class="text-xl font-semibold text-green-700 mb-2">Ajukan Laporan Dana</h2>
                <p class="text-gray-600">Buat dan kirim pengajuan dana kegiatan atau pembangunan desa.</p>
            </a>

            <a href="laporan_list.php" class="bg-white border border-blue-500 rounded-xl shadow-md p-6 hover:shadow-lg transition">
                <h2 class="text-xl font-semibold text-blue-700 mb-2">Riwayat Pengajuan</h2>
                <p class="text-gray-600">Lihat semua laporan pengajuan dana yang telah diajukan.</p>
            </a>

            <a href="laporan_export.php" class="bg-white border border-gray-500 rounded-xl shadow-md p-6 hover:shadow-lg transition">
                <h2 class="text-xl font-semibold text-gray-700 mb-2">Export Laporan</h2>
                <p class="text-gray-600">Cetak atau ekspor laporan pengajuan dalam format PDF.</p>
            </a>
        </div>
    </div>

    <footer class="mt-20">
        <?php include '../components/footer.php'; ?>
    </footer>
</body>
</html>
