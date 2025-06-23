<?php include '../components/header.php'; 

require '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $telepon = $_POST['telepon'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];

    // Cek apakah email sudah terdaftar
    $cek = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $cek->execute([$email]);
    if ($cek->fetch()) {
        echo "<script>alert('Email sudah terdaftar!'); history.back();</script>";
        exit;
    }

    // Simpan data ke DB
    $stmt = $pdo->prepare("INSERT INTO users (nama, email, telepon, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nama, $email, $telepon, $password, $role]);

    echo "<script>alert('Pendaftaran berhasil! Silakan login.'); window.location.href='../index.php?page=login';</script>";
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Register Petugas Desa</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-8">
    <h2 class="text-2xl font-bold text-center text-green-600 mb-6">Register Petugas Desa</h2>

    <form action="../proses/register_petugas.php" method="POST" class="space-y-4">
      <div>
        <label class="block mb-1 font-medium">Nama Lengkap</label>
        <input type="text" name="nama" required class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-green-200" />
      </div>
      <div>
        <label class="block mb-1 font-medium">Email</label>
        <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-green-200" />
      </div>
      <div>
        <label class="block mb-1 font-medium">No. HP</label>
        <input type="text" name="telepon" required class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-green-200" />
      </div>
      <div>
        <label class="block mb-1 font-medium">Password</label>
        <input type="password" name="password" required class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-green-200" />
      </div>
      <input type="hidden" name="role" value="petugas">
      <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-lg font-semibold hover:bg-green-700 transition">Daftar</button>
    </form>
  </div>

</body>
</html>
