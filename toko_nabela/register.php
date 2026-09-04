<?php
include 'fungsi.php';

if (is_logged_in()) {
    header('Location: ' . (is_admin() ? 'pesanan_masuk.php' : 'index.php'));
    exit;
}

$error = '';

if (isset($_POST['daftar'])) {
    $nama       = trim($_POST['nama']);
    $email      = trim($_POST['email']);
    $password   = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi'];
    $no_telepon = trim($_POST['no_telepon']);
    $alamat     = trim($_POST['alamat']);

    if ($nama === '' || $email === '' || $password === '') {
        $error = 'Mohon lengkapi nama, email, dan kata sandi.';
    } elseif (strlen($password) < 6) {
        $error = 'Kata sandi minimal 6 karakter.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Konfirmasi kata sandi tidak cocok.';
    } else {
        $email_esc = mysqli_real_escape_string($koneksi, $email);
        $cek = mysqli_query($koneksi, "SELECT id FROM users WHERE email = '$email_esc'");
        if ($cek && mysqli_num_rows($cek) > 0) {
            $error = 'Email ini sudah terdaftar. Silakan masuk.';
        } else {
            $nama_esc  = mysqli_real_escape_string($koneksi, $nama);
            $telp_esc  = mysqli_real_escape_string($koneksi, $no_telepon);
            $alamat_esc = mysqli_real_escape_string($koneksi, $alamat);
            $hash = password_hash($password, PASSWORD_DEFAULT);

            mysqli_query($koneksi, "INSERT INTO users (nama, email, password, role, no_telepon, alamat)
                VALUES ('$nama_esc', '$email_esc', '$hash', 'customer', '$telp_esc', '$alamat_esc')");

            $user_id = mysqli_insert_id($koneksi);
            $_SESSION['user'] = [
                'id' => $user_id,
                'nama' => $nama,
                'email' => $email,
                'role' => 'customer',
            ];
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun | Kedai Nabela</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-body: #16191E; --card-bg: #1E222A; --item-bg: #252A34;
            --text-main: #E2E8F0; --text-muted: #94A3B8; --accent: #E08D3C; --accent-hover: #C97A2E;
            --border-color: #2D333F;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-body); color: var(--text-main); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .auth-card { max-width: 420px; width: 100%; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 14px; padding: 32px; }
        .brand { text-align: center; font-size: 18px; font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .brand i { color: var(--accent); }
        .subtitle { text-align: center; font-size: 13px; color: var(--text-muted); margin-bottom: 26px; }
        .error-msg { background: rgba(220,38,38,0.12); border: 1px solid rgba(220,38,38,0.3); color: #FCA5A5; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
        .form-group { margin-bottom: 14px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 12.5px; color: var(--text-muted); }
        input, textarea { width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 13.5px; background: var(--item-bg); color: var(--text-main); font-family: inherit; }
        textarea { resize: vertical; min-height: 50px; }
        input:focus, textarea:focus { outline: none; border-color: var(--accent); }
        button { width: 100%; background: var(--accent); color: #fff; padding: 12px; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 14px; margin-top: 8px; }
        button:hover { background: var(--accent-hover); }
        .footer-text { text-align: center; margin-top: 20px; font-size: 12.5px; color: var(--text-muted); }
        .footer-text a { color: var(--accent); text-decoration: none; font-weight: 600; }
        .back-link { display: block; text-align: center; margin-top: 14px; font-size: 12.5px; color: var(--text-muted); text-decoration: none; }
        .back-link:hover { color: var(--text-main); }
        .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="brand"><i class="fas fa-utensils"></i> Kedai Nabela</div>
    <div class="subtitle">Daftar akun customer baru</div>

    <?php if ($error): ?><div class="error-msg"><i class="fas fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" required value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>
        <div class="row-2">
            <div class="form-group">
                <label>Kata Sandi</label>
                <input type="password" name="password" required minlength="6">
            </div>
            <div class="form-group">
                <label>Konfirmasi Sandi</label>
                <input type="password" name="konfirmasi" required minlength="6">
            </div>
        </div>
        <div class="form-group">
            <label>Nomor WhatsApp (opsional)</label>
            <input type="text" name="no_telepon" value="<?php echo htmlspecialchars($_POST['no_telepon'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Alamat (opsional)</label>
            <textarea name="alamat"><?php echo htmlspecialchars($_POST['alamat'] ?? ''); ?></textarea>
        </div>
        <button type="submit" name="daftar">Daftar</button>
    </form>

    <div class="footer-text">Sudah punya akun? <a href="login.php">Masuk di sini</a></div>
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Kembali ke menu</a>
</div>
</body>
</html>
