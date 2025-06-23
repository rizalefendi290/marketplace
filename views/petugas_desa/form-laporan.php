<?php
require '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pengaju = $_POST['nama_pengaju'];
    $judul_laporan = $_POST['judul_laporan'];
    $jumlah_dana = $_POST['jumlah_dana'];
    $keperluan = $_POST['keperluan'];

    $stmt = $pdo->prepare("INSERT INTO laporan_dana (nama_pengaju, judul_laporan, jumlah_dana, keperluan, tanggal) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$nama_pengaju, $judul_laporan, $jumlah_dana, $keperluan]);

    echo "<script>alert('Laporan berhasil disimpan!'); location.href='form-laporan.php';</script>";
    exit;
}

require 'vendor/autoload.php'; // pastikan dompdf sudah diinstal via composer

use Dompdf\Dompdf;

$stmt = $pdo->query("SELECT * FROM laporan_dana ORDER BY tanggal DESC");
$data = $stmt->fetchAll();

$html = '<h2 style="text-align:center;">Laporan Pengajuan Dana Desa</h2><table border="1" cellspacing="0" cellpadding="8" width="100%">';
$html .= '<tr><th>Nama Pengaju</th><th>Judul</th><th>Jumlah</th><th>Keperluan</th><th>Tanggal</th></tr>';

foreach ($data as $row) {
    $html .= "<tr>
        <td>{$row['nama_pengaju']}</td>
        <td>{$row['judul_laporan']}</td>
        <td>Rp" . number_format($row['jumlah_dana'], 0, ',', '.') . "</td>
        <td>{$row['keperluan']}</td>
        <td>{$row['tanggal']}</td>
    </tr>";
}
$html .= '</table>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("laporan_dana_desa.pdf", array("Attachment" => false));

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Form Laporan Dana Desa</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
  <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-4 text-center text-green-700">Form Laporan Pengajuan Dana Desa</h2>
    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-gray-700 font-medium mb-1">Nama Pengaju</label>
        <input type="text" name="nama_pengaju" required class="w-full border px-3 py-2 rounded" />
      </div>
      <div>
        <label class="block text-gray-700 font-medium mb-1">Judul Laporan</label>
        <input type="text" name="judul_laporan" required class="w-full border px-3 py-2 rounded" />
      </div>
      <div>
        <label class="block text-gray-700 font-medium mb-1">Jumlah Dana (Rp)</label>
        <input type="number" name="jumlah_dana" required class="w-full border px-3 py-2 rounded" />
      </div>
      <div>
        <label class="block text-gray-700 font-medium mb-1">Keperluan</label>
        <textarea name="keperluan" rows="4" class="w-full border px-3 py-2 rounded"></textarea>
      </div>
      <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">Simpan Laporan</button>
    </form>
    <div class="mt-6 text-center">
      <a href="cetak-pdf.php" target="_blank" class="text-blue-600 hover:underline">Cetak Laporan PDF</a>
    </div>
  </div>
</body>
</html>
