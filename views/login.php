<?php
require __DIR__ . '/../config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Marketplace Desa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-sm p-8">
        <h1 class="text-2xl font-bold text-green-600 mb-6 text-center">Login</h1>
        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">Username atau password salah!</div>
        <?php endif; ?>
        <form action="/marketplace/controllers/proses_login.php" method="POST" class="space-y-4">
            <input type="text" name="username" placeholder="Username" required class="w-full border border-gray-300 rounded px-3 py-2">
            <input type="password" name="password" placeholder="Password" required class="w-full border border-gray-300 rounded px-3 py-2">
            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded">Login</button>
        </form>
        <div class="mt-4 text-center">
            Belum punya akun? <a href="/marketplace/index.php?page=register" class="text-blue-600 hover:underline">Daftar di sini</a>
        </div>
    </div>
</body>
</html>