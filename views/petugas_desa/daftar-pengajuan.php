<?php
require __DIR__ . '/../../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'petugas_desa') {
    die("Akses ditolak.");
}

// Pagination
$limit = 10;
$page = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
$start = ($page - 1) * $limit;

// Ambil total data
$total_stmt = $pdo->query("SELECT COUNT(*) FROM pengajuan_dana");
$total_rows = $total_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// Ambil data pengajuan terbatas
$stmt = $pdo->prepare("SELECT * FROM pengajuan_dana ORDER BY tanggal_pengajuan DESC LIMIT :start, :limit");
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$pengajuan_list = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Daftar Pengajuan Dana</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="p-4 sm:ml-64">
        <div class="p-4 mt-12 border border-green-600 rounded-xl bg-white shadow max-w-7xl mx-auto">
            <h2 class="text-2xl font-bold text-green-700 mb-6">Daftar Pengajuan Dana</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-700 border border-gray-300">
                    <thead class="bg-green-600 text-white">
                        <tr>
                            <th class="px-4 py-3">No</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Nama Usaha</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Dokumentasi</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pengajuan_list) > 0): ?>
                            <?php foreach ($pengajuan_list as $i => $row): ?>
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="px-4 py-2"><?= $start + $i + 1 ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($row['nama']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($row['nama_usaha']) ?></td>
                                    <td class="px-4 py-2"><?= date('d-m-Y', strtotime($row['tanggal_pengajuan'])) ?></td>
                                    <td class="px-4 py-2">
                                        <span class="inline-block px-2 py-1 rounded text-xs font-semibold
                    <?= $row['status'] === 'Disetujui' ? 'bg-green-100 text-green-700' : ($row['status'] === 'Diproses' ? 'bg-yellow-100 text-yellow-700' :
                                        'bg-gray-200 text-gray-700') ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <?php if (!empty($row['file_bukti'])): ?>
                                            <a href="/marketplace/uploads/pengajuan/<?= $row['file_bukti'] ?>" target="_blank" class="text-blue-600 underline">Lihat</a>
                                        <?php else: ?>
                                            <span class="text-gray-500">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-2 space-x-1">
                                        <a href="index.php?page=detail-pengajuan&id=<?= $row['id'] ?>" class="inline-block bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-xs">Detail</a>
                                        <?php if ($row['status'] === 'Menunggu'): ?>
                                            <a href="controllers/proses_verifikasi.php?id=<?= $row['id'] ?>&aksi=proses" class="inline-block bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600 text-xs">Proses</a>
                                            <a href="controllers/proses_verifikasi.php?id=<?= $row['id'] ?>&aksi=setujui" class="inline-block bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 text-xs">Setujui</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-gray-500 py-6">Belum ada pengajuan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center mt-6 space-x-2">
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <a href="index.php?page=daftar-pengajuan&hal=<?= $p ?>"
                        class="px-3 py-1 rounded border <?= $p == $page ? 'bg-green-600 text-white' : 'bg-white text-green-600 border-green-500' ?>">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../components/footer_petugas.php'; ?>
</body>

</html>