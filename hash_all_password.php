<?php
require 'config/database.php'; // Sesuaikan path-nya

// Ambil semua user
$stmt = $pdo->query("SELECT id, password FROM users");
$users = $stmt->fetchAll();

foreach ($users as $user) {
    $id = $user['id'];
    $plainPassword = $user['password'];

    // Skip jika password sudah hash (dengan asumsi hash diawali dengan $2y$)
    if (strpos($plainPassword, '$2y$') === 0) {
        continue;
    }

    $hashed = password_hash($plainPassword, PASSWORD_DEFAULT);

    // Update password user
    $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $update->execute([$hashed, $id]);

    echo "Password untuk user ID $id berhasil di-hash.<br>";
}
