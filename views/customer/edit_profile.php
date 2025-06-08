<?php
require __DIR__ . '/../../config/database.php';

// Pastikan user sudah login dan berperan sebagai customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: /marketplace/index.php?page=login");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data user
$stmt = $pdo->prepare("SELECT nama, email, alamat, no_telp, foto_profil FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Proses update profile
$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $alamat = trim($_POST['alamat']);
    $no_telp = trim($_POST['no_telp']);

    // Handle upload foto profil
    $foto_profil = $user['foto_profil'];
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $allowed)) {
            $newName = 'user_' . $user_id . '_' . time() . '.' . $ext;
            $uploadPath = __DIR__ . '/../../uploads/' . $newName;
            if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $uploadPath)) {
                $foto_profil = $newName;
            } else {
                $error = "Gagal upload foto profil.";
            }
        } else {
            $error = "Format foto profil tidak didukung.";
        }
    }

    if ($nama === '' || $email === '') {
        $error = "Nama dan email wajib diisi.";
    } elseif (!$error) {
        $stmt = $pdo->prepare("UPDATE users SET nama = ?, email = ?, alamat = ?, no_telp = ?, foto_profil = ? WHERE id = ?");
        $stmt->execute([$nama, $email, $alamat, $no_telp, $foto_profil, $user_id]);
        $_SESSION['nama'] = $nama;
        $success = true;
        // Refresh data user
        $stmt = $pdo->prepare("SELECT nama, email, alamat, no_telp, foto_profil FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Profil - Marketplace Desa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black min-h-screen">
    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="max-w-lg mx-auto px-4 py-12">
        <h1 class="text-2xl font-bold text-green-400 mb-6 text-center">Edit Profil</h1>
        <div class="bg-gray-900 rounded-2xl shadow-xl p-8 border border-green-800">
            <?php if ($success): ?>
                <div class="mb-4 p-3 bg-green-700 text-white rounded">Profil berhasil diperbarui.</div>
            <?php elseif ($error): ?>
                <div class="mb-4 p-3 bg-red-700 text-white rounded"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data" class="space-y-5">
                <div class="flex flex-col items-center mb-4">
                    <?php if (!empty($user['foto_profil']) && file_exists(__DIR__ . '/../../uploads/' . $user['foto_profil'])): ?>
                        <img src="/marketplace/uploads/<?= htmlspecialchars($user['foto_profil']) ?>" alt="Foto Profil" class="w-24 h-24 rounded-full object-cover border-2 border-green-500 mb-2">
                    <?php else: ?>
                        <div class="w-24 h-24 rounded-full bg-gray-700 flex items-center justify-center text-gray-400 mb-2">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    <?php endif; ?>
                    <label class="block text-gray-200 font-medium">Foto Profil</label>
                    <input type="file" name="foto_profil" accept="image/*" class="mt-2 text-gray-200">
                    <span class="text-xs text-gray-400 mt-1">Format: jpg, jpeg, png, gif. Maks 2MB.</span>
                </div>
                <div>
                    <label class="block text-gray-200 mb-1 font-medium">Nama</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required class="w-full px-3 py-2 rounded border border-gray-600 bg-gray-800 text-white">
                </div>
                <div>
                    <label class="block text-gray-200 mb-1 font-medium">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="w-full px-3 py-2 rounded border border-gray-600 bg-gray-800 text-white">
                </div>
                <div>
                    <label class="block text-gray-200 mb-1 font-medium">Alamat</label>
                    <textarea name="alamat" rows="2" class="w-full px-3 py-2 rounded border border-gray-600 bg-gray-800 text-white"><?= htmlspecialchars($user['alamat']) ?></textarea>
                </div>
                <div>
                    <label class="block text-gray-200 mb-1 font-medium">No. Telepon</label>
                    <input type="text" name="no_telp" value="<?= htmlspecialchars($user['no_telp']) ?>" class="w-full px-3 py-2 rounded border border-gray-600 bg-gray-800 text-white">
                </div>
                <div class="text-center">
                    <button type="submit" class="bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white font-semibold px-8 py-2 rounded-full shadow transition">Simpan Perubahan</button>
                </div>
            </form>
        </div>
        <div class="text-center mt-8">
            <a href="/marketplace/index.php?page=home" class="inline-block bg-white text-green-700 font-semibold px-6 py-2 rounded-full shadow hover:bg-green-50 transition">Kembali ke Beranda</a>
        </div>
    </div>

    <footer class="mt-16">
        <?php include __DIR__ . '/../components/footer.php'; ?>
    </footer>
</body>
</html>