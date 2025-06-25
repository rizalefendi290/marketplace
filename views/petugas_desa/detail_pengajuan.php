<?php
require __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'petugas_desa') {
    die("Akses ditolak.");
}

if (!isset($_GET['id'])) {
    die("ID pengajuan tidak ditemukan.");
}

$id = (int) $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM pengajuan_dana WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    die("Data tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Detail Pengajuan Dana</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">

    <?php include __DIR__ . '/../components/navbar_petugas.php'; ?>

    <div class="p-4 sm:ml-64">
        <div class="max-w-5xl mx-auto bg-white rounded-xl shadow p-6 border border-green-600 mt-12">
            <h2 class="text-2xl font-bold text-green-700 mb-6 text-center">Detail Pengajuan Dana Usaha</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                <div><strong>Nama Lengkap:</strong> <?= htmlspecialchars($data['nama']) ?></div>
                <div><strong>Alamat:</strong> <?= htmlspecialchars($data['alamat']) ?></div>
                <div><strong>NIK:</strong> <?= htmlspecialchars($data['nik']) ?></div>
                <div><strong>Jenis Kelamin:</strong> <?= htmlspecialchars($data['jenis_kelamin']) ?></div>
                <div><strong>No Telepon:</strong> <?= htmlspecialchars($data['no_telepon']) ?></div>
                <div><strong>Email:</strong> <?= htmlspecialchars($data['email']) ?></div>
                <div><strong>Nama Usaha:</strong> <?= htmlspecialchars($data['nama_usaha']) ?></div>
                <div><strong>Alamat Usaha:</strong> <?= htmlspecialchars($data['alamat_usaha']) ?></div>
                <div><strong>Status Usaha:</strong> <?= htmlspecialchars($data['status_usaha']) ?></div>
                <div class="md:col-span-2"><strong>Legalitas Usaha:</strong> <?= nl2br(htmlspecialchars($data['legalitas_usaha'])) ?></div>
                <div><strong>Nomor Rekening:</strong> <?= htmlspecialchars($data['no_rekening']) ?></div>
                <div><strong>Tanggal Pengajuan:</strong> <?= date('d-m-Y', strtotime($data['tanggal_pengajuan'])) ?></div>

                <div><strong>Status:</strong>
                    <span class="inline-block px-2 py-1 rounded text-xs font-semibold
                        <?= $data['status'] === 'Disetujui' ? 'bg-green-100 text-green-700' :
                            ($data['status'] === 'Diproses' ? 'bg-yellow-100 text-yellow-700' :
                            ($data['status'] === 'Ditolak' ? 'bg-red-100 text-red-700' : 'bg-gray-200 text-gray-700')) ?>">
                        <?= $data['status'] ?>
                    </span>
                </div>

                <!-- Dokumen -->
                <div>
                    <strong>Dokumentasi Usaha:</strong><br>
                    <?php if ($data['file_bukti']): ?>
                        <a href="/marketplace/uploads/pengajuan/<?= $data['file_bukti'] ?>" target="_blank" class="text-blue-600 underline">Lihat</a>
                    <?php else: ?>
                        <span class="text-gray-500">Tidak ada</span>
                    <?php endif; ?>
                </div>

                <div>
                    <strong>File KTP & KK:</strong><br>
                    <?php if ($data['file_ktp_kk']): ?>
                        <a href="/marketplace/uploads/pengajuan/<?= $data['file_ktp_kk'] ?>" target="_blank" class="text-blue-600 underline">Lihat</a>
                    <?php else: ?>
                        <span class="text-gray-500">Tidak ada</span>
                    <?php endif; ?>
                </div>

                <div>
                    <strong>File SKU / NIB:</strong><br>
                    <?php if ($data['file_sku_nib']): ?>
                        <a href="/marketplace/uploads/pengajuan/<?= $data['file_sku_nib'] ?>" target="_blank" class="text-blue-600 underline">Lihat</a>
                    <?php else: ?>
                        <span class="text-gray-500">Tidak ada</span>
                    <?php endif; ?>
                </div>

                <div>
                    <strong>File Rekening Bank:</strong><br>
                    <?php if ($data['file_rekening_bank']): ?>
                        <a href="/marketplace/uploads/pengajuan/<?= $data['file_rekening_bank'] ?>" target="_blank" class="text-blue-600 underline">Lihat</a>
                    <?php else: ?>
                        <span class="text-gray-500">Tidak ada</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex justify-between mt-8">
                <a href="index.php?page=petugas-dashboard" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">← Kembali</a>
                <a href="index.php?page=cetak-pengajuan&id=<?= $data['id'] ?>" target="_blank" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">Cetak PDF</a>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../components/footer_petugas.php'; ?>
</body>
</html>
