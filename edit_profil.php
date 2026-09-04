<?php
include 'koneksi.php';

// Pastikan kolom keahlian sudah ada di tabel profil
$check_keahlian = mysqli_query($koneksi, "SHOW COLUMNS FROM profil LIKE 'keahlian'");
if(mysqli_num_rows($check_keahlian) == 0) {
    mysqli_query($koneksi, "ALTER TABLE profil ADD COLUMN keahlian TEXT DEFAULT ''");
}

// Ambil data profil saat ini
$query = mysqli_query($koneksi, "SELECT * FROM profil LIMIT 1");
$profil = mysqli_fetch_assoc($query);

$pesan = '';
$tipe_pesan = '';

// Proses Form submit
if (isset($_POST['simpan'])) {
    $nama          = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $tempat_lahir  = mysqli_real_escape_string($koneksi, $_POST['tempat_lahir']);
    $tanggal_lahir = mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']);
    $alamat        = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $email         = mysqli_real_escape_string($koneksi, $_POST['email']);
    $telepon       = mysqli_real_escape_string($koneksi, $_POST['telepon']);
    $instagram     = mysqli_real_escape_string($koneksi, $_POST['instagram']);
    $tiktok        = mysqli_real_escape_string($koneksi, $_POST['tiktok']);
    $tentang       = mysqli_real_escape_string($koneksi, $_POST['tentang']);
    $keahlian      = mysqli_real_escape_string($koneksi, $_POST['keahlian']);

    // Penanganan Upload Foto
    $nama_foto = $profil['foto'] ?? 'default.png';
    if (isset($_FILES['foto']['name']) && $_FILES['foto']['name'] != '') {
        $filename   = $_FILES['foto']['name'];
        $tmp_name   = $_FILES['foto']['tmp_name'];
        $file_size  = $_FILES['foto']['size'];
        $file_ext   = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed_ext = array('jpg', 'jpeg', 'png', 'webp');

        if (in_array($file_ext, $allowed_ext)) {
            if ($file_size <= 2000000) { // Maksimal 2MB
                $nama_foto = time() . '_' . rand(100, 999) . '.' . $file_ext;
                
                // Buat folder uploads jika belum ada
                if (!is_dir('uploads')) {
                    mkdir('uploads', 0777, true);
                }

                // Hapus foto lama jika bukan foto default
                if (!empty($profil['foto']) && $profil['foto'] != 'default.png' && file_exists('uploads/' . $profil['foto'])) {
                    unlink('uploads/' . $profil['foto']);
                }

                move_uploaded_file($tmp_name, 'uploads/' . $nama_foto);
            } else {
                $pesan = 'Ukuran foto terlalu besar! Maksimal 2MB.';
                $tipe_pesan = 'error';
            }
        } else {
            $pesan = 'Format foto tidak didukung! Gunakan JPG, PNG, atau WEBP.';
            $tipe_pesan = 'error';
        }
    }

    if (empty($pesan)) {
        if ($profil) {
            // Update data jika profil sudah ada
            $id = $profil['id'];
            $sql = "UPDATE profil SET 
                    nama = '$nama',
                    tempat_lahir = '$tempat_lahir',
                    tanggal_lahir = '$tanggal_lahir',
                    alamat = '$alamat',
                    email = '$email',
                    telepon = '$telepon',
                    instagram = '$instagram',
                    tiktok = '$tiktok',
                    tentang = '$tentang',
                    keahlian = '$keahlian',
                    foto = '$nama_foto'
                    WHERE id = $id";
        } else {
            // Insert data baru jika profil belum pernah diisi
            $sql = "INSERT INTO profil (nama, tempat_lahir, tanggal_lahir, alamat, email, telepon, instagram, tiktok, tentang, keahlian, foto) 
                    VALUES ('$nama', '$tempat_lahir', '$tanggal_lahir', '$alamat', '$email', '$telepon', '$instagram', '$tiktok', '$tentang', '$keahlian', '$nama_foto')";
        }

        if (mysqli_query($koneksi, $sql)) {
            header("Location: index.php");
            exit;
        } else {
            $pesan = 'Gagal menyimpan data: ' . mysqli_error($koneksi);
            $tipe_pesan = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil CV</title>
    <!-- Google Fonts & Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-body: #0F172A;
            --card-bg: #1E293B;
            --text-main: #F8FAFC;
            --text-muted: #94A3B8;
            --accent: #38BDF8;
            --accent-hover: #0284C7;
            --border-color: #334155;
            --input-bg: #0B1120;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.6;
            padding: 40px 15px;
        }

        .form-container {
            max-width: 700px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            border: 1px solid var(--border-color);
        }

        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .form-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-title i {
            color: var(--accent);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            color: #EF4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            width: 100%;
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 10px 14px;
            color: var(--text-main);
            font-size: 13.5px;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: var(--accent);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .photo-preview {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 5px;
        }

        .photo-preview img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--accent);
        }

        .btn-submit {
            background: var(--accent);
            color: #0F172A;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13.5px;
            cursor: pointer;
            transition: background 0.2s;
            width: 100%;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background: var(--accent-hover);
        }

        .btn-back {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
        }

        .btn-back:hover {
            color: var(--text-main);
        }

        @media (max-width: 600px) {
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="form-container">
    <div class="form-header">
        <div class="form-title">
            <i class="fas fa-user-edit"></i> Edit Profil CV
        </div>
        <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>

    <?php if (!empty($pesan)): ?>
        <div class="alert alert-<?php echo $tipe_pesan; ?>">
            <?php echo $pesan; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <!-- Nama Lengkap -->
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" class="form-control" required value="<?php echo htmlspecialchars($profil['nama'] ?? ''); ?>" placeholder="Contoh: Galang Prasetyo">
        </div>

        <!-- Tempat & Tanggal Lahir -->
        <div class="form-row">
            <div class="form-group">
                <label>Tempat Lahir</label>
                <input type="text" name="tempat_lahir" class="form-control" value="<?php echo htmlspecialchars($profil['tempat_lahir'] ?? ''); ?>" placeholder="Contoh: Surakarta">
            </div>
            <div class="form-group">
                <label>Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" class="form-control" value="<?php echo htmlspecialchars($profil['tanggal_lahir'] ?? ''); ?>">
            </div>
        </div>

        <!-- Email & Telepon -->
        <div class="form-row">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($profil['email'] ?? ''); ?>" placeholder="nama@email.com">
            </div>
            <div class="form-group">
                <label>Nomor Telepon / WA</label>
                <input type="text" name="telepon" class="form-control" value="<?php echo htmlspecialchars($profil['telepon'] ?? ''); ?>" placeholder="081234567890">
            </div>
        </div>

        <!-- Alamat -->
        <div class="form-group">
            <label>Alamat Tinggal</label>
            <input type="text" name="alamat" class="form-control" value="<?php echo htmlspecialchars($profil['alamat'] ?? ''); ?>" placeholder="Alamat lengkap kota/kabupaten">
        </div>

        <!-- Social Media Links -->
        <div class="form-row">
            <div class="form-group">
                <label>Link Instagram</label>
                <input type="url" name="instagram" class="form-control" value="<?php echo htmlspecialchars($profil['instagram'] ?? ''); ?>" placeholder="https://instagram.com/username">
            </div>
            <div class="form-group">
                <label>Link TikTok</label>
                <input type="url" name="tiktok" class="form-control" value="<?php echo htmlspecialchars($profil['tiktok'] ?? ''); ?>" placeholder="https://tiktok.com/@username">
            </div>
        </div>

        <!-- Keahlian Pemrograman -->
        <div class="form-group">
            <label>Keahlian Coding (Pisahkan dengan Koma)</label>
            <input type="text" name="keahlian" class="form-control" value="<?php echo htmlspecialchars($profil['keahlian'] ?? 'PHP, MySQL, HTML, CSS, JavaScript'); ?>" placeholder="PHP, MySQL, HTML, CSS, JavaScript, Bootstrap">
        </div>

        <!-- Ringkasan Profil / Tentang Saya -->
        <div class="form-group">
            <label>Profil Ringkas (Tentang Saya)</label>
            <textarea name="tentang" class="form-control" placeholder="Tuliskan deskripsi singkat mengenai pengalaman, fokus keahlian, dan tujuan profesional kamu..."><?php echo htmlspecialchars($profil['tentang'] ?? ''); ?></textarea>
        </div>

        <!-- Upload Foto Profil -->
        <div class="form-group">
            <label>Foto Profil (JPG/PNG, Maks. 2MB)</label>
            <div class="photo-preview">
                <?php 
                    $foto_nama = isset($profil['foto']) && !empty($profil['foto']) ? $profil['foto'] : 'default.png';
                    $foto_path = "uploads/" . $foto_nama;
                    if(!file_exists($foto_path) || $foto_nama == 'default.png') {
                        $foto_path = "https://via.placeholder.com/60/0B1120/F8FAFC?text=Foto";
                    }
                ?>
                <img src="<?php echo $foto_path; ?>" alt="Foto Profil Saat Ini">
                <input type="file" name="foto" class="form-control" accept="image/png, image/jpeg, image/webp">
            </div>
        </div>

        <button type="submit" name="simpan" class="btn-submit"><i class="fas fa-save"></i> Simpan Perubahan</button>
    </form>
</div>

</body>
</html>