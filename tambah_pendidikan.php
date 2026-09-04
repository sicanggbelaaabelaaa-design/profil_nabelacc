<?php
include 'koneksi.php';

if (isset($_POST['simpan'])) {
    $tingkat = mysqli_real_escape_string($koneksi, $_POST['tingkat']);
    $nama_instansi = mysqli_real_escape_string($koneksi, $_POST['nama_instansi']);
    $tahun_masuk = mysqli_real_escape_string($koneksi, $_POST['tahun_masuk']);
    $tahun_lulus = mysqli_real_escape_string($koneksi, $_POST['tahun_lulus']);
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    $sql = "INSERT INTO riwayat_pendidikan (tingkat, nama_instansi, tahun_masuk, tahun_lulus, keterangan) 
            VALUES ('$tingkat', '$nama_instansi', '$tahun_masuk', '$tahun_lulus', '$keterangan')";

    if (mysqli_query($koneksi, $sql)) {
        header('Location: index.php');
        exit;
    } else {
        $error = "Gagal menambah data: " . mysqli_error($koneksi);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Riwayat Pendidikan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7f6; padding: 40px 15px; }
        .form-card { max-width: 500px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h2 { color: #2e7d32; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; }
        button { background: #2e7d32; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; }
        button:hover { background: #1b5e20; }
        .btn-back { display: inline-block; margin-left: 10px; color: #666; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>
<div class="form-card">
    <h2>Tambah Riwayat Sekolah</h2>
    <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form action="" method="POST">
        <div class="form-group">
            <label>Tingkat Pendidikan</label>
            <input type="text" name="tingkat" placeholder="Contoh: SD / SMP / SMA / S1" required>
        </div>
        <div class="form-group">
            <label>Nama Instansi / Sekolah</label>
            <input type="text" name="nama_instansi" placeholder="Contoh: SMA Negeri 1" required>
        </div>
        <div class="form-group">
            <label>Tahun Masuk</label>
            <input type="text" name="tahun_masuk" placeholder="2018" required>
        </div>
        <div class="form-group">
            <label>Tahun Lulus</label>
            <input type="text" name="tahun_lulus" placeholder="2021" required>
        </div>
        <div class="form-group">
            <label>Keterangan / Catatan</label>
            <input type="text" name="keterangan" placeholder="Contoh: Jurusan IPA / Nilai Memuaskan">
        </div>
        <button type="submit" name="simpan">Simpan Data</button>
        <a href="index.php" class="btn-back">Batal</a>
    </form>
</div>
</body>
</html>
