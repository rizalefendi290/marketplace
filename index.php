<?php
session_start();
$page = $_GET['page'] ?? 'home'; // default ke halaman beranda (home)

// Hanya larang akses jika halaman bukan 'login' atau 'home' dan user belum login
if (!isset($_SESSION['user_id']) && !in_array($page, ['login', 'register', 'home', 'detail-barang'])) {
  header("Location: index.php?page=login");
  exit;
}

// Routing
switch ($page) {
  case 'admin-dashboard':
    include 'views/admin/admin_dashboard.php';
    break;

  case 'admin-produk':
    include 'views/admin/semua_produk.php';
    break;

  case 'admin-transaksi':
    include 'views/admin/semua_transaksi.php';
    break;

  case 'edit-produk':
    include 'views/admin/edit_produk.php';
    break;

  case 'hapus-produk':
    include 'views/admin/hapus_produk.php';
    break;

  case 'edit-toko':
    include 'views/admin/edit_toko.php';
    break;

  case 'verifikasi-transaksi':
    include 'views/admin/verifikasi_transaksi.php';
    break;

  case 'customer-dashboard':
    include 'views/home.php';
    break;

  case 'petugas-dashboard':
    include 'views/petugas/petugas_dashboard.php';
    break;

  case 'login':
    include 'views/login.php';
    break;

  case 'register':
    include 'views/register.php';
    break;

  case 'home': // Ini halaman beranda
    include 'views/home.php';
    break;

  case 'checkout':
    include 'views/customer/checkout.php';
    break;

  default:
    echo "404 - Halaman tidak ditemukan.";
    break;

  case 'detail-barang':
    include 'views/customer/detail_barang.php';
    break;

  case 'riwayat-pembelian':
    include 'views/customer/riwayat_pembelian.php';
    break;

  case 'detail-pembelian':
    include 'views/customer/detail_pembelian.php';
    break;

  case 'edit-profile':
    include 'views/customer/edit_profile.php';
    break;

  case 'keranjang-belanja':
    // Pastikan user sudah login dan customer
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
      header("Location: /marketplace/index.php?page=login");
      exit;
    }
    include 'views/customer/keranjang_belanja.php';
    break;

  case 'hapus-keranjang':
    // Hapus item dari keranjang
    if (
      isset($_SESSION['user_id'], $_GET['id']) &&
      $_SESSION['role'] === 'customer'
    ) {
      require_once __DIR__ . '/config/database.php';
      $keranjang_id = (int)$_GET['id'];
      $user_id = $_SESSION['user_id'];
      // Pastikan hanya user terkait yang bisa hapus
      $stmt = $pdo->prepare("DELETE FROM keranjang WHERE id = ? AND user_id = ?");
      $stmt->execute([$keranjang_id, $user_id]);
    }
    header("Location: /marketplace/index.php?page=keranjang");
    exit;

  case 'proses-ulasan':
    require_once __DIR__ . '/config/database.php';
    if (
      isset($_SESSION['user_id'], $_POST['barang_id'], $_POST['rating'], $_POST['komentar'])
      && $_SESSION['role'] === 'customer'
    ) {
      $barang_id = (int)$_POST['barang_id'];
      $user_id = $_SESSION['user_id'];
      $rating = (int)$_POST['rating'];
      $komentar = trim($_POST['komentar']);

      // Ambil toko_id dari barang
      $stmt = $pdo->prepare("SELECT toko_id FROM barang WHERE id = ?");
      $stmt->execute([$barang_id]);
      $toko_id = $stmt->fetchColumn();

      if ($toko_id) {
        // Cek apakah user sudah pernah mengulas produk ini
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM ulasan WHERE barang_id = ? AND user_id = ?");
        $stmt->execute([$barang_id, $user_id]);
        if ($stmt->fetchColumn() == 0) {
          $stmt = $pdo->prepare("INSERT INTO ulasan (barang_id, toko_id, user_id, rating, komentar, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
          $stmt->execute([$barang_id, $toko_id, $user_id, $rating, $komentar]);
        }
        // Redirect kembali ke detail barang
        header("Location: /marketplace/index.php?page=detail-barang&id=" . $barang_id);
        exit;
      } else {
        // Barang tidak ditemukan
        header("Location: /marketplace/index.php?page=home");
        exit;
      }
    } else {
      header("Location: /marketplace/index.php?page=home");
      exit;
    }
    break;
}
