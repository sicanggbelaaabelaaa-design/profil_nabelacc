<?php
include 'fungsi.php';
require_login('admin');
include 'config_pembayaran.php';

$daftar_status = ['Menunggu Konfirmasi', 'Diproses', 'Diantar', 'Selesai', 'Dibatalkan'];

$query = mysqli_query($koneksi, "SELECT p.*, u.email AS email_pembeli
    FROM pesanan p
    LEFT JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC");

$semua_pesanan = [];
while ($row = mysqli_fetch_assoc($query)) {
    $semua_pesanan[] = $row;
}

$pesan_sukses = isset($_GET['sukses']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan | Admin Kedai Nabela</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-body: #16191E; --card-bg: #1E222A; --sidebar-bg: #1B1E25; --item-bg: #252A34;
            --text-main: #E2E8F0; --text-muted: #94A3B8; --accent: #E08D3C; --accent-hover: #C97A2E;
            --border-color: #2D333F;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-body); color: var(--text-main); line-height: 1.6; min-height: 100vh; }

        .navbar { background: var(--sidebar-bg); border-bottom: 1px solid var(--border-color); padding: 16px 30px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; position: sticky; top: 0; z-index: 10; }
        .brand { font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .brand i { color: var(--accent); }
        .nav-links { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .nav-link { color: var(--text-muted); text-decoration: none; font-size: 13px; font-weight: 600; padding: 8px 14px; border-radius: 6px; border: 1px solid var(--border-color); display: flex; align-items: center; gap: 6px; }
        .nav-link:hover { color: var(--text-main); border-color: var(--accent); }
        .badge-admin { background: var(--accent); color: #fff; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; }

        .wrapper { max-width: 950px; margin: 0 auto; padding: 30px 20px 60px; }
        .page-title { font-size: 21px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .page-title i { color: var(--accent); }

        .alert-success { background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.35); color: #86efac; padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 18px; }

        .empty-box { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        .empty-box i { font-size: 36px; margin-bottom: 12px; color: var(--border-color); }

        .order-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 10px; padding: 18px; margin-bottom: 14px; }
        .order-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; flex-wrap: wrap; gap: 10px; }
        .order-id { font-size: 14px; font-weight: 700; }
        .order-date { font-size: 11.5px; color: var(--text-muted); margin-top: 2px; }
        .order-total { font-size: 15px; font-weight: 700; color: var(--accent); }

        .order-info { font-size: 12.5px; color: var(--text-muted); margin-bottom: 4px; }
        .order-info b { color: var(--text-main); font-weight: 600; }

        .order-items { margin-top: 10px; padding-top: 10px; border-top: 1px dashed var(--border-color); }
        .order-item-line { font-size: 12.5px; color: var(--text-muted); display: flex; justify-content: space-between; padding: 3px 0; }
        .order-item-line span:first-child { color: var(--text-main); }

        .status-form { margin-top: 14px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .status-form select { background: var(--item-bg); border: 1px solid var(--border-color); color: var(--text-main); padding: 8px 10px; border-radius: 6px; font-size: 12.5px; font-family: inherit; }
        .status-form button { background: var(--item-bg); border: 1px solid var(--border-color); color: var(--text-main); padding: 8px 14px; border-radius: 6px; font-size: 12.5px; font-weight: 600; cursor: pointer; }
        .status-form button:hover { border-color: var(--accent); color: var(--accent); }

        .status-tag { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; background: var(--item-bg); border: 1px solid var(--border-color); color: var(--accent); }
        .payment-tag { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; background: var(--item-bg); border: 1px solid var(--border-color); color: var(--text-main); display: inline-flex; align-items: center; gap: 5px; }
    </style>
</head>
<body>

<div class="navbar">
    <div class="brand"><i class="fas fa-utensils"></i> Kedai Nabela <span class="badge-admin">ADMIN</span></div>
    <div class="nav-links">
        <a href="index.php" class="nav-link"><i class="fas fa-house"></i> Lihat Menu</a>
        <a href="tambah_menu.php" class="nav-link"><i class="fas fa-plus"></i> Tambah Menu</a>
        <a href="atur_toko.php" class="nav-link"><i class="fas fa-store"></i> Atur Toko</a>
        <a href="logout.php" class="nav-link"><i class="fas fa-right-from-bracket"></i> Keluar</a>
    </div>
</div>

<div class="wrapper">
    <div class="page-title"><i class="fas fa-receipt"></i> Pesanan Masuk</div>

    <?php if ($pesan_sukses): ?>
        <div class="alert-success"><i class="fas fa-circle-check"></i> Status pesanan berhasil diperbarui.</div>
    <?php endif; ?>

    <?php if (count($semua_pesanan) === 0): ?>
        <div class="empty-box">
            <i class="fas fa-receipt"></i>
            <p>Belum ada pesanan masuk.</p>
        </div>
    <?php else: ?>
        <?php foreach ($semua_pesanan as $p): ?>
            <?php
                $q_detail = mysqli_query($koneksi, "SELECT * FROM detail_pesanan WHERE pesanan_id = " . (int) $p['id']);
                $items = [];
                while ($d = mysqli_fetch_assoc($q_detail)) { $items[] = $d; }
            ?>
            <div class="order-card">
                <div class="order-head">
                    <div>
                        <div class="order-id">Pesanan #<?php echo $p['id']; ?> &mdash; <?php echo htmlspecialchars($p['nama_pembeli']); ?></div>
                        <div class="order-date"><?php echo date('d M Y, H:i', strtotime($p['created_at'])); ?> WIB</div>
                    </div>
                    <div class="order-total"><?php echo rupiah($p['total_harga']); ?></div>
                </div>

                <div style="margin-bottom: 8px;">
                    <span class="payment-tag">
                        <i class="fas <?php echo htmlspecialchars($metode_pembayaran_list[$p['metode_pembayaran']]['icon'] ?? 'fa-wallet'); ?>"></i>
                        <?php echo htmlspecialchars($metode_pembayaran_list[$p['metode_pembayaran']]['label'] ?? $p['metode_pembayaran']); ?>
                    </span>
                </div>

                <div class="order-info"><b>Email:</b> <?php echo htmlspecialchars($p['email_pembeli'] ?? '-'); ?></div>
                <div class="order-info"><b>WhatsApp:</b> <?php echo htmlspecialchars($p['no_telepon']); ?></div>
                <div class="order-info"><b>Alamat:</b> <?php echo htmlspecialchars($p['alamat']); ?></div>
                <?php if (!empty($p['catatan'])): ?>
                    <div class="order-info"><b>Catatan:</b> <?php echo htmlspecialchars($p['catatan']); ?></div>
                <?php endif; ?>

                <div class="order-items">
                    <?php foreach ($items as $it): ?>
                        <div class="order-item-line">
                            <span><?php echo $it['jumlah']; ?>x <?php echo htmlspecialchars($it['nama_menu']); ?></span>
                            <span><?php echo rupiah($it['subtotal']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form action="update_status_pesanan.php" method="POST" class="status-form">
                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                    <span class="status-tag"><?php echo htmlspecialchars($p['status']); ?></span>
                    <select name="status">
                        <?php foreach ($daftar_status as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo ($s === $p['status']) ? 'selected' : ''; ?>><?php echo $s; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit"><i class="fas fa-rotate"></i> Update Status</button>
                    <a href="struk.php?id=<?php echo $p['id']; ?>" target="_blank" style="background: var(--item-bg); border: 1px solid var(--border-color); color: var(--text-main); padding: 8px 14px; border-radius: 6px; font-size: 12.5px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;"><i class="fas fa-receipt"></i> Lihat Struk</a>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
