<?php
include 'fungsi.php';
require_login();
include 'koneksi.php';
include 'config_pembayaran.php';

$user = current_user();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$pesanan = null;
$detail = [];

if ($id > 0) {
    $q_pesanan = mysqli_query($koneksi, "SELECT * FROM pesanan WHERE id = $id");
    $pesanan = mysqli_fetch_assoc($q_pesanan);

    // Customer hanya boleh lihat struk pesanan miliknya sendiri. Admin boleh lihat semua.
    if ($pesanan && $user['role'] !== 'admin' && (int) $pesanan['user_id'] !== (int) $user['id']) {
        $pesanan = null;
    }

    if ($pesanan) {
        $q_detail = mysqli_query($koneksi, "SELECT * FROM detail_pesanan WHERE pesanan_id = $id");
        while ($row = mysqli_fetch_assoc($q_detail)) {
            $detail[] = $row;
        }
    }
}

$q_toko = mysqli_query($koneksi, "SELECT * FROM pengaturan_toko ORDER BY id ASC LIMIT 1");
$toko = mysqli_fetch_assoc($q_toko);

$label_bayar = $pesanan ? ($metode_pembayaran_list[$pesanan['metode_pembayaran']]['label'] ?? $pesanan['metode_pembayaran']) : '';

$kode_struk = $pesanan ? 'KN-' . str_pad($pesanan['id'], 6, '0', STR_PAD_LEFT) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pesanan <?php echo $pesanan ? '#' . $pesanan['id'] : ''; ?> | Kedai Nabela</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-body: #16191E; --card-bg: #1E222A; --item-bg: #252A34;
            --text-main: #E2E8F0; --text-muted: #94A3B8; --accent: #E08D3C; --accent-hover: #C97A2E;
            --border-color: #2D333F;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-body); color: var(--text-main); padding: 40px 15px; }

        .page-actions { max-width: 380px; margin: 0 auto 16px; display: flex; gap: 10px; }
        .btn { flex: 1; text-align: center; padding: 11px; border-radius: 8px; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; border: none; font-family: inherit; }
        .btn-print { background: var(--accent); color: #16191E; }
        .btn-print:hover { background: var(--accent-hover); }
        .btn-back { background: var(--item-bg); border: 1px solid var(--border-color); color: var(--text-main); }
        .btn-back:hover { border-color: var(--accent); color: var(--accent); }

        .not-found { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        .not-found a { color: var(--accent); }

        /* Struk ala kertas thermal */
        .struk {
            max-width: 380px;
            margin: 0 auto;
            background: #F7F3EC;
            color: #1A1A1A;
            padding: 28px 22px;
            border-radius: 4px;
            font-family: 'Space Mono', monospace;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            position: relative;
        }
        .struk::before, .struk::after {
            content: '';
            position: absolute;
            left: 0; right: 0;
            height: 10px;
            background-image: linear-gradient(135deg, #F7F3EC 25%, transparent 25%), linear-gradient(225deg, #F7F3EC 25%, transparent 25%);
            background-size: 14px 14px;
            background-color: var(--bg-body);
        }
        .struk::before { top: -1px; transform: translateY(-100%); background-position: 0 -1px; }
        .struk::after { bottom: -1px; transform: translateY(100%) rotate(180deg); background-position: 0 -1px; }

        .struk-header { text-align: center; margin-bottom: 14px; padding-bottom: 14px; border-bottom: 1px dashed #999; }
        .struk-toko-nama { font-size: 16px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
        .struk-toko-info { font-size: 10.5px; color: #555; margin-top: 6px; line-height: 1.6; }

        .struk-meta { font-size: 11px; margin-bottom: 14px; padding-bottom: 14px; border-bottom: 1px dashed #999; }
        .struk-meta-row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .struk-meta-row span:first-child { color: #666; }

        .struk-items { margin-bottom: 14px; padding-bottom: 14px; border-bottom: 1px dashed #999; }
        .struk-item { margin-bottom: 8px; font-size: 11.5px; }
        .struk-item-name { display: flex; justify-content: space-between; font-weight: 700; }
        .struk-item-detail { display: flex; justify-content: space-between; color: #666; font-size: 10.5px; margin-top: 1px; }

        .struk-total-row { display: flex; justify-content: space-between; font-size: 11.5px; margin-bottom: 4px; }
        .struk-total-row.grand { font-size: 14px; font-weight: 700; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #999; }

        .struk-payment { margin-top: 14px; padding-top: 14px; border-top: 1px dashed #999; font-size: 10.5px; }
        .struk-payment-title { font-weight: 700; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
        .struk-payment-row { display: flex; justify-content: space-between; color: #555; margin-top: 2px; }

        .struk-status { text-align: center; margin-top: 14px; padding: 8px; background: #EAE3D6; border-radius: 4px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; }

        .struk-footer { text-align: center; margin-top: 16px; font-size: 10.5px; color: #666; }
        .struk-barcode { text-align: center; margin-top: 14px; font-size: 22px; letter-spacing: 2px; font-family: 'Space Mono', monospace; }

        @media print {
            body { background: #fff; padding: 0; }
            .page-actions { display: none; }
            .struk { box-shadow: none; margin: 0; max-width: 100%; }
        }
        <?php if ($user['role'] !== 'admin'): ?>
        /* Customer tidak diizinkan mencetak struk — hanya bisa melihat di layar */
        @media print {
            .struk { display: none !important; }
            .no-print-notice { display: block !important; }
        }
        <?php endif; ?>
        .no-print-notice { display: none; text-align: center; padding: 40px 20px; font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body>

<div class="no-print-notice">Struk ini hanya dapat dicetak oleh admin. Silakan hubungi admin untuk mendapatkan struk cetak resmi.</div>

<?php if (!$pesanan): ?>
    <div class="not-found">
        <p>Struk tidak ditemukan, atau kamu tidak punya akses ke pesanan ini.</p>

        <p style="margin-top:10px;"><a href="index.php">Kembali ke menu &rarr;</a></p>
    </div>
<?php else: ?>

    <div class="page-actions">
        <?php if ($user['role'] === 'admin'): ?>
            <button onclick="window.print()" class="btn btn-print"><i class="fas fa-print"></i> Cetak Struk</button>
        <?php endif; ?>
        <a href="<?php echo $user['role'] === 'admin' ? 'pesanan_masuk.php' : 'riwayat_pesanan.php'; ?>" class="btn btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>

    <div class="struk">
        <div class="struk-header">
            <div class="struk-toko-nama">Kedai Nabela</div>
            <div class="struk-toko-info">
                <?php echo nl2br(htmlspecialchars($toko['alamat'] ?? '-')); ?><br>
                <?php echo htmlspecialchars($toko['telepon'] ?? ''); ?>
            </div>
        </div>

        <div class="struk-meta">
            <div class="struk-meta-row"><span>No. Struk</span><span><?php echo $kode_struk; ?></span></div>
            <div class="struk-meta-row"><span>Tanggal</span><span><?php echo date('d/m/Y H:i', strtotime($pesanan['created_at'])); ?></span></div>
            <div class="struk-meta-row"><span>Kasir</span><span>Sistem Online</span></div>
            <div class="struk-meta-row"><span>Pelanggan</span><span><?php echo htmlspecialchars($pesanan['nama_pembeli']); ?></span></div>
        </div>

        <div class="struk-items">
            <?php foreach ($detail as $d): ?>
                <div class="struk-item">
                    <div class="struk-item-name">
                        <span><?php echo htmlspecialchars($d['nama_menu']); ?></span>
                        <span><?php echo rupiah($d['subtotal']); ?></span>
                    </div>
                    <div class="struk-item-detail">
                        <span><?php echo $d['jumlah']; ?> x <?php echo rupiah($d['harga']); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="struk-total-row"><span>Subtotal</span><span><?php echo rupiah($pesanan['total_harga']); ?></span></div>
        <div class="struk-total-row"><span>Ongkos Kirim</span><span>Rp0</span></div>
        <div class="struk-total-row grand"><span>TOTAL</span><span><?php echo rupiah($pesanan['total_harga']); ?></span></div>

        <div class="struk-payment">
            <div class="struk-payment-title">Pembayaran</div>
            <div class="struk-payment-row"><span>Metode</span><span><?php echo htmlspecialchars($label_bayar); ?></span></div>
            <?php if ($pesanan['metode_pembayaran'] === 'Transfer Bank' && isset($metode_pembayaran_list['Transfer Bank'])): ?>
                <div class="struk-payment-row"><span>Bank</span><span><?php echo htmlspecialchars($metode_pembayaran_list['Transfer Bank']['bank']); ?></span></div>
                <div class="struk-payment-row"><span>No. Rek.</span><span><?php echo htmlspecialchars($metode_pembayaran_list['Transfer Bank']['no_rekening']); ?></span></div>
            <?php endif; ?>
        </div>

        <div class="struk-status">Status: <?php echo htmlspecialchars($pesanan['status']); ?></div>

        <div class="struk-footer">
            Terima kasih telah berbelanja di Kedai Nabela!<br>
            Simpan struk ini sebagai bukti pemesanan.
        </div>

        <div class="struk-barcode">*<?php echo $kode_struk; ?>*</div>
    </div>

<?php endif; ?>

</body>
</html>
