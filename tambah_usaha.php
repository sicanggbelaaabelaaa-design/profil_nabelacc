<?php
include 'koneksi.php';

if (isset($_POST['simpan'])) {
    $nama_usaha = mysqli_real_escape_string($koneksi, $_POST['nama_usaha']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $link_usaha = mysqli_real_escape_string($koneksi, $_POST['link_usaha']);

    // Otomatis tambahkan https:// jika belum ada
    if (!empty($link_usaha) && !preg_match("~^(?:f|ht)tps?://~i", $link_usaha)) {
        $link_usaha = "https://" . $link_usaha;
    }

    $sql = "INSERT INTO usaha (nama_usaha, deskripsi, link_usaha) VALUES ('$nama_usaha', '$deskripsi', '$link_usaha')";

    if (mysqli_query($koneksi, $sql)) {
        header('Location: index.php');
        exit;
    } else {
        $error = "Gagal menyimpan data usaha: " . mysqli_error($koneksi);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Web Usaha</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0F172A; color: #F8FAFC; padding: 40px 15px; }
        .form-card { max-width: 550px; margin: 0 auto; background: #1E293B; padding: 35px; border-radius: 20px; border: 1px solid #334155; box-shadow: 0 15px 35px rgba(0,0,0,0.3); }
        h2 { color: #F8FAFC; margin-bottom: 20px; border-bottom: 1px solid #334155; padding-bottom: 12px; font-weight: 700; }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 13.5px; color: #94A3B8; }
        input, textarea { width: 100%; padding: 12px 14px; border: 1px solid #334155; border-radius: 10px; font-size: 14px; box-sizing: border-box; background: #0F172A; color: #F8FAFC; }
        input:focus, textarea:focus { border-color: #38BDF8; outline: none; }
        button { background: #38BDF8; color: #0F172A; padding: 12px 28px; border: none; border-radius: 10px; cursor: pointer; font-weight: 700; font-size: 14px; }
        button:hover { background: #0284C7; color: #fff; }
        .btn-back { display: inline-block; margin-left: 15px; color: #94A3B8; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>
<div class="form-card">
    <h2>Tambah Web Usaha</h2>
    <?php if(isset($error)): ?><p style="color: #EF4444;"><?php echo $error; ?></p><?php endif; ?>
    <form action="" method="POST">
        <div class="form-group">
            <label>Nama Usaha / Projek</label>
            <input type="text" name="nama_usaha" required placeholder="Contoh: Toko Online nabela POS">
        </div>
        <div class="form-group">
            <label>Deskripsi Usaha</label>
            <textarea name="deskripsi" rows="4" placeholder="Jelaskan mengenai bisnis/projek ini..."></textarea>
        </div>
        <div class="form-group">
            <label>Link Web Usaha</label>
            <input type="text" name="link_usaha" placeholder="Contoh: tokoanda.com atau instagram.com/tokoanda">
        </div>
        <div style="margin-top: 25px;">
            <button type="submit" name="simpan">Simpan Usaha</button>
            <a href="index.php" class="btn-back">Batal</a>
        </div>
    </form>
</div>
</body>
</html>