<?php
require __DIR__ . '/../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'petugas_desa') {
    header("Location: ../login.php");
    exit;
}

$stmt = $pdo->query("
    SELECT p.*, u.username 
    FROM pengajuan_dana p 
    JOIN users u ON p.admin_id = u.id 
    ORDER BY p.created_at DESC
");
$pengajuan = $stmt->fetchAll();
?>

<div class="max-w-6xl mx-auto mt-12 bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-bold mb-6">Daftar Pengajuan Dana</h2>
    <table class="w-full table-auto border border-collapse">
        <thead class="bg-gray-100">
            <tr>
                <th class="border px-4 py-2">Judul</th>
                <th class="border px-4 py-2">Deskripsi</th>
                <th class="border px-4 py-2">Diajukan Oleh</th>
                <th class="border px-4 py-2">Tanggal</th>
                <th class="border px-4 py-2">Status</th>
                <th class="border px-4 py-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pengajuan as $item): ?>
                <tr>
                    <td class="border px-4 py-2"><?= htmlspecialchars($item['judul']) ?></td>
                    <td class="border px-4 py-2"><?= htmlspecialchars($item['deskripsi']) ?></td>
                    <td class="border px-4 py-2"><?= htmlspecialchars($item['username']) ?></td>
                    <td class="border px-4 py-2"><?= date('d-m-Y', strtotime($item['created_at'])) ?></td>
                    <td class="border px-4 py-2"><?= $item['status'] ?></td>
                    <td class="border px-4 py-2">
                        <a href="/marketplace/uploads/pengajuan/<?= $item['file_path'] ?>" class="text-blue-600 underline" target="_blank">Lihat PDF</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
