<?php
include 'fungsi.php';
require_login('admin');

$q = mysqli_query($koneksi, "SELECT * FROM pengaturan_toko ORDER BY id ASC LIMIT 1");
$pengaturan = mysqli_fetch_assoc($q);

$pesan = '';
$sukses = false;

if (isset($_POST['simpan'])) {
    $alamat    = mysqli_real_escape_string($koneksi, trim($_POST['alamat']));
    $jam_buka  = mysqli_real_escape_string($koneksi, trim($_POST['jam_buka']));
    $telepon   = mysqli_real_escape_string($koneksi, trim($_POST['telepon']));
    $whatsapp  = mysqli_real_escape_string($koneksi, trim($_POST['whatsapp']));
    $instagram = mysqli_real_escape_string($koneksi, trim($_POST['instagram']));

    mysqli_query($koneksi, "UPDATE pengaturan_toko SET
        alamat = '$alamat',
        jam_buka = '$jam_buka',
        telepon = '$telepon',
        whatsapp = '$whatsapp',
        instagram = '$instagram'
        WHERE id = " . (int) $pengaturan['id']);

    $sukses = true;
    $q = mysqli_query($koneksi, "SELECT * FROM pengaturan_toko ORDER BY id ASC LIMIT 1");
    $pengaturan = mysqli_fetch_assoc($q);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Toko | Kedai Nabela</title>
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
        .success-msg { background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.35); color: #86efac; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 12.5px; color: var(--text-muted); }
        input, textarea { width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 13.5px; background: var(--item-bg); color: var(--text-main); font-family: inherit; }
        textarea { resize: vertical; min-height: 70px; }
        input:focus, textarea:focus { outline: none; border-color: var(--accent); }
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
    <h2><i class="fas fa-store"></i> Atur Info Toko</h2>

    <?php if ($sukses): ?><div class="success-msg"><i class="fas fa-circle-check"></i> Info toko berhasil diperbarui.</div><?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label>Alamat Toko</label>
            <textarea name="alamat" required><?php echo htmlspecialchars($pengaturan['alamat'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label>Jam Buka</label>
            <textarea name="jam_buka" required placeholder="Contoh: Senin - Sabtu: 08.00 - 21.00"><?php echo htmlspecialchars($pengaturan['jam_buka'] ?? ''); ?></textarea>
            <div class="hint">Bisa ditulis per baris, contoh: "Senin - Sabtu: 08.00 - 21.00" lalu baris baru "Minggu: 09.00 - 22.00".</div>
        </div>
        <div class="form-group">
            <label>Nomor Telepon</label>
            <input type="text" name="telepon" value="<?php echo htmlspecialchars($pengaturan['telepon'] ?? ''); ?>" placeholder="081234567890">
        </div>
        <div class="form-group">
            <label>Nomor WhatsApp</label>
            <input type="text" name="whatsapp" value="<?php echo htmlspecialchars($pengaturan['whatsapp'] ?? ''); ?>" placeholder="6281234567890">
            <div class="hint">Format 62xxxxxxxxxx (tanpa spasi/strip, tanpa tanda +).</div>
        </div>
        <div class="form-group">
            <label>Instagram (opsional, boleh kosong)</label>
            <input type="text" name="instagram" value="<?php echo htmlspecialchars($pengaturan['instagram'] ?? ''); ?>" placeholder="https://instagram.com/namatoko">
        </div>
        <div class="actions">
            <button type="submit" name="simpan">Simpan Perubahan</button>
            <a href="index.php" class="btn-back">Kembali ke Menu</a>
        </div>
    </form>
</div>
</body>
</html>
