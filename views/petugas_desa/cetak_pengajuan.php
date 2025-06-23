<?php
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan.");
}

$id = (int) $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM pengajuan_dana WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    die("Data tidak ditemukan.");
}

// Format tanggal
$tanggal = date('d F Y', strtotime($data['tanggal_pengajuan']));

// HTML Template
$html = '
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    h2 { text-align: center; margin-bottom: 20px; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
    td { padding: 5px; vertical-align: top; }
    .label { width: 180px; }
    .footer { text-align: right; margin-top: 50px; }
</style>

<h2>FORMULIR PENDAFTARAN BANTUAN USAHA MIKRO</h2>
<table>
    <tr><td class="label">1. Nama</td><td>: ' . htmlspecialchars($data['nama']) . '</td></tr>
    <tr><td class="label">2. NIK</td><td>: ' . htmlspecialchars($data['nik']) . '</td></tr>
    <tr><td class="label">3. No. SKU/NIB/IUMK</td><td>: ' . htmlspecialchars($data['legalitas_usaha']) . '</td></tr>
    <tr><td class="label">4. Alamat Lengkap</td><td>: ' . htmlspecialchars($data['alamat']) . '</td></tr>
    <tr><td class="label">5. Alamat Usaha</td><td>: ' . htmlspecialchars($data['alamat_usaha']) . '</td></tr>
    <tr><td class="label">6. Bidang/Jenis Usaha</td><td>: ' . htmlspecialchars($data['nama_usaha']) . '</td></tr>
    <tr><td class="label">7. No. HP/WA</td><td>: ' . htmlspecialchars($data['no_telepon']) . '</td></tr>
    <tr><td class="label">8. No. Rekening</td><td>: ' . htmlspecialchars($data['no_rekening']) . '</td></tr>
    <tr><td class="label">9. Asset (Rp.)</td><td>: ........................................</td></tr>
    <tr><td class="label">10. Omset/Tahun (Rp.)</td><td>: ........................................</td></tr>
    <tr><td class="label">11. Email</td><td>: ' . htmlspecialchars($data['email']) . '</td></tr>
    <tr><td class="label">12. NPWP (jika memiliki)</td><td>: ........................................</td></tr>
</table>

<div class="footer">
    Tulungagung, ' . $tanggal . '<br><br>
    Yang bersangkutan,<br><br><br>
    <b>' . htmlspecialchars($data['nama']) . '</b>
</div>
';

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("formulir_pengajuan_" . $data['id'] . ".pdf", ["Attachment" => false]);
exit;
