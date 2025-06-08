<?php
session_start();
$page = $_GET['page'] ?? 'home'; // default ke halaman beranda (home)

// Hanya larang akses jika halaman bukan 'login' atau 'home' dan user belum login
if (!isset($_SESSION['user_id']) && !in_array($page, ['login', 'register', 'home'])) {
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
}