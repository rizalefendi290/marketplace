<?php
$nama_user = '';
$foto_profil = '';
$email_user = '';
if (isset($_SESSION['user_id'])) {
    $nama_user = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'User';
    $role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
    $email_user = isset($_SESSION['email']) ? $_SESSION['email'] : '';
    // Ambil foto profil dari database jika ada
    require_once __DIR__ . '/../../config/database.php';
    $stmt = $pdo->prepare("SELECT foto_profil FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $foto_profil = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
</head>

<body>
    <nav class="bg-white border-gray-200 dark:bg-gray-900">
        <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
            <a href="/marketplace/index.php?page=home" class="flex items-center space-x-3 rtl:space-x-reverse">
                <img src="https://flowbite.com/docs/images/logo.svg" class="h-8" alt="Flowbite Logo" />
                <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">Marketplace Desa</span>
            </a>
            <div class="flex items-center md:order-2 space-x-3 md:space-x-0 rtl:space-x-reverse relative">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="/marketplace/index.php?page=login" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition">Login</a>
                <?php else: ?>
                    <button type="button" class="flex text-sm bg-gray-800 rounded-full md:me-0 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600" id="user-menu-button" aria-expanded="false" data-dropdown-toggle="user-dropdown" data-dropdown-placement="bottom">
                        <span class="sr-only">Open user menu</span>
                        <?php if ($foto_profil && file_exists(__DIR__ . '/../../uploads/' . $foto_profil)): ?>
                            <img class="w-8 h-8 rounded-full object-cover" src="/marketplace/uploads/<?= htmlspecialchars($foto_profil) ?>" alt="user photo">
                        <?php else: ?>
                            <img class="w-8 h-8 rounded-full object-cover" src="https://flowbite.com/docs/images/people/profile-picture-3.jpg" alt="user photo">
                        <?php endif; ?>
                    </button>
                    <!-- Dropdown menu -->
                    <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow-sm dark:bg-gray-700 dark:divide-gray-600 absolute left-0 top-full min-w-[200px]" id="user-dropdown">
                        <div class="px-4 py-3">
                            <span class="block text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($nama_user) ?></span>
                            <span class="block text-sm text-gray-500 truncate dark:text-gray-400"><?= htmlspecialchars($email_user) ?></span>
                        </div>
                        <ul class="py-2" aria-labelledby="user-menu-button">
                            <li>
                                <?php
                                $editProfileUrl = "#";
                                if ($role === 'admin_toko') {
                                    $editProfileUrl = "/marketplace/index.php?page=edit-toko";
                                } elseif ($role === 'customer') {
                                    $editProfileUrl = "/marketplace/index.php?page=edit-profile";
                                }
                                ?>
                                <a href="<?= $editProfileUrl ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Edit Profile</a>
                            </li>
                            <li>
                                <a href="/marketplace/index.php?page=keranjang-belanja" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Keranjang Belanja</a>
                            </li>
                            <li>
                                <a href="/marketplace/index.php?page=riwayat-pembelian" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Riwayat Pembelian</a>
                            </li>
                            <li>
                                <a href="/marketplace/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:text-red-400 dark:hover:bg-gray-600">Logout</a>
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>
                <button data-collapse-toggle="navbar-user" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600" aria-controls="navbar-user" aria-expanded="false">
                    <span class="sr-only">Open main menu</span>
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15"/>
                    </svg>
                </button>
            </div>
            <div class="hidden w-full md:block md:w-auto" id="navbar-default">
                <ul class="font-medium flex flex-col p-4 md:p-0 mt-4 border border-gray-100 rounded-lg bg-gray-50 md:flex-row md:space-x-8 rtl:space-x-reverse md:mt-0 md:border-0 md:bg-white dark:bg-gray-800 md:dark:bg-gray-900 dark:border-gray-700">
                    <li>
                        <a href="/marketplace/index.php?page=home" class="block py-2 px-3 text-white bg-blue-700 rounded-sm md:bg-transparent md:text-blue-700 md:p-0 dark:text-white md:dark:text-blue-500" aria-current="page">Home</a>
                    </li>
                    <li>
                        <a href="#" class="block py-2 px-3 text-gray-900 rounded-sm hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent">About</a>
                    </li>
                    <li>
                        <a href="#" class="block py-2 px-3 text-gray-900 rounded-sm hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent">Services</a>
                    </li>
                    <li>
                        <a href="#" class="block py-2 px-3 text-gray-900 rounded-sm hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent">Pricing</a>
                    </li>
                    <li>
                        <a href="#" class="block py-2 px-3 text-gray-900 rounded-sm hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</body>
<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</html>