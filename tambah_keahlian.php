<?php
include 'koneksi.php';

// Otomatis buat tabel jika belum ada
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS keahlian_coding (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_skill VARCHAR(100) NOT NULL,
    persentase INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$pesan = '';

if (isset($_POST['simpan'])) {
    $nama_skill = mysqli_real_escape_string($koneksi, $_POST['nama_skill']);
    $persentase = (int)$_POST['persentase'];

    if ($persentase < 0) $persentase = 0;
    if ($persentase > 100) $persentase = 100;

    $query = "INSERT INTO keahlian_coding (nama_skill, persentase) VALUES ('$nama_skill', '$persentase')";
    if (mysqli_query($koneksi, $query)) {
        header("Location: index.php");
        exit;
    } else {
        $pesan = "Gagal menambah keahlian: " . mysqli_error($koneksi);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Keahlian Coding</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #0F172A; color: #F8FAFC; font-family: 'Plus Jakarta Sans', sans-serif; padding: 40px 15px; }
        .box { max-width: 500px; margin: 0 auto; background: #1E293B; border: 1px solid #334155; padding: 30px; border-radius: 12px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #94A3B8; margin-bottom: 6px; }
        input[type="text"], input[type="number"] { width: 100%; padding: 10px; background: #0B1120; border: 1px solid #334155; border-radius: 6px; color: #fff; box-sizing: border-box; }
        .btn { background: #38BDF8; color: #0F172A; font-weight: 700; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; width: 100%; margin-top: 10px; }
        .btn-back { color: #94A3B8; text-decoration: none; font-size: 13px; display: inline-block; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="box">
    <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
    <h3 style="margin-bottom: 20px;"><i class="fas fa-code" style="color: #38BDF8;"></i> Tambah Keahlian Coding</h3>
    <?php if($pesan) echo "<p style='color:#ef4444; font-size:13px;'>$pesan</p>"; ?>
    <form method="POST">
        <div class="form-group">
            <label>Nama Bahasa / Skill Pemrograman</label>
            <input type="text" name="nama_skill" placeholder="Contoh: PHP, MySQL, React" required>
        </div>
        <div class="form-group">
            <label>Tingkat Penguasaan (%)</label>
            <input type="number" name="persentase" min="0" max="100" placeholder="Contoh: 85" required>
        </div>
        <button type="submit" name="simpan" class="btn">Simpan Skill</button>
    </form>
</div>
</body>
</html>