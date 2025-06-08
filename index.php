<?php
session_start();
$page = $_GET['page'] ?? 'home'; // default ke halaman beranda (home)

if ($page == 'register_admin_toko') {
    include __DIR__ . '/views/admin/register_admin_toko.php';
    exit;
}
// Hanya larang akses jika halaman bukan public dan user belum login
$public_pages = [
  'login', 'register', 'home', 'detail-barang', 'detail-toko',
  'proses-login', 'proses-register', // jika ada proses login/register
  // tambahkan halaman public lain jika perlu
];

// Jika user belum login dan halaman bukan public, redirect ke home
if (!isset($_SESSION['user_id']) && !in_array($page, $public_pages)) {
  header("Location: index.php?page=home");
  exit;
}

// Routing
switch ($page) {
  case 'admin-dashboard':
    include 'views/admin/admin_dashboard.php';
    break;

  case 'tambah-produk':
    // Pastikan hanya admin_toko yang bisa akses
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin_toko') {
      header("Location: /marketplace/index.php?page=home");
      exit;
    }
    include 'views/admin/admin_produk.php';
    break;

  case 'semua-produk':
    // Pastikan hanya admin_toko yang bisa akses
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin_toko') {
      header("Location: /marketplace/index.php?page=home");
      exit;
    }
    include 'views/admin/semua_produk.php';
    break;

  case 'admin-transaksi':
    include 'views/admin/semua_transaksi.php';
    break;

  case 'edit-produk':
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin_toko') {
      header("Location: /marketplace/index.php?page=home");
      exit;
    }
    include 'views/admin/edit_produk.php';
    break;

  case 'hapus-produk':
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin_toko') {
      header("Location: /marketplace/index.php?page=home");
      exit;
    }
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

  case 'home':
    require_once __DIR__ . '/config/database.php';
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    if ($q !== '') {
      // Cari produk dan toko sesuai keyword
      $produk_stmt = $pdo->prepare("SELECT b.*, t.nama_toko FROM barang b JOIN toko t ON b.toko_id = t.id WHERE b.nama_barang LIKE ? OR t.nama_toko LIKE ? ORDER BY b.id DESC");
      $produk_stmt->execute(['%' . $q . '%', '%' . $q . '%']);
      $produk_list = $produk_stmt->fetchAll();

      $toko_stmt = $pdo->prepare("SELECT * FROM toko WHERE nama_toko LIKE ? ORDER BY id DESC");
      $toko_stmt->execute(['%' . $q . '%']);
      $toko_list = $toko_stmt->fetchAll();
    } else {
      // Tampilkan produk terbaru jika tidak ada pencarian
      $produk_stmt = $pdo->query("SELECT b.*, t.nama_toko FROM barang b JOIN toko t ON b.toko_id = t.id ORDER BY b.id DESC LIMIT 20");
      $produk_list = $produk_stmt->fetchAll();

      $toko_stmt = $pdo->query("SELECT * FROM toko ORDER BY id DESC LIMIT 10");
      $toko_list = $toko_stmt->fetchAll();
    }
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
      header("Location: /marketplace/index.php?page=home");
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

  case 'detail-toko':
    include 'views/customer/detail_toko.php';
    break;

  case 'follow-toko':
    require_once __DIR__ . '/config/database.php';
    if (isset($_SESSION['user_id'], $_GET['id']) && $_SESSION['role'] === 'customer') {
      $toko_id = (int)$_GET['id'];
      $user_id = $_SESSION['user_id'];
      // Cek sudah follow atau belum
      $stmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE toko_id = ? AND user_id = ?");
      $stmt->execute([$toko_id, $user_id]);
      if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO followers (toko_id, user_id, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$toko_id, $user_id]);
      }
    }
    header("Location: /marketplace/index.php?page=detail-toko&id=" . ($_GET['id'] ?? ''));
    exit;

  case 'unfollow-toko':
    require_once __DIR__ . '/config/database.php';
    if (isset($_SESSION['user_id'], $_GET['id']) && $_SESSION['role'] === 'customer') {
      $toko_id = (int)$_GET['id'];
      $user_id = $_SESSION['user_id'];
      $stmt = $pdo->prepare("DELETE FROM followers WHERE toko_id = ? AND user_id = ?");
      $stmt->execute([$toko_id, $user_id]);
    }
    header("Location: /marketplace/index.php?page=detail-toko&id=" . ($_GET['id'] ?? ''));
    exit;
}
