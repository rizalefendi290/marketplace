<?php
// filepath: c:\xampp\htdocs\marketplace\views\semua_toko.php
require __DIR__ . '/../config/database.php';

// Ambil semua toko dari database
$stmt = $pdo->query("SELECT id, nama_toko, logo FROM toko ORDER BY id DESC");
$tokoList = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Semua Toko - Marketplace Desa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black min-h-screen">
    <?php include __DIR__ . '/components/header.php'; ?>

    <div class="max-w-6xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold text-yellow-500 mb-10 text-center">Semua Toko</h1>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <?php if ($tokoList): ?>
                <?php foreach ($tokoList as $toko): ?>
                    <a href="index.php?page=detail-toko&id=<?= $toko['id'] ?>" class="block border rounded-lg p-4 bg-gray-900 border border-yellow-300 hover:shadow-lg transition text-center">
                        <?php if (!empty($toko['logo']) && file_exists(__DIR__ . '/../uploads/' . $toko['logo'])): ?>
                            <img src="/marketplace/uploads/<?= htmlspecialchars($toko['logo']) ?>" class="w-20 h-20 object-cover rounded-full mx-auto mb-2" alt="<?= htmlspecialchars($toko['nama_toko']) ?>">
                        <?php else: ?>
                            <div class="w-20 h-20 flex items-center justify-center bg-gray-200 text-gray-400 rounded-full mx-auto mb-2">No Logo</div>
                        <?php endif; ?>
                        <div class="font-semibold text-yellow-500"><?= htmlspecialchars($toko['nama_toko']) ?></div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center text-gray-400 py-8">Belum ada toko terdaftar.</div>
            <?php endif; ?>
        </div>
        <div class="mt-12 text-center">
            <a href="index.php?page=home" class="inline-block bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-yellow-500 font-semibold px-10 py-4 rounded-full shadow-lg text-lg transition">
                Kembali ke Beranda
            </a>
        </div>
    </div>

    <footer class="mt-16">
        <?php include __DIR__ . '/components/footer.php'; ?>
    </footer>
</body>
</html>