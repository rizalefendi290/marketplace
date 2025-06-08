<?php
require __DIR__ . '/../../config/database.php';

$success = '';
$error = '';

if (isset($_POST['register_admin_toko'])) {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $nomor_hp = trim($_POST['nomor_hp']);
    $password = $_POST['password'];
    $nama_toko = trim($_POST['nama_toko']);
    $alamat_toko = trim($_POST['alamat_toko']);
    $deskripsi = trim($_POST['deskripsi']);

    // Validasi sederhana
    if ($nama == '' || $username == '' || $email == '' || $nomor_hp == '' || $password == '' || $nama_toko == '' || $alamat_toko == '') {
        $error = "Semua field bertanda * wajib diisi.";
    } else {
        // Cek username sudah ada
        $cek = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $cek->execute([$username]);
        if ($cek->fetch()) {
            $error = "Username sudah digunakan.";
        } else {
            // Simpan user baru
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (nama, username, email, nomor_hp, password, role) VALUES (?, ?, ?, ?, ?, 'admin_toko')");
            $stmt->execute([$nama, $username, $email, $nomor_hp, $hashedPassword]);
            $user_id = $pdo->lastInsertId();

            // Simpan toko baru
            $stmt = $pdo->prepare("INSERT INTO toko (admin_id, nama_toko, alamat, deskripsi, status) VALUES (?, ?, ?, ?, 'Aktif')");
            $stmt->execute([$user_id, $nama_toko, $alamat_toko, $deskripsi]);

            $success = "Admin toko & toko berhasil didaftarkan!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Admin Toko - Marketplace Desa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black min-h-screen">
    <div class="max-w-xl mx-auto px-4 py-12">
        <h1 class="text-2xl font-bold text-green-400 mb-6 text-center">Daftar Admin Toko</h1>
        <?php if ($success): ?>
            <div class="bg-green-100 text-green-800 px-4 py-3 rounded mb-4"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="bg-red-100 text-red-800 px-4 py-3 rounded mb-4"><?= $error ?></div>
        <?php endif; ?>
        <form method="post" class="bg-white rounded-lg shadow p-6 space-y-4">
            <div>
                <label class="block font-medium mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="nama" required class="w-full border border-gray-300 rounded px-3 py-2" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
            </div>
            <div>
                <label class="block font-medium mb-1">Username <span class="text-red-500">*</span></label>
                <input type="text" name="username" required class="w-full border border-gray-300 rounded px-3 py-2" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div>
                <label class="block font-medium mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" required class="w-full border border-gray-300 rounded px-3 py-2" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div>
                <label class="block font-medium mb-1">Nomor HP <span class="text-red-500">*</span></label>
                <input type="text" name="nomor_hp" required class="w-full border border-gray-300 rounded px-3 py-2" value="<?= htmlspecialchars($_POST['nomor_hp'] ?? '') ?>">
            </div>
            <div>
                <label class="block font-medium mb-1">Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" required class="w-full border border-gray-300 rounded px-3 py-2">
            </div>
            <hr>
            <div>
                <label class="block font-medium mb-1">Nama Toko <span class="text-red-500">*</span></label>
                <input type="text" name="nama_toko" required class="w-full border border-gray-300 rounded px-3 py-2" value="<?= htmlspecialchars($_POST['nama_toko'] ?? '') ?>">
            </div>
            <div>
                <label class="block font-medium mb-1">Alamat Toko <span class="text-red-500">*</span></label>
                <input type="text" name="alamat_toko" required class="w-full border border-gray-300 rounded px-3 py-2" value="<?= htmlspecialchars($_POST['alamat_toko'] ?? '') ?>">
            </div>
            <div>
                <label class="block font-medium mb-1">Deskripsi Toko</label>
                <textarea name="deskripsi" rows="3" class="w-full border border-gray-300 rounded px-3 py-2"><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
            </div>
            <div class="text-center">
                <button type="submit" name="register_admin_toko" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-8 py-2 rounded-lg">Daftar Admin Toko</button>
            </div>
        </form>
        <div class="mt-6 text-center">
            <a href="/marketplace/index.php?page=home" class="text-blue-500 hover:underline">Kembali ke Home</a>
        </div>
    </div>
</body>
</html>