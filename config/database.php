<?php
$host = 'localhost';
$db = 'marketplace';
$user = 'root';
$pass = '';
$hashing_algorithm = PASSWORD_DEFAULT;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>
