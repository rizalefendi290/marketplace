<?php
require __DIR__ . '/../../config/database.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Form Pengajuan Dana Usaha</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">

  <?php include 'components/header.php'; ?>
  <div class="p-4 sm:ml-64 mt-20">
    <div class="max-w-3xl mx-auto bg-white border border-green-600 rounded-xl shadow-lg p-8">
      <h2 class="text-2xl font-bold text-center text-green-700 mb-6">Form Pengajuan Dana Usaha</h2>

      <!-- Persyaratan -->
      <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded mb-6 text-sm">
        <h3 class="font-bold text-lg mb-2">Persyaratan Dokumen Pengajuan Dana:</h3>
        <ul class="list-disc list-inside space-y-1">
          <li><strong>Scan Fotokopi KTP dan KK</strong> – Untuk verifikasi identitas dan alamat pemohon.</li>
          <li><strong>Surat Keterangan Usaha (SKU) atau NIB</strong> – Bukti legalitas usaha.</li>
          <li><strong>Fotokopi Rekening Bank</strong> – Rekening atas nama pemohon untuk pencairan dana.</li>
          <li><strong>Dokumentasi Usaha</strong> – Foto tempat usaha atau produk yang dijual.</li>
        </ul>
      </div>
      <form action="/marketplace/controllers/handle_pengajuan.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Nama & Alamat -->
        <div class="col-span-1">
          <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
          <input type="text" name="nama" required class="input-field border-b-2 border-black bg-transparent text-sm text-gray-900 focus:outline-none focus:ring-0 focus:border-green-600">
        </div>
        <div class="col-span-1">
          <label class="block text-sm font-medium text-gray-700">Alamat</label>
          <input type="text" name="alamat" required class="input-field border-b-2 border-black bg-transparent text-sm text-gray-900 focus:outline-none focus:ring-0 focus:border-green-600">
        </div>

        <!-- NIK & Jenis Kelamin -->
        <div class="col-span-1">
          <label class="block text-sm font-medium text-gray-700">NIK</label>
          <input type="text" name="nik" required class="input-field border-b-2 border-black bg-transparent text-sm text-gray-900 focus:outline-none focus:ring-0 focus:border-green-600">
        </div>
        <div class="col-span-1">
          <label class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
          <select name="jenis_kelamin" required class="input-field border-b-2 border-black bg-transparent text-sm text-gray-900 focus:outline-none focus:ring-0 focus:border-green-600">
            <option value="">Pilih</option>
            <option value="Laki-laki">Laki-laki</option>
            <option value="Perempuan">Perempuan</option>
          </select>
        </div>

        <!-- No HP & Email -->
        <div class="col-span-1">
          <label class="block text-sm font-medium text-gray-700">No Telepon</label>
          <input type="text" name="no_telepon" required class="input-field border-b-2 border-black bg-transparent text-sm text-gray-900 focus:outline-none focus:ring-0 focus:border-green-600">
        </div>
        <div class="col-span-1">
          <label class="block text-sm font-medium text-gray-700">Email</label>
          <input type="email" name="email" required class="input-field border-b-2 border-black bg-transparent text-sm text-gray-900 focus:outline-none focus:ring-0 focus:border-green-600">
        </div>

        <!-- Usaha -->
        <div class="col-span-1">
          <label class="block text-sm font-medium text-gray-700">Nama Usaha</label>
          <input type="text" name="nama_usaha" required class="input-field border-b-2 border-black bg-transparent text-sm text-gray-900 focus:outline-none focus:ring-0 focus:border-green-600">
        </div>
        <div class="col-span-1">
          <label class="block text-sm font-medium text-gray-700">Alamat Usaha</label>
          <input type="text" name="alamat_usaha" required class="input-field border-b-2 border-black bg-transparent text-sm text-gray-900 focus:outline-none focus:ring-0 focus:border-green-600">
        </div>

        <div class="col-span-1">
          <label class="block text-sm font-medium text-gray-700">Status Usaha</label>
          <select name="status_usaha" required class="input-field border-b-2 border-black bg-transparent text-sm text-gray-900 focus:outline-none focus:ring-0 focus:border-green-600">
            <option value="">Pilih</option>
            <option value="Milik Sendiri">Milik Sendiri</option>
            <option value="Sewa">Sewa</option>
            <option value="Dipinjamkan">Dipinjamkan</option>
          </select>
        </div>
        <div class="col-span-1">
          <label class="block text-sm font-medium text-gray-700">Legalitas Usaha</label>
          <textarea name="legalitas_usaha" placeholder="NIB, SIUP, NPWP" rows="2" class="input-field resize-none border-b-2 border-black"></textarea>
        </div>

        <!-- Rekening -->
        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700">Nomor Rekening Bank</label>
          <input type="text" name="no_rekening" required class="input-field border-b-2 border-black bg-transparent text-sm text-gray-900 focus:outline-none focus:ring-0 focus:border-green-600">
        </div>

        <!-- Dokumen Upload -->
        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700">Scan KTP & KK</label>
          <input type="file" name="ktp_kk" accept="image/*,application/pdf" required class="input-upload border-b-2 border-black bg-transparent text-sm text-gray-900 focus:outline-none focus:ring-0 focus:border-green-600">
        </div>

        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700">SKU / NIB</label>
          <input type="file" name="sku_nib" accept="image/*,application/pdf" required class="input-upload border-b-2 border-black bg-transparent text-sm text-gray-900 focus:outline-none focus:ring-0 focus:border-green-600">
        </div>

        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700">Rekening Bank (Foto / PDF)</label>
          <input type="file" name="rekening_bank" accept="image/*,application/pdf" required class="input-upload border-b-2 border-black bg-transparent text-sm text-gray-900 focus:outline-none focus:ring-0 focus:border-green-600">
        </div>

        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700">Dokumentasi Usaha</label>
          <input type="file" name="dokumentasi" accept="image/*,application/pdf" required class="input-upload border-b-2 border-black bg-transparent text-sm text-gray-900 focus:outline-none focus:ring-0 focus:border-green-600">
        </div>

        <!-- Submit -->
        <div class="col-span-2">
          <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold">Kirim Pengajuan</button>
        </div>
      </form>

    </div>
  </div>

  <!-- Tambahkan Style Tailwind -->
  <style>
    .input-field {
      @apply w-full bg-transparent border-0 border-b-2 border-black text-sm text-gray-900 focus:outline-none focus:ring-0 focus:border-green-600;
    }

    .input-upload {
      @apply block w-full text-sm text-gray-900 border-b-2 border-gray-300 bg-transparent file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-green-600 file:text-white hover:file:bg-green-700;
    }
  </style>

</body>

</html>