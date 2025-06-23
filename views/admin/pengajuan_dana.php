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

<div class="p-4 sm:ml-64 mt-6">
  <div class="max-w-4xl mx-auto bg-white border border-green-600 rounded-xl shadow-md p-8 relative overflow-hidden">
    
    <!-- Decorative SVG -->
    <svg class="absolute left-0 top-0 w-32 h-32 opacity-10" viewBox="0 0 200 200" fill="none">
      <circle cx="100" cy="100" r="100" fill="#10B981" />
    </svg>

    <h2 class="text-3xl font-bold text-green-700 text-center mb-8">Form Pengajuan Dana Usaha</h2>

    <form action="/marketplace/controllers/handle_pengajuan.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">

      <div>
        <label class="block font-medium text-gray-700 mb-1">Nama Lengkap</label>
        <input type="text" name="nama" required class="input-field">
      </div>

      <div>
        <label class="block font-medium text-gray-700 mb-1">Alamat</label>
        <input type="text" name="alamat" required class="input-field">
      </div>

      <div>
        <label class="block font-medium text-gray-700 mb-1">NIK</label>
        <input type="text" name="nik" required class="input-field">
      </div>

      <div>
        <label class="block font-medium text-gray-700 mb-1">Jenis Kelamin</label>
        <select name="jenis_kelamin" required class="input-field">
          <option value="">Pilih</option>
          <option value="Laki-laki">Laki-laki</option>
          <option value="Perempuan">Perempuan</option>
        </select>
      </div>

      <div>
        <label class="block font-medium text-gray-700 mb-1">No Telepon</label>
        <input type="text" name="no_telepon" required class="input-field">
      </div>

      <div>
        <label class="block font-medium text-gray-700 mb-1">Email</label>
        <input type="email" name="email" required class="input-field">
      </div>

      <div>
        <label class="block font-medium text-gray-700 mb-1">Nama Usaha</label>
        <input type="text" name="nama_usaha" required class="input-field">
      </div>

      <div>
        <label class="block font-medium text-gray-700 mb-1">Alamat Usaha</label>
        <input type="text" name="alamat_usaha" required class="input-field">
      </div>

      <div>
        <label class="block font-medium text-gray-700 mb-1">Status Usaha</label>
        <select name="status_usaha" required class="input-field">
          <option value="">Pilih</option>
          <option value="Milik Sendiri">Milik Sendiri</option>
          <option value="Sewa">Sewa</option>
          <option value="Dipinjamkan">Dipinjamkan</option>
        </select>
      </div>

      <div class="md:col-span-2">
        <label class="block font-medium text-gray-700 mb-1">Legalitas Usaha</label>
        <textarea name="legalitas_usaha" rows="3" placeholder="Izin Usaha, NIB, SIUP, NPWP - jika ada" class="input-field resize-none"></textarea>
      </div>

      <div class="md:col-span-2">
        <label class="block font-medium text-gray-700 mb-1">Nomor Rekening Bank</label>
        <input type="text" name="no_rekening" required class="input-field">
      </div>

      <div class="md:col-span-2">
        <label class="block font-medium text-gray-700 mb-1">Upload Dokumentasi Usaha</label>
        <input type="file" name="dokumentasi" accept="image/*,application/pdf" required class="w-full border rounded border-gray-300 px-3 py-2 bg-white">
      </div>

      <div class="md:col-span-2">
        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg transition">Kirim Pengajuan</button>
      </div>

    </form>
  </div>
</div>

<style>
.input-field {
  @apply w-full px-4 py-2 border rounded border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-400;
}
</style>

</body>
</html>
