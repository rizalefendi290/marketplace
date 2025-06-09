<?php
require __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin_toko') {
    header("Location: /marketplace/index.php?page=login");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data toko
$stmt = $pdo->prepare("SELECT * FROM toko WHERE admin_id = ?");
$stmt->execute([$user_id]);
$toko = $stmt->fetch();

// Ambil data user (pemilik)
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$pemilik = $stmt->fetch();

if (!$toko) {
    die("Toko tidak ditemukan.");
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_toko = $_POST['nama_toko'];
    $alamat = $_POST['alamat'];
    $kontak = $_POST['kontak'];
    $nama_pemilik = $_POST['nama_pemilik'];
    $email = $_POST['email'];

    // Handle upload logo
    $logo = $toko['logo'];
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $newName = 'toko_' . $toko['id'] . '_' . time() . '.' . $ext;
        $uploadPath = __DIR__ . '/../../uploads/' . $newName;
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadPath)) {
            $logo = $newName;
        }
    }

    // Update toko (tambahkan logo)
    $stmt = $pdo->prepare("UPDATE toko SET nama_toko = ?, alamat = ?, kontak = ?, logo = ? WHERE id = ?");
    $stmt->execute([$nama_toko, $alamat, $kontak, $logo, $toko['id']]);

    // Update nama pemilik dan email di tabel users
    $stmt = $pdo->prepare("UPDATE users SET nama = ?, email = ? WHERE id = ?");
    $stmt->execute([$nama_pemilik, $email, $user_id]);

    // Jika password diisi, update password
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$password, $user_id]);
        $success = "Data toko, email, password, dan logo berhasil diperbarui.";
    } else {
        $success = "Data toko, email, dan logo berhasil diperbarui.";
    }

    echo "<div class='p-4 bg-green-100 text-green-800 rounded mt-4'>
            $success <a href='/marketplace/index.php?page=admin-dashboard' class='underline text-blue-600'>Kembali ke Dashboard</a>
          </div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Informasi Toko</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-800 p-6">
    <?php include __DIR__ . '/components/header.php'; ?>
    <div class="p-4 sm:ml-64">
        <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg mt-14 bg-white">
            <div class="max-w-2xl mx-auto p-6 bg-white shadow-md mt-8 rounded-lg">
                <h2 class="text-2xl font-bold mb-6 text-center text-green-700">Edit Informasi Toko</h2>
                <form method="POST" class="space-y-5" enctype="multipart/form-data">
                    <div>
                        <label class="block font-medium mb-1">Nama Toko</label>
                        <input type="text" name="nama_toko" value="<?= htmlspecialchars($toko['nama_toko']) ?>" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-green-200">
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Alamat</label>
                        <textarea name="alamat" required rows="3" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-green-200"><?= htmlspecialchars($toko['alamat']) ?></textarea>
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Kontak</label>
                        <input type="text" name="kontak" value="<?= htmlspecialchars($toko['kontak']) ?>" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-green-200">
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Nama Pemilik</label>
                        <input type="text" name="nama_pemilik" value="<?= htmlspecialchars($pemilik['nama']) ?>" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-green-200">
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Email Pemilik</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($pemilik['email']) ?>" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-green-200">
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Password Baru <span class="text-xs text-gray-500">(Kosongkan jika tidak ingin mengubah)</span></label>
                        <input type="password" name="password" placeholder="Password baru" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-green-200">
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Logo/Foto Toko</label>
                        <?php if (!empty($toko['logo']) && file_exists(__DIR__ . '/../../uploads/' . $toko['logo'])): ?>
                            <img src="/marketplace/uploads/<?= htmlspecialchars($toko['logo']) ?>" alt="Logo Toko" class="w-24 h-24 rounded-full mb-2 object-cover border">
                        <?php endif; ?>
                        <input type="file" name="logo" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                        <span class="text-xs text-gray-500">Kosongkan jika tidak ingin mengubah foto.</span>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-lg transition">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>