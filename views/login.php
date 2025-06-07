<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h2>Login</h2>
    <form action="/marketplace/controllers/proses_login.php" method="POST">
        <input type="text" name="username" placeholder="Username" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit">Login</button>
    </form>
    <?php if (isset($_GET['error'])): ?>
        <p style="color:red;">Login gagal! Periksa kembali username atau password.</p>
    <?php endif; ?>
    <p>Belum mempunyai akun <a href='register.php'>Daftar Sekarang!</a></p>
    
</body>

</html>