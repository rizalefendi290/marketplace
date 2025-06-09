<?php
require __DIR__ . '/../../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin_toko') {
    header("Location: /marketplace/index.php?page=login");
    exit;
}

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID produk tidak ditemukan.");
}

// Validasi produk milik admin
$stmt = $pdo->prepare("SELECT b.*, k.nama_kategori FROM barang b 
    JOIN toko t ON b.toko_id = t.id 
    LEFT JOIN kategori k ON b.kategori_id = k.id
    WHERE b.id = ? AND t.admin_id = ?");
$stmt->execute([$id, $user_id]);
$produk = $stmt->fetch();

if (!$produk) {
    die("Produk tidak ditemukan atau bukan milik Anda.");
}

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama_barang'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $kategori_nama = trim($_POST['kategori_nama']);

    // Cek kategori, buat baru jika belum ada
    $kategori_id = null;
    if ($kategori_nama !== '') {
        $stmt = $pdo->prepare("SELECT id FROM kategori WHERE LOWER(nama_kategori) = LOWER(?)");
        $stmt->execute([$kategori_nama]);
        $kategori = $stmt->fetch();
        if ($kategori) {
            $kategori_id = $kategori['id'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
            $stmt->execute([$kategori_nama]);
            $kategori_id = $pdo->lastInsertId();
        }
    }

    $gambar = $produk['gambar'];
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $uploadDir = dirname(__DIR__, 2) . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $filename = time() . '-' . basename($_FILES['gambar']['name']);
        $targetFile = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetFile)) {
            $gambar = $filename;
        } else {
            echo "<p class='text-red-600'>Upload gambar gagal.</p>";
        }
    }

    $stmt = $pdo->prepare("UPDATE barang SET nama_barang = ?, deskripsi = ?, harga = ?, stok = ?, gambar = ?, kategori_id = ? WHERE id = ?");
    $stmt->execute([$nama, $deskripsi, $harga, $stok, $gambar, $kategori_id, $id]);

    echo "<div class='p-4 bg-green-100 text-green-800 rounded mt-4'>
            Produk berhasil diperbarui. <a href='/marketplace/index.php?page=admin-dashboard' class='underline text-blue-600'>Kembali ke Dashboard</a>
          </div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-800 p-6">
    
    <?php include __DIR__ . '/components/header.php'; ?>
    <div class="p-4 sm:ml-64">
        <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg mt-14 bg-white">
            <div class="max-w-3xl mx-auto p-6 bg-white shadow-md mt-8 rounded-lg">
                <h2 class="text-2xl font-bold mb-4 text-center">Edit Produk</h2>
                <form method="post" enctype="multipart/form-data" class="space-y-5">
                    <div>
                        <label class="block font-medium mb-1">Nama Barang</label>
                        <input type="text" name="nama_barang" value="<?= htmlspecialchars($produk['nama_barang']) ?>" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200">
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Kategori</label>
                        <input type="text" name="kategori_nama" value="<?= htmlspecialchars($produk['nama_kategori'] ?? '') ?>" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200" placeholder="Masukkan nama kategori">
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Deskripsi</label>
                        <textarea name="deskripsi" rows="4" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200"><?= htmlspecialchars($produk['deskripsi']) ?></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium mb-1">Harga</label>
                            <input type="number" name="harga" value="<?= $produk['harga'] ?>" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200">
                        </div>
                        <div>
                            <label class="block font-medium mb-1">Stok</label>
                            <input type="number" name="stok" value="<?= $produk['stok'] ?>" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200">
                        </div>
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Gambar Saat Ini</label>
                        <?php if ($produk['gambar']): ?>
                            <img src="/marketplace/uploads/<?= htmlspecialchars($produk['gambar']) ?>" class="w-32 h-auto rounded mb-2 border">
                        <?php else: ?>
                            <p class="text-gray-500">Tidak ada gambar</p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Ganti Gambar (Opsional)</label>
                        <input type="file" name="gambar" accept="image/*" class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <div class="text-center">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg transition">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>