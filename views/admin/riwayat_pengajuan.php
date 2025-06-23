<?php
require __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin_toko') {
    die("Akses ditolak.");
}

// Ambil semua riwayat pengajuan dari semua user
$stmt = $pdo->query("SELECT pd.*, u.username 
                     FROM pengajuan_dana pd 
                     JOIN users u ON pd.user_id = u.id 
                     ORDER BY pd.tanggal_pengajuan DESC");
$pengajuanList = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Pengajuan Dana | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php include 'components/header.php'; ?>

    <div class="p-4 sm:ml-64 mt-6">
        <div class="max-w-7xl mx-auto bg-white border border-green-600 rounded-xl shadow-md p-6 mt-10">
            <h1 class="text-2xl font-bold text-green-700 mb-6">Riwayat Pengajuan Dana</h1>

            <?php if (count($pengajuanList) === 0): ?>
                <p class="text-gray-500">Belum ada pengajuan dana.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300 text-sm">
                        <thead class="bg-green-600 text-white">
                            <tr>
                                <th class="px-4 py-2 border">No</th>
                                <th class="px-4 py-2 border">Nama</th>
                                <th class="px-4 py-2 border">Usaha</th>
                                <th class="px-4 py-2 border">Tanggal</th>
                                <th class="px-4 py-2 border">Status</th>
                                <th class="px-4 py-2 border">Dokumen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pengajuanList as $i => $p): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border text-center"><?= $i + 1 ?></td>
                                    <td class="px-4 py-2 border"><?= htmlspecialchars($p['nama']) ?></td>
                                    <td class="px-4 py-2 border"><?= htmlspecialchars($p['nama_usaha']) ?></td>
                                    <td class="px-4 py-2 border text-center"><?= htmlspecialchars($p['tanggal_pengajuan']) ?></td>
                                    <td class="px-4 py-2 border text-center">
                                        <?php if ($p['status'] === 'Menunggu'): ?>
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-medium">Menunggu</span>
                                        <?php elseif ($p['status'] === 'Disetujui'): ?>
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">Disetujui</span>
                                        <?php else: ?>
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-medium">Ditolak</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-2 border text-center">
                                        <?php if (!empty($p['file_bukti'])): ?>
                                            <a href="/marketplace/uploads/pengajuan/<?= htmlspecialchars($p['file_bukti']) ?>" target="_blank" class="text-blue-600 underline">Lihat</a>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
