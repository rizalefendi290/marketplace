<?php
require __DIR__ . '/../config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register - Marketplace Desa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-sm p-8">
        <h1 class="text-2xl font-bold text-blue-600 mb-6 text-center">Register</h1>
        <?php if (isset($_GET['register_error'])): ?>
            <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">
                <?php
                if ($_GET['register_error'] == 'username') {
                    echo "Username sudah digunakan!";
                } else {
                    echo "Registrasi gagal. Silakan coba lagi.";
                }
                ?>
            </div>
        <?php endif; ?>
        <form action="/marketplace/controllers/register_process.php" method="POST" class="space-y-3">
            <input type="text" name="nama" placeholder="Nama Lengkap" required class="w-full border border-gray-300 rounded px-3 py-2">
            <input type="text" name="username" placeholder="Username" required class="w-full border border-gray-300 rounded px-3 py-2">
            <input type="email" name="email" placeholder="Email" required class="w-full border border-gray-300 rounded px-3 py-2">
            <input type="text" name="nomor_hp" placeholder="Nomor HP" required class="w-full border border-gray-300 rounded px-3 py-2">
            <input type="password" name="password" placeholder="Password" required class="w-full border border-gray-300 rounded px-3 py-2">
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded">Register</button>
        </form>
        <div class="mt-4 text-center">
            Sudah punya akun? <a href="/marketplace/index.php?page=login" class="text-green-600 hover:underline">Login di sini</a>
        </div>
    </div>
</body>
</html>