<?php
include 'fungsi.php';
require_login('customer');
include 'koneksi.php';
include 'config_pembayaran.php';

$user_id = (int) $_SESSION['user']['id'];

$q = mysqli_query($koneksi, "SELECT * FROM pesanan WHERE user_id = $user_id ORDER BY created_at DESC");
$daftar_pesanan = [];
while ($row = mysqli_fetch_assoc($q)) {
    $daftar_pesanan[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan | Kedai Nabela</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-body: #16191E; --card-bg: #1E222A; --sidebar-bg: #1B1E25; --item-bg: #252A34;
            --text-main: #E2E8F0; --text-muted: #94A3B8; --accent: #E08D3C; --accent-hover: #C97A2E;
            --border-color: #2D333F;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-body); color: var(--text-main); line-height: 1.6; }

        .navbar { background: var(--sidebar-bg); border-bottom: 1px solid var(--border-color); padding: 16px 30px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
        .brand { font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .brand i { color: var(--accent); }
        .nav-link { color: var(--text-muted); text-decoration: none; font-size: 13px; font-weight: 600; padding: 8px 14px; border-radius: 6px; border: 1px solid var(--border-color); }
        .nav-link:hover { color: var(--text-main); border-color: var(--accent); }

        .wrapper { max-width: 800px; margin: 0 auto; padding: 35px 20px 60px; }
        .page-title { font-size: 22px; font-weight: 700; margin-bottom: 22px; display: flex; align-items: center; gap: 10px; }
        .page-title i { color: var(--accent); }

        .empty-box { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        .empty-box i { font-size: 36px; margin-bottom: 12px; color: var(--border-color); }
        .empty-box a { color: var(--accent); text-decoration: none; font-weight: 600; }

        .order-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 18px 20px; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; }
        .order-left { display: flex; align-items: center; gap: 14px; }
        .order-icon { width: 42px; height: 42px; border-radius: 10px; background: var(--item-bg); color: var(--accent); display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
        .order-id { font-size: 14px; font-weight: 700; }
        .order-date { font-size: 11.5px; color: var(--text-muted); margin-top: 2px; }
        .order-status { font-size: 10.5px; font-weight: 700; padding: 3px 10px; border-radius: 20px; background: var(--item-bg); border: 1px solid var(--border-color); color: var(--accent); display: inline-block; margin-top: 4px; }
        .order-right { display: flex; align-items: center; gap: 14px; }
        .order-total { font-size: 14.5px; font-weight: 700; color: var(--accent); }
        .btn-struk { background: var(--item-bg); border: 1px solid var(--border-color); color: var(--text-main); padding: 8px 14px; border-radius: 7px; font-size: 12px; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 6px; }
        .btn-struk:hover { border-color: var(--accent); color: var(--accent); }
    </style>
</head>
<body>

<div class="navbar">
    <div class="brand"><i class="fas fa-utensils"></i> Kedai Nabela</div>
    <a href="index.php" class="nav-link"><i class="fas fa-arrow-left"></i> Kembali ke Menu</a>
</div>

<div class="wrapper">
    <div class="page-title"><i class="fas fa-clock-rotate-left"></i> Riwayat Pesanan</div>

    <?php if (count($daftar_pesanan) === 0): ?>
        <div class="empty-box">
            <i class="fas fa-receipt"></i>
            <p>Kamu belum pernah pesan apa-apa di sini.</p>
            <p style="margin-top:8px;"><a href="index.php">Mulai pesan sekarang &rarr;</a></p>
        </div>
    <?php else: ?>
        <?php foreach ($daftar_pesanan as $p): ?>
            <div class="order-card">
                <div class="order-left">
                    <div class="order-icon"><i class="fas <?php echo htmlspecialchars($metode_pembayaran_list[$p['metode_pembayaran']]['icon'] ?? 'fa-receipt'); ?>"></i></div>
                    <div>
                        <div class="order-id">Pesanan #<?php echo $p['id']; ?></div>
                        <div class="order-date"><?php echo date('d M Y, H:i', strtotime($p['created_at'])); ?> WIB</div>
                        <div class="order-status"><?php echo htmlspecialchars($p['status']); ?></div>
                    </div>
                </div>
                <div class="order-right">
                    <div class="order-total"><?php echo rupiah($p['total_harga']); ?></div>
                    <a href="struk.php?id=<?php echo $p['id']; ?>" class="btn-struk"><i class="fas fa-receipt"></i> Lihat Struk</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
