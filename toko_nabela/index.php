<?php
include 'fungsi.php';
include 'koneksi.php';

// Pastikan kolom 'foto' sudah ada di tabel menu (auto-migrasi jika belum ada)
$cek_kolom_foto = mysqli_query($koneksi, "SHOW COLUMNS FROM menu LIKE 'foto'");
if ($cek_kolom_foto && mysqli_num_rows($cek_kolom_foto) == 0) {
    mysqli_query($koneksi, "ALTER TABLE menu ADD COLUMN foto VARCHAR(255) DEFAULT NULL");
}

$query_menu = mysqli_query($koneksi, "SELECT * FROM menu WHERE tersedia = 1 ORDER BY kategori ASC, id ASC");
$menu_per_kategori = [];
while ($row = mysqli_fetch_assoc($query_menu)) {
    $menu_per_kategori[$row['kategori']][] = $row;
}

// Info toko: alamat, jam buka, kontak
$q_toko = mysqli_query($koneksi, "SELECT * FROM pengaturan_toko ORDER BY id ASC LIMIT 1");
$toko = mysqli_fetch_assoc($q_toko);
$wa_bersih = preg_replace('/[^0-9]/', '', $toko['whatsapp'] ?? '');

// Rating & ulasan
$q_rating = mysqli_query($koneksi, "SELECT COUNT(*) AS jml, AVG(rating) AS rerata FROM ulasan");
$ringkasan_rating = mysqli_fetch_assoc($q_rating);
$total_ulasan = (int) ($ringkasan_rating['jml'] ?? 0);
$rerata_rating = $total_ulasan > 0 ? round((float) $ringkasan_rating['rerata'], 1) : 0;

$q_ulasan = mysqli_query($koneksi, "SELECT * FROM ulasan ORDER BY created_at DESC");
$daftar_ulasan = [];
while ($row = mysqli_fetch_assoc($q_ulasan)) {
    $daftar_ulasan[] = $row;
}

$pesan_sukses = isset($_GET['sukses']) ? true : false;
$akses_ditolak = $_GET['akses_ditolak'] ?? '';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kedai Nabela | Cafe & Resto</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            /* Warm Dark Coffee Theme (Gelap & Elegan) */
            --bg-body: #120E0D;          /* Deep Espresso Background */
            --card-bg: #1C1614;          /* Dark Coffee Card */
            --sidebar-bg: #16110F;       /* Navbar & Footer Dark Surface */
            --item-bg: #261F1C;          /* Inner Card Container */
            --text-main: #EFE7E1;        /* Warm Off-White Text */
            --text-muted: #A3958B;       /* Muted Warm Taupe Text */
            --accent: #F3EBE1;           /* Cream Accent untuk Tombol Utama */
            --accent-hover: #D8CEB7;     /* Soft Cream Hover */
            --border-color: #2E2521;     /* Subtle Dark Brown Border */
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        
        body { 
            background-color: var(--bg-body); 
            color: var(--text-main); 
            line-height: 1.6; 
            min-height: 100vh; 
        }

        /* Cafe Header / Navbar */
        .navbar { 
            background: rgba(22, 17, 15, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color); 
            padding: 16px 40px; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            position: sticky; 
            top: 0; 
            z-index: 100; 
            flex-wrap: wrap; 
            gap: 15px; 
        }

        .brand { 
            font-size: 20px; 
            font-weight: 800; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            color: var(--text-main);
            letter-spacing: -0.5px;
        }

        .brand i { 
            color: #120E0D; 
            background: var(--accent);
            padding: 10px;
            border-radius: 50%;
            font-size: 15px;
        }

        .nav-links { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        
        .nav-link { 
            color: var(--text-muted); 
            text-decoration: none; 
            font-size: 13px; 
            font-weight: 600; 
            padding: 9px 16px; 
            border-radius: 8px; 
            border: 1px solid transparent; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            transition: all 0.2s ease;
        }

        .nav-link:hover { color: var(--text-main); border-color: var(--border-color); background: var(--item-bg); }
        .nav-link.cta { background: var(--accent); color: #120E0D; font-weight: 700; }
        .nav-link.cta:hover { background: var(--accent-hover); transform: translateY(-1px); }
        
        .cart-badge { 
            background: #120E0D; 
            color: var(--accent); 
            font-size: 11px; 
            font-weight: 800; 
            padding: 2px 8px; 
            border-radius: 12px; 
        }

        .badge-role { 
            background: var(--accent); 
            color: #120E0D; 
            font-size: 10px; 
            font-weight: 800; 
            padding: 3px 8px; 
            border-radius: 20px; 
            margin-left: 6px; 
            text-transform: uppercase; 
        }

        .user-chip { color: var(--text-main); font-size: 13px; font-weight: 600; display: flex; align-items: center; padding: 6px 12px; background: var(--item-bg); border-radius: 8px; border: 1px solid var(--border-color); }

        /* Cafe Hero Banner */
        .hero { 
            max-width: 1000px; 
            margin: 30px auto 10px; 
            padding: 45px 20px; 
            text-align: center; 
            background: var(--card-bg);
            border-radius: 20px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow);
        }

        .hero-tag {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #120E0D;
            margin-bottom: 12px;
            background: var(--accent);
            padding: 4px 14px;
            border-radius: 20px;
        }

        .hero h1 { font-size: 32px; font-weight: 800; margin-bottom: 10px; letter-spacing: -0.5px; color: var(--text-main); }
        .hero h1 span { color: var(--accent); }
        .hero p { color: var(--text-muted); font-size: 14.5px; max-width: 540px; margin: 0 auto; line-height: 1.7; }

        /* Alerts */
        .alert-success { max-width: 1000px; margin: 20px auto 0; background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.4); color: #86EFAC; padding: 14px 18px; border-radius: 10px; font-size: 13.5px; text-align: center; font-weight: 600; }
        .alert-warning { max-width: 1000px; margin: 20px auto 0; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #FCA5A5; padding: 14px 18px; border-radius: 10px; font-size: 13.5px; text-align: center; font-weight: 600; }

        /* Menu Content Area */
        .wrapper { max-width: 1000px; margin: 0 auto; padding: 30px 20px 80px; }

        .kategori-block { margin-bottom: 48px; }
        .kategori-title { 
            font-size: 19px; 
            font-weight: 800; 
            margin-bottom: 20px; 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            border-bottom: 2px solid var(--border-color); 
            padding-bottom: 12px; 
            color: var(--text-main);
        }
        .kategori-title i { color: var(--accent); }

        .menu-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        
        .menu-card { 
            background: var(--card-bg); 
            border: 1px solid var(--border-color); 
            border-radius: 16px; 
            padding: 16px; 
            display: flex; 
            flex-direction: column; 
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .menu-card:hover { 
            transform: translateY(-3px); 
            border-color: rgba(243, 235, 225, 0.3); 
        }

        .menu-photo-container {
            width: 100%;
            height: 160px;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 14px;
            background: var(--item-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .menu-photo { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            transition: transform 0.4s ease;
        }

        .menu-card:hover .menu-photo { transform: scale(1.05); }

        .menu-icon { 
            font-size: 32px; 
            color: var(--accent); 
        }

        .menu-nama { font-size: 16px; font-weight: 700; margin-bottom: 6px; color: var(--text-main); }
        .menu-desc { font-size: 13px; color: var(--text-muted); flex-grow: 1; margin-bottom: 16px; line-height: 1.5; }
        
        .menu-footer { display: flex; align-items: center; justify-content: space-between; margin-top: auto; }
        .menu-harga { font-size: 15px; font-weight: 800; color: var(--accent); }
        
        .btn-add { 
            background: var(--accent); 
            border: 1px solid var(--accent); 
            color: #120E0D; 
            padding: 8px 14px; 
            border-radius: 8px; 
            font-size: 12px; 
            font-weight: 700; 
            text-decoration: none; 
            display: flex; 
            align-items: center; 
            gap: 6px; 
            transition: all 0.2s ease;
        }
        
        .btn-add:hover { 
            background: var(--accent-hover); 
            border-color: var(--accent-hover);
        }

        .btn-add-menu { 
            background: var(--accent); 
            border: 1px solid var(--accent); 
            color: #120E0D; 
            padding: 10px 18px; 
            border-radius: 8px; 
            font-size: 13px; 
            font-weight: 800; 
            text-decoration: none; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            transition: all 0.2s ease;
        }
        
        .btn-add-menu:hover { background: var(--accent-hover); transform: translateY(-1px); }

        .menu-actions { display: flex; gap: 8px; margin-top: 14px; padding-top: 12px; border-top: 1px dashed var(--border-color); }
        .btn-action { flex: 1; text-align: center; padding: 7px 10px; font-size: 11.5px; border-radius: 6px; text-decoration: none; font-weight: 700; transition: all 0.2s ease; }
        .btn-edit { background: var(--item-bg); color: var(--text-main); border: 1px solid var(--border-color); }
        .btn-edit:hover { border-color: var(--accent); }
        .btn-delete { background: rgba(239, 68, 68, 0.15); color: #FCA5A5; border: 1px solid rgba(239, 68, 68, 0.3); }
        .btn-delete:hover { background: rgba(239, 68, 68, 0.3); color: #FFF; }

        .footer { text-align: center; padding: 40px 20px 60px; color: var(--text-muted); font-size: 13px; border-top: 1px solid var(--border-color); background: var(--sidebar-bg); }
        .footer a { color: var(--accent); text-decoration: none; font-weight: 700; }
        .footer a:hover { text-decoration: underline; }

        /* Info Toko Panel */
        .info-toko-grid { max-width: 1000px; margin: 20px auto 0; padding: 0 20px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
        .info-toko-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 14px; padding: 18px; display: flex; align-items: flex-start; gap: 12px; }
        .info-toko-icon { width: 38px; height: 38px; border-radius: 10px; background: var(--item-bg); color: var(--accent); display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
        .info-toko-label { font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.6px; color: var(--text-muted); font-weight: 700; margin-bottom: 4px; }
        .info-toko-value { font-size: 12.5px; color: var(--text-main); font-weight: 600; line-height: 1.5; white-space: pre-line; }
        .info-toko-card a.info-toko-value { text-decoration: none; }
        .info-toko-card a.info-toko-value:hover { color: var(--accent); }
        .info-toko-card.rating-card { cursor: pointer; }
        .stars { color: var(--accent); font-size: 13px; letter-spacing: 1px; }

        /* Ulasan Section */
        .ulasan-section { max-width: 1000px; margin: 10px auto 0; padding: 40px 20px 20px; }
        .ulasan-summary { display: flex; align-items: center; gap: 20px; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px; padding: 24px; margin-bottom: 24px; flex-wrap: wrap; }
        .ulasan-score { font-size: 40px; font-weight: 800; color: var(--accent); line-height: 1; }
        .ulasan-score-sub { font-size: 12px; color: var(--text-muted); margin-top: 6px; }

        .ulasan-form { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px; padding: 22px 24px; margin-bottom: 24px; }
        .ulasan-form-title { font-size: 14px; font-weight: 700; margin-bottom: 14px; }
        .star-picker { display: flex; gap: 6px; margin-bottom: 14px; font-size: 22px; }
        .star-picker label { cursor: pointer; color: var(--border-color); transition: color 0.15s ease; }
        .star-picker input { display: none; }
        .star-picker input:checked ~ label,
        .star-picker label:hover,
        .star-picker label:hover ~ label { color: var(--accent); }
        .star-picker { flex-direction: row-reverse; justify-content: flex-end; }
        .ulasan-textarea { width: 100%; background: var(--item-bg); border: 1px solid var(--border-color); color: var(--text-main); padding: 10px 12px; border-radius: 8px; font-size: 13px; font-family: inherit; resize: vertical; min-height: 60px; margin-bottom: 12px; }
        .ulasan-textarea:focus { outline: none; border-color: var(--accent); }
        .btn-ulasan-submit { background: var(--accent); color: #120E0D; border: none; padding: 10px 20px; border-radius: 8px; font-size: 12.5px; font-weight: 800; cursor: pointer; }
        .ulasan-login-hint { font-size: 12.5px; color: var(--text-muted); text-align: center; padding: 16px; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px; margin-bottom: 24px; }
        .ulasan-login-hint a { color: var(--accent); font-weight: 700; text-decoration: none; }

        .ulasan-list { display: flex; flex-direction: column; gap: 12px; }
        .ulasan-item { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 14px; padding: 18px 20px; }
        .ulasan-item-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; flex-wrap: wrap; gap: 6px; }
        .ulasan-nama { font-size: 13.5px; font-weight: 700; }
        .ulasan-date { font-size: 11px; color: var(--text-muted); }
        .ulasan-komentar { font-size: 13px; color: var(--text-muted); line-height: 1.6; margin-top: 6px; }
        .ulasan-hapus { font-size: 11px; color: #FCA5A5; text-decoration: none; font-weight: 700; }

        @media (max-width: 850px) {
            .info-toko-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 540px) {
            .info-toko-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 850px) {
            .menu-grid { grid-template-columns: repeat(2, 1fr); }
            .navbar { padding: 16px 20px; }
        }
        @media (max-width: 540px) {
            .menu-grid { grid-template-columns: 1fr; }
            .navbar { flex-direction: column; align-items: stretch; }
            .nav-links { justify-content: center; }
            .hero h1 { font-size: 26px; }
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="brand"><i class="fas fa-mug-hot"></i> Kedai Nabela</div>
    <div class="nav-links">
        <a href="index.php" class="nav-link"><i class="fas fa-house"></i> Beranda</a>
        <a href="<?php echo LINK_CV; ?>" class="nav-link"><i class="fas fa-id-card"></i> Profil Pembuat</a>

        <?php if ($user): ?>
            <?php if ($user['role'] === 'admin'): ?>
                <a href="pesanan_masuk.php" class="nav-link"><i class="fas fa-receipt"></i> Pesanan Masuk</a>
                <a href="atur_toko.php" class="nav-link"><i class="fas fa-store"></i> Atur Toko</a>
            <?php else: ?>
                <a href="riwayat_pesanan.php" class="nav-link"><i class="fas fa-receipt"></i> Riwayat Pesanan</a>
                <a href="keranjang.php" class="nav-link cta">
                    <i class="fas fa-cart-shopping"></i> Keranjang
                    <span class="cart-badge"><?php echo total_item_keranjang(); ?></span>
                </a>
            <?php endif; ?>
            <span class="user-chip"><i class="fas fa-circle-user" style="color: var(--accent);"></i>&nbsp; <?php echo htmlspecialchars($user['nama']); ?><span class="badge-role"><?php echo htmlspecialchars($user['role']); ?></span></span>
            <a href="logout.php" class="nav-link"><i class="fas fa-right-from-bracket"></i> Keluar</a>
        <?php else: ?>
            <a href="register.php" class="nav-link"><i class="fas fa-user-plus"></i> Daftar</a>
            <a href="login.php" class="nav-link cta"><i class="fas fa-right-to-bracket"></i> Masuk</a>
        <?php endif; ?>
    </div>
</div>

<div class="hero">
    <span class="hero-tag"><i class="fas fa-coffee"></i> Authentic Taste & Warm Atmosphere</span>
    <h1>Sensasi Rasa <span>Spesial Cafe</span></h1>
    <p>Nikmati hidangan makanan dan racikan minuman hangat khas Kedai Nabela. Pilih menu favoritmu dan pesan secara online.</p>
</div>

<div class="info-toko-grid">
    <div class="info-toko-card">
        <div class="info-toko-icon"><i class="fas fa-location-dot"></i></div>
        <div>
            <div class="info-toko-label">Alamat</div>
            <div class="info-toko-value"><?php echo nl2br(htmlspecialchars($toko['alamat'] ?? '-')); ?></div>
        </div>
    </div>
    <div class="info-toko-card">
        <div class="info-toko-icon"><i class="fas fa-clock"></i></div>
        <div>
            <div class="info-toko-label">Jam Buka</div>
            <div class="info-toko-value"><?php echo nl2br(htmlspecialchars($toko['jam_buka'] ?? '-')); ?></div>
        </div>
    </div>
    <div class="info-toko-card">
        <div class="info-toko-icon"><i class="fas fa-phone"></i></div>
        <div>
            <div class="info-toko-label">Hubungi Kami</div>
            <?php if (!empty($wa_bersih)): ?>
                <a href="https://wa.me/<?php echo htmlspecialchars($wa_bersih); ?>" target="_blank" class="info-toko-value"><i class="fab fa-whatsapp" style="color:#25D366;"></i> WhatsApp</a>
                <?php if (!empty($toko['telepon'])): ?>
                    <div style="margin-top:4px;"><a href="tel:<?php echo htmlspecialchars($toko['telepon']); ?>" class="info-toko-value" style="font-weight:500; opacity:0.85;"><?php echo htmlspecialchars($toko['telepon']); ?></a></div>
                <?php endif; ?>
            <?php elseif (!empty($toko['telepon'])): ?>
                <a href="tel:<?php echo htmlspecialchars($toko['telepon']); ?>" class="info-toko-value"><?php echo htmlspecialchars($toko['telepon']); ?></a>
            <?php else: ?>
                <div class="info-toko-value">-</div>
            <?php endif; ?>
            <?php if (!empty($toko['instagram'])): ?>
                <div style="margin-top:4px;"><a href="<?php echo htmlspecialchars($toko['instagram']); ?>" target="_blank" class="info-toko-value" style="font-weight:500; opacity:0.85;"><i class="fab fa-instagram"></i> Instagram</a></div>
            <?php endif; ?>
        </div>
    </div>
    <a href="#ulasan" class="info-toko-card rating-card" style="text-decoration:none;">
        <div class="info-toko-icon"><i class="fas fa-star"></i></div>
        <div>
            <div class="info-toko-label">Rating Toko</div>
            <div class="info-toko-value">
                <?php if ($total_ulasan > 0): ?>
                    <span class="stars"><?php echo str_repeat('★', round($rerata_rating)) . str_repeat('☆', 5 - round($rerata_rating)); ?></span>
                    <?php echo $rerata_rating; ?>/5 (<?php echo $total_ulasan; ?> ulasan)
                <?php else: ?>
                    Belum ada ulasan
                <?php endif; ?>
            </div>
        </div>
    </a>
</div>

<?php if ($pesan_sukses): ?>
    <div class="alert-success"><i class="fas fa-circle-check"></i> Menu berhasil ditambahkan ke keranjang!</div>
<?php endif; ?>

<?php if ($akses_ditolak): ?>
    <div class="alert-warning"><i class="fas fa-triangle-exclamation"></i> <?php echo htmlspecialchars($akses_ditolak); ?></div>
<?php endif; ?>

<div class="wrapper">
    <?php if ($user && $user['role'] === 'admin'): ?>
    <div style="display:flex; justify-content:flex-end; margin-bottom: 24px;">
        <a href="tambah_menu.php" class="btn-add-menu"><i class="fas fa-plus"></i> Tambah Menu Baru</a>
    </div>
    <?php endif; ?>

    <?php if (count($menu_per_kategori) === 0): ?>
        <p style="text-align:center; color: var(--text-muted); font-style: italic; padding: 40px 0;">Belum ada menu yang tersedia saat ini.</p>
    <?php endif; ?>

    <?php foreach ($menu_per_kategori as $kategori => $items): ?>
        <?php
            $icon_kategori = 'fa-utensils';
            if (stripos($kategori, 'minum') !== false || stripos($kategori, 'kopi') !== false) $icon_kategori = 'fa-mug-hot';
            if (stripos($kategori, 'camilan') !== false || stripos($kategori, 'dessert') !== false || stripos($kategori, 'snack') !== false) $icon_kategori = 'fa-cookie-bite';
        ?>
        <div class="kategori-block">
            <div class="kategori-title"><i class="fas <?php echo $icon_kategori; ?>"></i> <?php echo htmlspecialchars($kategori); ?></div>
            <div class="menu-grid">
                <?php foreach ($items as $item): ?>
                    <?php
                        $ada_foto = !empty($item['foto']) && file_exists('uploads/' . $item['foto']);
                    ?>
                    <div class="menu-card">
                        <div class="menu-photo-container">
                            <?php if ($ada_foto): ?>
                                <img src="uploads/<?php echo htmlspecialchars($item['foto']); ?>" alt="<?php echo htmlspecialchars($item['nama_menu']); ?>" class="menu-photo">
                            <?php else: ?>
                                <div class="menu-icon"><i class="fas <?php echo htmlspecialchars($item['icon']); ?>"></i></div>
                            <?php endif; ?>
                        </div>

                        <div class="menu-nama"><?php echo htmlspecialchars($item['nama_menu']); ?></div>
                        <div class="menu-desc"><?php echo htmlspecialchars($item['deskripsi']); ?></div>

                        <div class="menu-footer">
                            <div class="menu-harga"><?php echo rupiah($item['harga']); ?></div>
                            <?php if (!$user || $user['role'] !== 'admin'): ?>
                                <a href="<?php echo $user ? 'update_keranjang.php?action=add&id=' . $item['id'] : 'login.php?redirect=index.php'; ?>" class="btn-add">
                                    <i class="fas fa-plus"></i> Tambah
                                </a>
                            <?php endif; ?>
                        </div>

                        <?php if ($user && $user['role'] === 'admin'): ?>
                            <div class="menu-actions">
                                <a href="edit_menu.php?id=<?php echo $item['id']; ?>" class="btn-action btn-edit"><i class="fas fa-pen"></i> Edit</a>
                                <a href="hapus_menu.php?id=<?php echo $item['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Hapus menu ini?');"><i class="fas fa-trash"></i> Hapus</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div id="ulasan" class="ulasan-section">
    <div class="kategori-title"><i class="fas fa-star"></i> Ulasan Pelanggan</div>

    <div class="ulasan-summary">
        <div>
            <div class="ulasan-score"><?php echo $total_ulasan > 0 ? $rerata_rating : '-'; ?></div>
            <div class="ulasan-score-sub">dari 5</div>
        </div>
        <div>
            <div class="stars" style="font-size:18px;"><?php echo str_repeat('★', $total_ulasan > 0 ? round($rerata_rating) : 0) . str_repeat('☆', 5 - ($total_ulasan > 0 ? round($rerata_rating) : 0)); ?></div>
            <div style="font-size:12.5px; color: var(--text-muted); margin-top:4px;"><?php echo $total_ulasan; ?> ulasan dari pelanggan</div>
        </div>
    </div>

    <?php if ($user && $user['role'] === 'customer'): ?>
        <form action="tambah_ulasan.php" method="POST" class="ulasan-form">
            <div class="ulasan-form-title">Bagikan pengalamanmu</div>
            <div class="star-picker">
                <input type="radio" name="rating" value="5" id="r5"><label for="r5">★</label>
                <input type="radio" name="rating" value="4" id="r4"><label for="r4">★</label>
                <input type="radio" name="rating" value="3" id="r3" checked><label for="r3">★</label>
                <input type="radio" name="rating" value="2" id="r2"><label for="r2">★</label>
                <input type="radio" name="rating" value="1" id="r1"><label for="r1">★</label>
            </div>
            <textarea name="komentar" class="ulasan-textarea" placeholder="Ceritakan pengalamanmu memesan di Kedai Nabela..."></textarea>
            <button type="submit" class="btn-ulasan-submit"><i class="fas fa-paper-plane"></i> Kirim Ulasan</button>
        </form>
    <?php elseif (!$user): ?>
        <div class="ulasan-login-hint">
            <a href="login.php?redirect=index.php">Masuk</a> sebagai customer untuk memberi ulasan & rating.
        </div>
    <?php endif; ?>

    <?php if (count($daftar_ulasan) === 0): ?>
        <p style="text-align:center; color: var(--text-muted); font-style: italic; padding: 20px 0;">Belum ada ulasan. Jadilah yang pertama!</p>
    <?php else: ?>
        <div class="ulasan-list">
            <?php foreach ($daftar_ulasan as $u): ?>
                <div class="ulasan-item">
                    <div class="ulasan-item-head">
                        <div>
                            <span class="ulasan-nama"><?php echo htmlspecialchars($u['nama']); ?></span>
                            <div class="stars"><?php echo str_repeat('★', (int) $u['rating']) . str_repeat('☆', 5 - (int) $u['rating']); ?></div>
                        </div>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span class="ulasan-date"><?php echo date('d M Y', strtotime($u['created_at'])); ?></span>
                            <?php if ($user && $user['role'] === 'admin'): ?>
                                <a href="hapus_ulasan.php?id=<?php echo $u['id']; ?>" class="ulasan-hapus" onclick="return confirm('Hapus ulasan ini?');">Hapus</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!empty($u['komentar'])): ?>
                        <div class="ulasan-komentar"><?php echo nl2br(htmlspecialchars($u['komentar'])); ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="footer">
    Dibuat oleh <strong>Nabela Chusnul Chotimah</strong> &mdash; <a href="<?php echo LINK_CV; ?>">Lihat Profil / CV</a>
</div>

</body>
</html>