<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h2>Form Registrasi User</h2>
    <form action="../controllers/register_process.php" method="POST">
        <input type="text" name="nama" placeholder="Nama Lengkap" required><br><br>
        <input type="text" name="username" placeholder="Username" required><br><br>
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="text" name="nomor_hp" placeholder="Nomor HP" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit">Register</button>
    </form>
    <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>

    <?php if (isset($_GET['error'])): ?>
        <p style="color:red;">Username sudah digunakan atau terjadi kesalahan.</p>
    <?php endif; ?>
</body>

</html>