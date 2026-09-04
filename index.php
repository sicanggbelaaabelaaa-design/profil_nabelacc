<?php
// Mulai session untuk cek status login (apabila dibutuhkan)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi.php';

// Otomatis buat tabel jika belum ada
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS keahlian_coding (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_skill VARCHAR(100) NOT NULL,
    persentase INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS pengalaman_magang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    posisi VARCHAR(100) NOT NULL,
    nama_perusahaan VARCHAR(100) NOT NULL,
    durasi VARCHAR(50) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS usaha (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_usaha VARCHAR(150) NOT NULL,
    deskripsi TEXT,
    link_usaha VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Cek apakah kolom whatsapp sudah ada di tabel profil, jika belum maka tambahkan otomatis
$cek_wa_col = mysqli_query($koneksi, "SHOW COLUMNS FROM profil LIKE 'whatsapp'");
if ($cek_wa_col && mysqli_num_rows($cek_wa_col) == 0) {
    mysqli_query($koneksi, "ALTER TABLE profil ADD whatsapp VARCHAR(30) NULL AFTER telepon");
}

// Otomatis daftarkan "Kedai Nabela" ke daftar Projek & Portofolio jika belum ada
$cek_usaha = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM usaha WHERE nama_usaha = 'Kedai Nabela'");
if ($cek_usaha && mysqli_fetch_assoc($cek_usaha)['jml'] == 0) {
    mysqli_query($koneksi, "INSERT INTO usaha (nama_usaha, deskripsi, link_usaha) VALUES (
        'Kedai Nabela',
        'Website dinamis untuk penjualan makanan & minuman: pelanggan dapat melihat menu, menambahkan ke keranjang, dan melakukan checkout secara online.',
        'toko_nabela/index.php'
    )");
}

// Fetch Profil
$query_profil = mysqli_query($koneksi, "SELECT * FROM profil LIMIT 1");
$profil = mysqli_fetch_assoc($query_profil);

// Fetch Keahlian Coding
$query_keahlian = mysqli_query($koneksi, "SELECT * FROM keahlian_coding ORDER BY persentase DESC");

// Fetch Magang
$query_magang = mysqli_query($koneksi, "SELECT * FROM pengalaman_magang ORDER BY id DESC");

// Fetch Pendidikan
$query_pendidikan = mysqli_query($koneksi, "SELECT * FROM riwayat_pendidikan ORDER BY id DESC");

// Fetch Usaha/Projek
$query_usaha = mysqli_query($koneksi, "SELECT * FROM usaha ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $profil ? htmlspecialchars($profil['nama']) : 'Portfolio'; ?> | Portfolio</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            /* Tema Hitam & Putih Minimalis */
            --bg-body: #111111;          /* Hitam pekat background */
            --card-bg: #1A1A1A;          /* Panel utama */
            --sidebar-bg: #161616;       /* Panel sidebar */
            --item-bg: #242424;          /* Kartu/chip */
            --item-hover: #2E2E2E;       /* Hover state */
            --text-main: #FFFFFF;        /* Putih bersih */
            --text-muted: #A0A0A0;       /* Abu-abu terang */
            --accent: #FFFFFF;           /* Putih sebagai aksen */
            --border-color: #333333;
            --shadow: 0 20px 40px rgba(0, 0, 0, 0.8);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.6;
            padding: 40px 16px;
            min-height: 100vh;
        }

        .main-wrapper {
            max-width: 1060px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: 16px;
            overflow: hidden;
            display: grid;
            grid-template-columns: 320px 1fr;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow);
        }

        /* Sidebar Left */
        .sidebar {
            background: var(--sidebar-bg);
            padding: 40px 28px 30px;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .avatar-box {
            width: 128px;
            height: 128px;
            margin: 0 auto 24px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid var(--border-color);
            padding: 4px;
            background: var(--item-bg);
        }

        .profile-photo { 
            width: 100%; 
            height: 100%; 
            border-radius: 50%;
            overflow: hidden;
        }
        
        .profile-photo img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            display: block; 
        }

        .sidebar-section { margin-bottom: 28px; }
        .sidebar-title {
            font-size: 11px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--text-muted);
            font-weight: 700;
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border-color);
        }

        .info-list { list-style: none; }
        .info-item { 
            display: flex; 
            align-items: flex-start; 
            gap: 12px; 
            margin-bottom: 12px; 
            font-size: 13px; 
            color: var(--text-muted); 
            word-break: break-word; 
            font-weight: 500; 
        }
        .info-item i { color: var(--text-main); margin-top: 3px; width: 16px; text-align: center; }

        .social-grid { display: flex; flex-direction: column; gap: 8px; }
        .social-btn { 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            background: var(--item-bg); 
            border: 1px solid var(--border-color); 
            color: var(--text-main); 
            padding: 10px 14px; 
            border-radius: 8px; 
            text-decoration: none; 
            font-size: 12.5px; 
            font-weight: 600; 
            transition: all 0.2s ease; 
        }
        .social-btn:hover { 
            border-color: var(--text-main); 
            background: var(--item-hover); 
        }

        .btn-toko {
            display: flex; 
            align-items: center; 
            justify-content: center; 
            gap: 9px;
            width: 100%; 
            background: var(--text-main);
            border: none; 
            color: #000000; 
            padding: 12px; 
            border-radius: 8px;
            text-decoration: none; 
            font-size: 13.5px; 
            font-weight: 800;
            transition: all 0.2s ease;
            margin-top: 10px;
        }
        .btn-toko:hover { background: #E5E5E5; transform: translateY(-2px); }

        /* Main Content Right */
        .content { padding: 44px 40px; }

        .name-title {
            font-size: 32px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .role-text {
            font-size: 12px; 
            color: var(--text-main); 
            font-weight: 700;
            margin-bottom: 32px; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px;
            background: var(--item-bg); 
            padding: 6px 14px; 
            border-radius: 6px;
            border: 1px solid var(--border-color);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-block { margin-bottom: 38px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; }
        .section-title {
            font-size: 16px; 
            font-weight: 700; 
            color: var(--text-main);
            display: flex; 
            align-items: center; 
            gap: 12px;
        }
        .section-title i {
            color: var(--text-main); 
            background: var(--item-bg);
            width: 32px; 
            height: 32px; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            border-radius: 6px; 
            font-size: 13px; 
            border: 1px solid var(--border-color);
        }

        /* Profile Text Box */
        .about-card { 
            background: var(--item-bg); 
            border: 1px solid var(--border-color); 
            border-left: 3px solid var(--text-main); 
            padding: 20px 22px; 
            border-radius: 4px 8px 8px 4px; 
            font-size: 14px; 
            color: var(--text-muted); 
            line-height: 1.8; 
        }

        /* Skill Item */
        .skills-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .skill-card { 
            background: var(--item-bg); 
            padding: 16px; 
            border-radius: 8px; 
            border: 1px solid var(--border-color); 
            transition: border-color 0.2s ease; 
        }
        .skill-card:hover { border-color: var(--text-main); }
        .skill-head { display: flex; justify-content: space-between; font-size: 13px; font-weight: 700; margin-bottom: 8px; color: var(--text-main); }
        .progress-bar-bg { background: var(--sidebar-bg); height: 6px; border-radius: 10px; overflow: hidden; border: 1px solid var(--border-color); }
        .progress-bar-fill { background: var(--text-main); height: 100%; border-radius: 10px; }

        /* Timeline Items */
        .timeline { border-left: 1px solid var(--border-color); padding-left: 20px; margin-left: 8px; }
        .timeline-item { position: relative; margin-bottom: 24px; }
        .timeline-item:last-child { margin-bottom: 0; }
        .timeline-item::before { 
            content: ''; 
            position: absolute; 
            left: -25px; 
            top: 6px; 
            width: 9px; 
            height: 9px; 
            border-radius: 50%; 
            background: var(--text-main); 
            border: 2px solid var(--card-bg); 
        }
        .item-title { font-size: 14.5px; font-weight: 700; color: var(--text-main); }
        .item-sub { font-size: 12.5px; color: var(--text-muted); font-weight: 600; margin-top: 2px; }
        .item-date { font-size: 11.5px; color: var(--text-muted); margin: 3px 0 6px; font-weight: 500; }
        .item-desc { font-size: 13px; color: var(--text-muted); line-height: 1.6; }

        /* Project Cards */
        .projects-grid { display: grid; grid-template-columns: 1fr; gap: 12px; }
        .project-card { 
            background: var(--item-bg); 
            border: 1px solid var(--border-color); 
            padding: 20px 22px; 
            border-radius: 8px; 
            transition: all 0.2s ease; 
            display: flex; 
            gap: 16px; 
        }
        .project-card:hover { border-color: var(--text-main); }
        .project-icon { 
            flex-shrink: 0; 
            width: 38px; 
            height: 38px; 
            border-radius: 6px; 
            background: var(--sidebar-bg); 
            border: 1px solid var(--border-color); 
            color: var(--text-main); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 14px; 
        }
        .project-title { font-size: 15px; font-weight: 700; color: var(--text-main); }
        .project-desc { font-size: 13px; color: var(--text-muted); margin-top: 4px; line-height: 1.6; }
        .project-link { 
            font-size: 12px; 
            color: var(--text-main); 
            text-decoration: none; 
            display: inline-flex; 
            align-items: center; 
            gap: 6px; 
            margin-top: 12px; 
            font-weight: 700; 
        }
        .project-link:hover { text-decoration: underline; }

        @media (max-width: 800px) {
            .main-wrapper { grid-template-columns: 1fr; }
            .sidebar { border-right: none; border-bottom: 1px solid var(--border-color); }
            .skills-grid { grid-template-columns: 1fr; }
            .content { padding: 30px 20px; }
            .name-title { font-size: 24px; }
            .project-card { flex-direction: column; gap: 12px; }
        }
    </style>
</head>
<body>

<div class="main-wrapper">
    <!-- Sidebar Left -->
    <div class="sidebar">
        <div>
            <div class="avatar-box">
                <div class="profile-photo">
                    <?php 
                        $foto_nama = isset($profil['foto']) && !empty($profil['foto']) ? $profil['foto'] : 'default.png';
                        $foto_path = "uploads/" . $foto_nama;
                        if(!file_exists($foto_path) || $foto_nama == 'default.png') {
                            $foto_path = "https://via.placeholder.com/140/242424/FFFFFF?text=Profil";
                        }
                    ?>
                    <img src="<?php echo $foto_path; ?>" alt="Foto Profil">
                </div>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-title">Informasi Pribadi</div>
                <ul class="info-list">
                    <li class="info-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>
                            <?php 
                                $tempat = !empty($profil['tempat_lahir']) ? htmlspecialchars($profil['tempat_lahir']) : '';
                                $tgl = !empty($profil['tanggal_lahir']) ? date('d M Y', strtotime($profil['tanggal_lahir'])) : '';
                                if ($tempat && $tgl) echo $tempat . ', ' . $tgl;
                                elseif ($tempat || $tgl) echo $tempat . $tgl;
                                else echo '-';
                            ?>
                        </span>
                    </li>
                    <li class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo htmlspecialchars($profil['alamat'] ?? '-'); ?></span>
                    </li>
                    <li class="info-item">
                        <i class="fas fa-envelope"></i>
                        <span><?php echo htmlspecialchars($profil['email'] ?? '-'); ?></span>
                    </li>
                    <li class="info-item">
                        <i class="fas fa-phone"></i>
                        <span><?php echo htmlspecialchars($profil['telepon'] ?? '-'); ?></span>
                    </li>
                </ul>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-title">Media Sosial & Kontak</div>
                <div class="social-grid">
                    <?php if(!empty($profil['instagram'])): ?>
                        <a href="<?php echo htmlspecialchars($profil['instagram']); ?>" target="_blank" class="social-btn">
                            <i class="fab fa-instagram"></i> Instagram
                        </a>
                    <?php endif; ?>
                    <?php if(!empty($profil['tiktok'])): ?>
                        <a href="<?php echo htmlspecialchars($profil['tiktok']); ?>" target="_blank" class="social-btn">
                            <i class="fab fa-tiktok"></i> TikTok
                        </a>
                    <?php endif; ?>
                    <?php 
                        // Menentukan nomor WhatsApp
                        $wa_num = !empty($profil['whatsapp']) ? $profil['whatsapp'] : ($profil['telepon'] ?? '');
                        $wa_clean = preg_replace('/[^0-9]/', '', $wa_num);
                        if(substr($wa_clean, 0, 1) === '0') {
                            $wa_clean = '62' . substr($wa_clean, 1);
                        }
                    ?>
                    <?php if(!empty($wa_clean)): ?>
                        <a href="https://wa.me/<?php echo $wa_clean; ?>" target="_blank" class="social-btn">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div>
            <!-- Tombol Kunjungi Toko Online -->
            <a href="https://nabelacc.gamer.free/index.php" class="btn-toko"><i class="fas fa-cart-shopping"></i> Kunjungi Toko Online</a>
        </div>
    </div>

    <!-- Main Content Right -->
    <div class="content">
        <h1 class="name-title"><?php echo htmlspecialchars($profil['nama'] ?? 'Nama Lengkap'); ?></h1>
        <div class="role-text"><i class="fas fa-laptop-code"></i> Web Developer</div>

        <!-- 1. Profil Ringkas -->
        <div class="section-block">
            <div class="about-card">
                <?php echo nl2br(htmlspecialchars($profil['tentang'] ?? 'Belum ada ringkasan profil yang ditambahkan.')); ?>
            </div>
        </div>

        <!-- 2. Keahlian Coding -->
        <div class="section-block">
            <div class="section-header">
                <div class="section-title"><i class="fas fa-code"></i> Keahlian Pemrograman</div>
            </div>

            <?php if($query_keahlian && mysqli_num_rows($query_keahlian) > 0): ?>
                <div class="skills-grid">
                    <?php while($k = mysqli_fetch_assoc($query_keahlian)): ?>
                        <div class="skill-card">
                            <div class="skill-head">
                                <span><?php echo htmlspecialchars($k['nama_skill']); ?></span>
                                <span><?php echo $k['persentase']; ?>%</span>
                            </div>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill" style="width: <?php echo $k['persentase']; ?>%;"></div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p style="font-size: 13px; color: var(--text-muted); font-style: italic;">Belum ada keahlian coding yang ditambahkan.</p>
            <?php endif; ?>
        </div>

        <!-- 3. Pengalaman Magang -->
        <div class="section-block">
            <div class="section-header">
                <div class="section-title"><i class="fas fa-briefcase"></i> Pengalaman Magang</div>
            </div>

            <div class="timeline">
                <?php if($query_magang && mysqli_num_rows($query_magang) > 0): ?>
                    <?php while($m = mysqli_fetch_assoc($query_magang)): ?>
                        <div class="timeline-item">
                            <div class="item-title"><?php echo htmlspecialchars($m['posisi']); ?></div>
                            <div class="item-sub"><?php echo htmlspecialchars($m['nama_perusahaan']); ?></div>
                            <div class="item-date"><?php echo htmlspecialchars($m['durasi']); ?></div>
                            <div class="item-desc"><?php echo nl2br(htmlspecialchars($m['deskripsi'])); ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="font-size: 13px; color: var(--text-muted); font-style: italic;">Belum ada pengalaman magang.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- 4. Riwayat Pendidikan -->
        <div class="section-block">
            <div class="section-header">
                <div class="section-title"><i class="fas fa-graduation-cap"></i> Riwayat Pendidikan</div>
            </div>

            <div class="timeline">
                <?php if($query_pendidikan && mysqli_num_rows($query_pendidikan) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($query_pendidikan)): ?>
                        <div class="timeline-item">
                            <div class="item-title"><?php echo htmlspecialchars($row['nama_instansi']); ?></div>
                            <div class="item-sub"><?php echo htmlspecialchars($row['tingkat']); ?></div>
                            <div class="item-date"><?php echo htmlspecialchars($row['tahun_masuk']); ?> — <?php echo htmlspecialchars($row['tahun_lulus']); ?></div>
                            <div class="item-desc"><?php echo htmlspecialchars($row['keterangan']); ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="font-size: 13px; color: var(--text-muted); font-style: italic;">Belum ada riwayat pendidikan.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- 5. Projek & Usaha -->
        <div class="section-block" style="margin-bottom: 0;">
            <div class="section-header">
                <div class="section-title"><i class="fas fa-folder-open"></i> Projek & Portofolio</div>
            </div>

            <?php if($query_usaha && mysqli_num_rows($query_usaha) > 0): ?>
                <div class="projects-grid">
                    <?php while($u = mysqli_fetch_assoc($query_usaha)): ?>
                        <div class="project-card">
                            <div class="project-icon"><i class="fas fa-shop"></i></div>
                            <div>
                                <div class="project-title">
                                    <?php echo htmlspecialchars($u['nama_usaha']); ?>
                                </div>
                                <div class="project-desc"><?php echo nl2br(htmlspecialchars($u['deskripsi'])); ?></div>

                                <?php if(!empty($u['link_usaha'])): ?>
                                    <a href="<?php echo htmlspecialchars($u['link_usaha']); ?>" target="_blank" class="project-link">
                                        <i class="fas fa-arrow-up-right-from-square"></i> Kunjungi Tautan Projek
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p style="font-size: 13px; color: var(--text-muted); font-style: italic;">Belum ada projek yang ditambahkan.</p>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>