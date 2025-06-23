<?php require __DIR__ . '/../../config/database.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register Petugas Desa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<div class="w-full max-w-md bg-white p-6 rounded-lg shadow-md border border-green-600">
    <h2 class="text-2xl font-bold text-center text-green-700 mb-6">Register Petugas Desa</h2>

    <form action="/marketplace/controllers/register_petugas_proses.php" method="POST" class="space-y-4">
        <input type="text" name="username" placeholder="Username" required class="input-field">
        <input type="email" name="email" placeholder="Email" required class="input-field">
        <input type="password" name="password" placeholder="Password" required class="input-field">
        <input type="password" name="confirm_password" placeholder="Konfirmasi Password" required class="input-field">

        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 rounded">Daftar</button>
    </form>
</div>

<style>
    .input-field {
        @apply w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-green-300;
    }
</style>

</body>
</html>
