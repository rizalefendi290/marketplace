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

// Ambil semua toko untuk grid toko pilihan
$stmtToko = $pdo->query("SELECT id, nama_toko, logo FROM toko ORDER BY id DESC");
$tokoList = $stmtToko->fetchAll();

// Jika ada pencarian
$produk_list = [];
$toko_list = [];
if (isset($_GET['q']) && trim($_GET['q']) !== '') {
    $q = '%' . trim($_GET['q']) . '%';
    // Cari produk
    $stmt = $pdo->prepare("SELECT b.*, t.nama_toko FROM barang b JOIN toko t ON b.toko_id = t.id WHERE b.nama_barang LIKE ? OR t.nama_toko LIKE ? ORDER BY b.id DESC");
    $stmt->execute([$q, $q]);
    $produk_list = $stmt->fetchAll();

    // Cari toko
    $stmt = $pdo->prepare("SELECT id, nama_toko, logo FROM toko WHERE nama_toko LIKE ? ORDER BY id DESC");
    $stmt->execute([$q]);
    $toko_list = $stmt->fetchAll();
}

// Ambil semua kategori untuk grid kategori
$stmtKategori = $pdo->query("SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori ASC");
$kategoriList = $stmtKategori->fetchAll();
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
        <a href="#unggulan" class="inline-block bg-white text-black font-semibold px-8 py-3 rounded-full shadow hover:bg-green-50 transition">Lihat Barang Unggulan</a>
      </div>
    </div>

        <div class="max-w-6xl mx-auto px-4 py-12">
      <h2 class="text-2xl font-bold text-yellow-400 mb-8 text-center">Kategori Produk</h2>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-6">
        <?php if ($kategoriList): ?>
          <?php foreach ($kategoriList as $kategori): ?>
            <a href="index.php?page=kategori&id=<?= $kategori['id'] ?>" class="block bg-gray-900 rounded-xl shadow hover:shadow-lg transition p-6 text-center border border-yellow-300 hover:bg-gray-700">
              <span class="text-lg font-semibold text-yellow-300"><?= htmlspecialchars($kategori['nama_kategori']) ?></span>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-span-full text-center text-gray-400 py-8">Belum ada kategori terdaftar.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Barang Unggulan -->
    <div id="unggulan" class="max-w-6xl mx-auto px-4 py-12">
      <h2 class="text-3xl font-bold text-yellow-400 mb-10 text-center tracking-wide">Barang Unggulan</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
        <?php foreach ($barangUnggulan as $barang): ?>
          <?php
          // Ambil rata-rata rating dan jumlah ulasan produk
          $stmtUlasan = $pdo->prepare("SELECT AVG(rating) as rata2, COUNT(*) as total FROM ulasan WHERE barang_id = ?");
          $stmtUlasan->execute([$barang['id']]);
          $ulasan = $stmtUlasan->fetch();
          $rata2 = $ulasan['rata2'] ? round($ulasan['rata2'], 1) : '-';
          $total_ulasan = $ulasan['total'];

          // Ambil jumlah terjual (asumsi tabel transaksi_detail dan status transaksi 'selesai')
          $stmtTerjual = $pdo->prepare("
            SELECT SUM(td.jumlah) as terjual
            FROM transaksi_detail td
            JOIN transaksi t ON td.transaksi_id = t.id
            WHERE td.barang_id = ? AND t.status = 'selesai'
        ");
          $stmtTerjual->execute([$barang['id']]);
          $terjual = $stmtTerjual->fetchColumn();
          if (!$terjual) $terjual = 0;
          ?>
          <div class="bg-gray-900 rounded-2xl shadow-xl hover:shadow-2xl transition p-4 flex flex-col items-center border border-yellow-300 group relative overflow-hidden">
            <!-- Ribbon -->
            <div class="absolute left-0 top-0 bg-gradient-to-r from-green-500 to-blue-500 text-yellow-400 text-xs px-3 py-1 rounded-br-2xl font-bold shadow group-hover:scale-105 transition">Unggulan</div>
            <a href="index.php?page=detail-barang&id=<?= $barang['id'] ?>">
              <img class="rounded-xl w-full h-40 object-cover mb-3 border border-gray-700 group-hover:scale-105 transition" src="uploads/<?= htmlspecialchars($barang['gambar']) ?>" alt="<?= htmlspecialchars($barang['nama_barang']) ?>" />
            </a>
            <div class="flex-1 w-full">
              <a href="index.php?page=detail-barang&id=<?= $barang['id'] ?>">
                <h5 class="mb-2 text-lg font-bold tracking-tight text-yellow-400 hover:text-yellow-50 transition"><?= htmlspecialchars($barang['nama_barang']) ?></h5>
              </a>
              <p class="mb-1 text-sm text-gray-400">Toko: <span class="font-semibold text-yellow-400"><?= htmlspecialchars($barang['nama_toko']) ?></span></p>
              <div class="flex items-center gap-3 mb-1">
                <span class="text-yellow-400 flex items-center">
                  <svg class="w-4 h-4 mr-1 inline" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.175c.969 0 1.371 1.24.588 1.81l-3.38 2.455a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.38-2.454a1 1 0 00-1.175 0l-3.38 2.454c-.784.57-1.838-.196-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118L2.05 9.394c-.783-.57-.38-1.81.588-1.81h4.175a1 1 0 00.95-.69l1.286-3.967z" />
                  </svg>
                  <?= $rata2 ?>
                </span>
                <span class="text-gray-400 text-xs">(<?= $total_ulasan ?> ulasan)</span>
                <span class="text-yellow-400 text-xs ml-auto"><?= $terjual ?> terjual</span>
              </div>
              <p class="mb-3 font-semibold text-yellow-400 text-xl">Rp<?= number_format($barang['harga'], 0, ',', '.') ?></p>
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
        <a href="index.php?page=produk" class="inline-block bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-yellow-400 font-semibold px-10 py-4 rounded-full shadow-lg text-lg transition">Lihat Semua Produk</a>
      </div>
    </div>

    <!-- Grid Toko: Menampilkan semua toko -->
    <div class="max-w-6xl mx-auto px-4 py-12">
      <h2 class="text-2xl font-bold text-yellow-400 mb-8 text-center">Toko Pilihan</h2>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
        <?php if ($tokoList): ?>
          <?php foreach ($tokoList as $toko): ?>
            <?php
            $logo_path = realpath(__DIR__ . '/../uploads/' . $toko['logo']);
            ?>
            <a href="index.php?page=detail-toko&id=<?= $toko['id'] ?>" class="block border border-yellow-300 rounded-lg p-4 bg-gray-900 hover:shadow-lg transition text-center">
              <?php if (!empty($toko['logo']) && $logo_path && file_exists($logo_path)): ?>
                <img src="/marketplace/uploads/<?= htmlspecialchars($toko['logo']) ?>" class="w-16 h-16 object-cover rounded-full mx-auto mb-2" alt="<?= htmlspecialchars($toko['nama_toko']) ?>">
              <?php else: ?>
                <div class="w-16 h-16 flex items-center justify-center bg-gray-200 text-gray-400 rounded-full mx-auto mb-2">No Logo</div>
              <?php endif; ?>
              <div class="font-semibold text-yellow-400"><?= htmlspecialchars($toko['nama_toko']) ?></div>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-span-full text-center text-gray-400 py-8">Belum ada toko terdaftar.</div>
        <?php endif; ?>
      </div>
      <div class="mt-10 text-center">
        <a href="index.php?page=semua-toko" class="inline-block bg-gradient-to-r from-blue-600 to-green-600 hover:from-blue-700 hover:to-green-700 text-yellow-400 font-semibold px-10 py-4 rounded-full shadow-lg text-lg transition">
          Lihat Semua Toko
        </a>
      </div>
    </div>

    <?php if (isset($_GET['q']) && trim($_GET['q']) !== ''): ?>
      <h2 class="text-xl font-bold mb-4">Hasil Pencarian: "<?= htmlspecialchars($_GET['q']) ?>"</h2>
      <h3 class="text-lg font-semibold mt-6 mb-2">Produk</h3>
      <?php if ($produk_list): ?>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
          <?php foreach ($produk_list as $produk): ?>
            <a href="/marketplace/index.php?page=detail-barang&id=<?= $produk['id'] ?>" class="block border rounded-lg p-3 bg-white hover:shadow-lg transition">
              <?php if (!empty($produk['gambar']) && file_exists(__DIR__ . '/../../uploads/' . $produk['gambar'])): ?>
                <img src="/marketplace/uploads/<?= htmlspecialchars($produk['gambar']) ?>" class="w-full h-32 object-cover rounded mb-2" alt="<?= htmlspecialchars($produk['nama_barang']) ?>">
              <?php else: ?>
                <div class="w-full h-32 flex items-center justify-center bg-gray-200 text-gray-400 rounded mb-2">Tidak Ada Gambar</div>
              <?php endif; ?>
              <div class="font-semibold text-gray-800 truncate"><?= htmlspecialchars($produk['nama_barang']) ?></div>
              <div class="text-green-600 font-bold mt-1">Rp<?= number_format($produk['harga'], 0, ',', '.') ?></div>
              <div class="text-xs text-gray-500 mt-1">Toko: <?= htmlspecialchars($produk['nama_toko']) ?></div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="text-gray-400 py-8">Tidak ada produk ditemukan.</div>
      <?php endif; ?>

      <h3 class="text-lg font-semibold mt-8 mb-2">Toko</h3>
      <?php if ($toko_list): ?>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
          <?php foreach ($toko_list as $toko): ?>
            <?php
            $logo_path = realpath(__DIR__ . '/../uploads/' . $toko['logo']);
            ?>
            <a href="/marketplace/index.php?page=detail-toko&id=<?= $toko['id'] ?>" class="block border rounded-lg p-3 bg-white hover:shadow-lg transition text-center">
              <?php if (!empty($toko['logo']) && $logo_path && file_exists($logo_path)): ?>
                <img src="/marketplace/uploads/<?= htmlspecialchars($toko['logo']) ?>" class="w-16 h-16 object-cover rounded-full mx-auto mb-2" alt="<?= htmlspecialchars($toko['nama_toko']) ?>">
              <?php else: ?>
                <div class="w-16 h-16 flex items-center justify-center bg-gray-200 text-gray-400 rounded-full mx-auto mb-2">No Logo</div>
              <?php endif; ?>
              <div class="font-semibold text-gray-800"><?= htmlspecialchars($toko['nama_toko']) ?></div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="text-gray-400 py-8">Tidak ada toko ditemukan.</div>
      <?php endif; ?>
    <?php endif; ?>

    <!-- CTA Section -->
    <div class="max-w-5xl mx-auto mt-20 mb-10 px-4">
      <div class="bg-gray-900 from-green-400 to-blue-500 rounded-2xl shadow-lg p-10 flex flex-col md:flex-row items-center justify-between gap-8">
        <div>
          <h3 class="text-2xl font-bold text-yellow-400 mb-2">Gabung Menjadi Penjual!</h3>
          <p class="text-white mb-4">Punya produk lokal? Daftarkan tokomu dan mulai jualan di Marketplace Desa sekarang juga.</p>
          <a href="https://wa.me/62895347042844?text=Halo%20admin,%20saya%20ingin%20mendaftar%20sebagai%20penjual%20di%20Marketplace%20Desa." target="_blank" class="inline-block bg-white text-black font-semibold px-6 py-2 rounded-full shadow hover:bg-green-50 transition">
            Daftar Sekarang
          </a>
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