<?php
require 'config/database.php';

// Ambil 4 barang terbaru beserta nama toko dan gambar
$stmt = $pdo->query("
    SELECT b.*, t.nama_toko 
    FROM barang b 
    JOIN toko t ON b.toko_id = t.id 
    ORDER BY b.id DESC 
    LIMIT 4
");
$barangUnggulan = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Marketplace Desa</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-black min-h-screen">

  <?php include 'components/header.php'; ?>

  <div class="relative">
    <!-- Hero Section -->
    <div class="w-full bg-gradient-to-r from-green-400 to-blue-500 py-20 mb-10 shadow-lg relative overflow-hidden">
      <!-- Decorative SVGs -->
      <svg class="absolute left-0 top-0 w-48 h-48 opacity-20" viewBox="0 0 200 200" fill="none">
        <circle cx="100" cy="100" r="100" fill="#fff" />
      </svg>
      <svg class="absolute right-0 bottom-0 w-56 h-56 opacity-10" viewBox="0 0 220 220" fill="none">
        <rect width="220" height="220" rx="110" fill="#fff" />
      </svg>
      <div class="max-w-4xl mx-auto px-4 text-center relative z-10">
        <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4 drop-shadow-lg">Selamat Datang di <span class="text-yellow-300">Marketplace Desa</span></h1>
        <p class="text-lg md:text-xl text-blue-50 mb-8">Temukan produk UMKM terbaik dari desa Anda, langsung dari pelaku usaha lokal!</p>
        <a href="#unggulan" class="inline-block bg-white text-green-700 font-semibold px-8 py-3 rounded-full shadow hover:bg-green-50 transition">Lihat Barang Unggulan</a>
      </div>
    </div>

    <!-- Barang Unggulan -->
    <div id="unggulan" class="max-w-6xl mx-auto px-4 py-12">
      <h2 class="text-3xl font-bold text-green-400 mb-10 text-center tracking-wide">Barang Unggulan</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
        <?php foreach ($barangUnggulan as $barang): ?>
          <div class="bg-gray-900 rounded-2xl shadow-xl hover:shadow-2xl transition p-4 flex flex-col items-center border border-green-800 group relative overflow-hidden">
            <!-- Ribbon -->
            <div class="absolute left-0 top-0 bg-gradient-to-r from-green-500 to-blue-500 text-white text-xs px-3 py-1 rounded-br-2xl font-bold shadow group-hover:scale-105 transition">Unggulan</div>
            <a href="index.php?page=detail-barang&id=<?= $barang['id'] ?>">
              <img class="rounded-xl w-full h-40 object-cover mb-3 border border-gray-700 group-hover:scale-105 transition" src="uploads/<?= htmlspecialchars($barang['gambar']) ?>" alt="<?= htmlspecialchars($barang['nama_barang']) ?>" />
            </a>
            <div class="flex-1 w-full">
              <a href="index.php?page=detail-barang&id=<?= $barang['id'] ?>">
                <h5 class="mb-2 text-lg font-bold tracking-tight text-white hover:text-green-400 transition"><?= htmlspecialchars($barang['nama_barang']) ?></h5>
              </a>
              <p class="mb-1 text-sm text-gray-400">Toko: <span class="font-semibold text-green-400"><?= htmlspecialchars($barang['nama_toko']) ?></span></p>
              <p class="mb-3 font-semibold text-green-400 text-xl">Rp<?= number_format($barang['harga'], 0, ',', '.') ?></p>
            </div>
            <a href="index.php?page=detail-barang&id=<?= $barang['id'] ?>" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-green-500 to-blue-500 rounded-lg shadow hover:from-green-600 hover:to-blue-600 focus:ring-4 focus:outline-none focus:ring-green-200 transition">
              Lihat Detail
              <svg class="w-4 h-4 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9" />
              </svg>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="mt-12 text-center">
        <a href="index.php?page=produk" class="inline-block bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white font-semibold px-10 py-4 rounded-full shadow-lg text-lg transition">Lihat Semua Produk</a>
      </div>
    </div>

    <!-- CTA Section -->
    <div class="max-w-5xl mx-auto mt-20 mb-10 px-4">
      <div class="bg-gray-900 from-green-400 to-blue-500 rounded-2xl shadow-lg p-10 flex flex-col md:flex-row items-center justify-between gap-8">
        <div>
          <h3 class="text-2xl font-bold text-white mb-2">Gabung Menjadi Penjual!</h3>
          <p class="text-white mb-4">Punya produk lokal? Daftarkan tokomu dan mulai jualan di Marketplace Desa sekarang juga.</p>
          <a href="index.php?page=register" class="inline-block bg-white text-green-700 font-semibold px-6 py-2 rounded-full shadow hover:bg-green-50 transition">Daftar Sekarang</a>
        </div>
        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Gabung Penjual" class="w-32 h-32 md:w-40 md:h-40 drop-shadow-xl">
      </div>
    </div>
  </div>

  <footer class="mt-16">
    <?php include 'components/footer.php'; ?>
  </footer>

</body>

</html>