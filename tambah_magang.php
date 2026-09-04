<?php
include 'koneksi.php';
if (isset($_POST['simpan'])) {
    $posisi = mysqli_real_escape_string($koneksi, $_POST['posisi']);
    $perusahaan = mysqli_real_escape_string($koneksi, $_POST['nama_perusahaan']);
    $durasi = mysqli_real_escape_string($koneksi, $_POST['durasi']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);

    mysqli_query($koneksi, "INSERT INTO pengalaman_magang (posisi, nama_perusahaan, durasi, deskripsi) VALUES ('$posisi', '$perusahaan', '$durasi', '$deskripsi')");
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Tambah Pengalaman Magang</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #080C14; color: #F8FAFC; font-family: 'Plus Jakarta Sans', sans-serif; padding: 40px 15px; }
        .box { max-width: 500px; margin: 0 auto; background: #0B1120; border: 1px solid #334155; padding: 30px; border-radius: 12px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-size: 13px; color: #94A3B8; margin-bottom: 5px; }
        input, textarea { width: 100%; padding: 10px; background: #1E293B; border: 1px solid #334155; border-radius: 6px; color: #fff; box-sizing: border-box; }
        .btn { background: #38BDF8; color: #0F172A; font-weight: 700; border: none; padding: 10px; border-radius: 6px; cursor: pointer; width: 100%; margin-top: 10px; }
    </style>
</head>
<body>
<div class="box">
    <h3 style="margin-bottom: 20px; color: #38BDF8;">Tambah Pengalaman Magang</h3>
    <form method="POST">
        <div class="form-group"><label>Posisi / Peran Magang</label><input type="text" name="posisi" placeholder="Contoh: Junior Web Developer Intern" required></div>
        <div class="form-group"><label>Nama Perusahaan / Instansi</label><input type="text" name="nama_perusahaan" placeholder="Contoh: PT Teknologi Nusantara" required></div>
        <div class="form-group"><label>Durasi / Periode</label><input type="text" name="durasi" placeholder="Contoh: Jan 2025 - Apr 2025" required></div>
        <div class="form-group"><label>Deskripsi Tugas & Pencapaian</label><textarea name="deskripsi" rows="4" placeholder="Jelaskan apa saja yang kamu kerjakan..."></textarea></div>
        <button type="submit" name="simpan" class="btn">Simpan Magang</button>
    </form>
</div>
</body>
</html>