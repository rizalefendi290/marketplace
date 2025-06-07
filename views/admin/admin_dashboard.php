<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <form method="POST" action="index.php?page=save-pengajuan" enctype="multipart/form-data">
        <input name="judul" placeholder="Judul..." required>
        <textarea name="deskripsi"></textarea>
        <input type="number" name="jumlah" step="0.01">
        <input type="file" name="lampiran">
        <button type="submit">Kirim</button>
    </form>

</body>

</html>