<?php
include 'koneksi.php';
$id = (int)$_GET['id'];
$query = mysqli_query($koneksi, "SELECT * FROM pengalaman_magang WHERE id = $id");
$data = mysqli_fetch_assoc($query);

if (isset($_POST['update'])) {
    $posisi = mysqli_real_escape_string($koneksi, $_POST['posisi']);
    $perusahaan = mysqli_real_escape_string($koneksi, $_POST['nama_perusahaan']);
    $durasi = mysqli_real_escape_string($koneksi, $_POST['durasi']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);

    mysqli_query($koneksi, "UPDATE pengalaman_magang SET posisi='$posisi', nama_perusahaan='$perusahaan', durasi='$durasi', deskripsi='$deskripsi' WHERE id=$id");
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Edit Pengalaman Magang</title>
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
    <h3 style="margin-bottom: 20px; color: #38BDF8;">Edit Pengalaman Magang</h3>
    <form method="POST">
        <div class="form-group"><label>Posisi / Peran Magang</label><input type="text" name="posisi" value="<?php echo htmlspecialchars($data['posisi']); ?>" required></div>
        <div class="form-group"><label>Nama Perusahaan / Instansi</label><input type="text" name="nama_perusahaan" value="<?php echo htmlspecialchars($data['nama_perusahaan']); ?>" required></div>
        <div class="form-group"><label>Durasi / Periode</label><input type="text" name="durasi" value="<?php echo htmlspecialchars($data['durasi']); ?>" required></div>
        <div class="form-group"><label>Deskripsi Tugas & Pencapaian</label><textarea name="deskripsi" rows="4"><?php echo htmlspecialchars($data['deskripsi']); ?></textarea></div>
        <button type="submit" name="update" class="btn">Update Magang</button>
    </form>
</div>
</body>
</html>