<?php
include 'fungsi.php';
require_login('admin');

$cek_kolom_foto = mysqli_query($koneksi, "SHOW COLUMNS FROM menu LIKE 'foto'");
if ($cek_kolom_foto && mysqli_num_rows($cek_kolom_foto) == 0) {
    mysqli_query($koneksi, "ALTER TABLE menu ADD COLUMN foto VARCHAR(255) DEFAULT NULL");
}

$pesan = '';

// Ambil daftar kategori yang sudah ada, untuk pilihan cepat
$q_kategori = mysqli_query($koneksi, "SELECT DISTINCT kategori FROM menu ORDER BY kategori ASC");
$daftar_kategori = [];
while ($k = mysqli_fetch_assoc($q_kategori)) {
    $daftar_kategori[] = $k['kategori'];
}
if (count($daftar_kategori) === 0) {
    $daftar_kategori = ['Makanan Utama', 'Minuman', 'Camilan & Dessert'];
}

if (isset($_POST['simpan'])) {
    $kategori   = trim($_POST['kategori']);
    if ($kategori === 'lainnya') {
        $kategori = trim($_POST['kategori_baru']);
    }
    $nama_menu  = mysqli_real_escape_string($koneksi, trim($_POST['nama_menu']));
    $deskripsi  = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi']));
    $harga      = (int) $_POST['harga'];
    $icon       = mysqli_real_escape_string($koneksi, trim($_POST['icon']) ?: 'fa-utensils');
    $kategori_esc = mysqli_real_escape_string($koneksi, $kategori);

    if ($kategori === '' || $nama_menu === '' || $harga <= 0) {
        $pesan = 'Mohon lengkapi kategori, nama menu, dan harga (harus lebih dari 0).';
    } else {
        // Penanganan upload foto
        $nama_foto = null;
        if (isset($_FILES['foto']['name']) && $_FILES['foto']['name'] != '') {
            $filename   = $_FILES['foto']['name'];
            $tmp_name   = $_FILES['foto']['tmp_name'];
            $file_size  = $_FILES['foto']['size'];
            $file_ext   = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $allowed_ext = array('jpg', 'jpeg', 'png', 'webp');

            if (in_array($file_ext, $allowed_ext)) {
                if ($file_size <= 2000000) { // Maksimal 2MB
                    if (!is_dir('uploads')) {
                        mkdir('uploads', 0777, true);
                    }
                    $nama_foto = 'menu_' . time() . '_' . rand(100, 999) . '.' . $file_ext;
                    move_uploaded_file($tmp_name, 'uploads/' . $nama_foto);
                } else {
                    $pesan = 'Ukuran foto terlalu besar! Maksimal 2MB.';
                }
            } else {
                $pesan = 'Format foto tidak didukung! Gunakan JPG, PNG, atau WEBP.';
            }
        }

        if ($pesan === '') {
            $foto_sql = $nama_foto ? "'" . mysqli_real_escape_string($koneksi, $nama_foto) . "'" : "NULL";
            $sql = "INSERT INTO menu (kategori, nama_menu, deskripsi, harga, icon, foto, tersedia)
                    VALUES ('$kategori_esc', '$nama_menu', '$deskripsi', $harga, '$icon', $foto_sql, 1)";
            if (mysqli_query($koneksi, $sql)) {
                header('Location: index.php');
                exit;
            } else {
                $pesan = 'Gagal menyimpan menu: ' . mysqli_error($koneksi);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Menu | Kedai Nabela</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-body: #16191E; --card-bg: #1E222A; --item-bg: #252A34;
            --text-main: #E2E8F0; --text-muted: #94A3B8; --accent: #E08D3C; --accent-hover: #C97A2E;
            --border-color: #2D333F;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-body); color: var(--text-main); line-height: 1.6; padding: 40px 15px; }
        .form-card { max-width: 560px; margin: 0 auto; background: var(--card-bg); padding: 30px; border-radius: 14px; border: 1px solid var(--border-color); }
        h2 { font-size: 19px; font-weight: 700; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 10px; }
        h2 i { color: var(--accent); }
        .error-msg { background: rgba(220,38,38,0.12); border: 1px solid rgba(220,38,38,0.3); color: #FCA5A5; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 12.5px; color: var(--text-muted); }
        input, textarea, select { width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 13.5px; background: var(--item-bg); color: var(--text-main); font-family: inherit; }
        textarea { resize: vertical; min-height: 60px; }
        input:focus, textarea:focus, select:focus { outline: none; border-color: var(--accent); }
        .hint { font-size: 11.5px; color: var(--text-muted); margin-top: 5px; }
        .actions { margin-top: 24px; display: flex; align-items: center; gap: 14px; }
        button { background: var(--accent); color: #fff; padding: 11px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 13.5px; }
        button:hover { background: var(--accent-hover); }
        .btn-back { color: var(--text-muted); text-decoration: none; font-size: 13px; font-weight: 600; }
        .btn-back:hover { color: var(--text-main); }
    </style>
</head>
<body>
<div class="form-card">
    <h2><i class="fas fa-plus"></i> Tambah Menu Baru</h2>

    <?php if ($pesan): ?><div class="error-msg"><?php echo htmlspecialchars($pesan); ?></div><?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Kategori</label>
            <select name="kategori" id="kategori" onchange="document.getElementById('kategoriBaruWrap').style.display = this.value === 'lainnya' ? 'block' : 'none';">
                <?php foreach ($daftar_kategori as $kat): ?>
                    <option value="<?php echo htmlspecialchars($kat); ?>"><?php echo htmlspecialchars($kat); ?></option>
                <?php endforeach; ?>
                <option value="lainnya">+ Kategori baru...</option>
            </select>
        </div>
        <div class="form-group" id="kategoriBaruWrap" style="display:none;">
            <label>Nama Kategori Baru</label>
            <input type="text" name="kategori_baru" placeholder="Contoh: Paket Hemat">
        </div>
        <div class="form-group">
            <label>Nama Menu</label>
            <input type="text" name="nama_menu" required placeholder="Contoh: Es Kopi Susu">
        </div>
        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="deskripsi" placeholder="Jelaskan menu ini secara singkat..."></textarea>
        </div>
        <div class="form-group">
            <label>Harga (Rp)</label>
            <input type="number" name="harga" required min="0" step="500" placeholder="Contoh: 15000">
        </div>
        <div class="form-group">
            <label>Foto Menu (opsional)</label>
            <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp">
            <div class="hint">Format JPG/PNG/WEBP, maksimal 2MB. Kalau tidak diisi, akan pakai ikon bawaan.</div>
        </div>
        <div class="form-group">
            <label>Ikon Cadangan (jika tanpa foto)</label>
            <input type="text" name="icon" placeholder="Contoh: fa-mug-hot, fa-bowl-rice, fa-ice-cream">
            <div class="hint">Nama ikon dari Font Awesome, tanpa perlu diisi jika sudah pakai foto.</div>
        </div>
        <div class="actions">
            <button type="submit" name="simpan">Simpan Menu</button>
            <a href="index.php" class="btn-back">Batal</a>
        </div>
    </form>
</div>
</body>
</html>
