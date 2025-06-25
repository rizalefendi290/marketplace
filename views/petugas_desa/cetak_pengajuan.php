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

$tanggal = date('d F Y', strtotime($data['tanggal_pengajuan']));

$html = '
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    h2 { text-align: center; font-weight: bold; margin-bottom: 25px; text-transform: uppercase; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
    td { padding: 6px 4px; vertical-align: top; }
    .label { width: 200px; font-weight: bold; }
    .footer { width: 100%; text-align: right; margin-top: 50px; }
    .ttd { margin-top: 60px; text-align: center; }
</style>

<h2>Formulir Pendaftaran Bantuan Usaha Mikro</h2>

<table>
    <tr><td class="label">1. Nama</td><td>: ' . htmlspecialchars($data['nama']) . '</td></tr>
    <tr><td class="label">2. NIK</td><td>: ' . htmlspecialchars($data['nik']) . '</td></tr>
    <tr><td class="label">3. Legalitas Usaha <br><small>(NIB, SIUP, NPWP jika ada)</small></td><td>: ' . htmlspecialchars($data['legalitas_usaha']) . '</td></tr>
    <tr><td class="label">4. Alamat Lengkap</td><td>: ' . htmlspecialchars($data['alamat']) . '</td></tr>
    <tr><td class="label">5. Alamat Usaha</td><td>: ' . htmlspecialchars($data['alamat_usaha']) . '</td></tr>
    <tr><td class="label">6. Bidang/Jenis Usaha</td><td>: ' . htmlspecialchars($data['nama_usaha']) . '</td></tr>
    <tr><td class="label">7. No. HP/WA</td><td>: ' . htmlspecialchars($data['no_telepon']) . '</td></tr>
    <tr><td class="label">8. No. Rekening</td><td>: ' . htmlspecialchars($data['no_rekening']) . '</td></tr>
    <tr><td class="label">9. Asset (Rp.)</td><td>: _________________________________</td></tr>
    <tr><td class="label">10. Omset/Tahun (Rp.)</td><td>: _________________________________</td></tr>
    <tr><td class="label">11. Email</td><td>: ' . htmlspecialchars($data['email']) . '</td></tr>
</table>

<table style="width: 100%; margin-top: 50px;">
    <tr>
        <td style="width: 60%;"></td>
        <td style="text-align: center;">
            Tulungagung, ' . $tanggal . '<br><br>
            Yang bersangkutan,<br><br><br><br>
            <b>' . htmlspecialchars($data['nama']) . '</b>
        </td>
    </tr>
</table>

';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("formulir_pengajuan_" . $data['id'] . ".pdf", ["Attachment" => false]);
exit;
