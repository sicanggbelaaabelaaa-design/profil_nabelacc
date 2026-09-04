<?php
include 'fungsi.php';
require_login('customer');
include 'koneksi.php';
include 'config_pembayaran.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$pesanan = null;
$detail = [];

if ($id > 0) {
    $q_pesanan = mysqli_query($koneksi, "SELECT * FROM pesanan WHERE id = $id");
    $pesanan = mysqli_fetch_assoc($q_pesanan);

    // Pastikan pesanan ini benar milik customer yang sedang login
    if ($pesanan && (int) $pesanan['user_id'] !== (int) $_SESSION['user']['id']) {
        $pesanan = null;
    }

    if ($pesanan) {
        $q_detail = mysqli_query($koneksi, "SELECT * FROM detail_pesanan WHERE pesanan_id = $id");
        while ($row = mysqli_fetch_assoc($q_detail)) {
            $detail[] = $row;
        }
    }
}

// Nomor WhatsApp admin kedai (silakan ganti dengan nomor asli, format 62xxxxxxxxxx)
$wa_admin = '6281234567890';
$pesan_wa = '';
if ($pesanan) {
    $label_bayar = $metode_pembayaran_list[$pesanan['metode_pembayaran']]['label'] ?? $pesanan['metode_pembayaran'];
    $pesan_wa = "Halo, saya {$pesanan['nama_pembeli']} ingin konfirmasi pesanan #{$pesanan['id']} sebesar " . rupiah($pesanan['total_harga']) . " (pembayaran: $label_bayar)";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Diterima | Kedai Nabela</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-body: #16191E; --card-bg: #1E222A; --sidebar-bg: #1B1E25; --item-bg: #252A34;
            --text-main: #E2E8F0; --text-muted: #94A3B8; --accent: #E08D3C; --accent-hover: #C97A2E;
            --border-color: #2D333F;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-body); color: var(--text-main); line-height: 1.6; padding: 40px 15px; }

        .wrapper { max-width: 560px; margin: 0 auto; }

        .not-found { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        .not-found a { color: var(--accent); }

        .success-box { text-align: center; margin-bottom: 26px; }
        .success-icon { width: 64px; height: 64px; border-radius: 50%; background: rgba(224,141,60,0.15); color: var(--accent); font-size: 26px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
        .success-box h1 { font-size: 21px; font-weight: 700; margin-bottom: 6px; }
        .success-box p { font-size: 13.5px; color: var(--text-muted); }

        .invoice-box { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 22px; }
        .invoice-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 14px; border-bottom: 1px dashed var(--border-color); }
        .invoice-id { font-size: 13px; font-weight: 700; }
        .invoice-status { font-size: 11px; background: var(--item-bg); border: 1px solid var(--border-color); color: var(--accent); padding: 4px 10px; border-radius: 20px; font-weight: 700; }

        .invoice-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 8px; color: var(--text-muted); }
        .invoice-row b { color: var(--text-main); font-weight: 600; }

        .item-line { display: flex; justify-content: space-between; font-size: 13px; padding: 6px 0; }
        .item-line span:first-child { color: var(--text-main); }
        .item-line span:last-child { color: var(--text-muted); }

        .invoice-total { display: flex; justify-content: space-between; font-size: 15px; font-weight: 700; margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--border-color); }
        .invoice-total span:last-child { color: var(--accent); }

        .action-row { display: flex; gap: 10px; margin-top: 22px; }
        .btn { flex: 1; text-align: center; padding: 12px; border-radius: 8px; font-size: 13px; font-weight: 700; text-decoration: none; }
        .btn-wa { background: #25D366; color: #0b1a0f; }
        .btn-menu { background: var(--item-bg); border: 1px solid var(--border-color); color: var(--text-main); }
        .btn-struk { background: var(--item-bg); border: 1px solid var(--accent); color: var(--accent); }

        .footer-link { text-align: center; margin-top: 30px; font-size: 12.5px; color: var(--text-muted); }
        .footer-link a { color: var(--accent); text-decoration: none; font-weight: 600; }

        .payment-box { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; margin-top: 14px; }
        .payment-head { display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 700; margin-bottom: 12px; }
        .payment-head i { color: var(--accent); }
        .payment-desc { font-size: 12.5px; color: var(--text-muted); line-height: 1.6; margin-bottom: 12px; }
        .payment-detail-row { display: flex; justify-content: space-between; align-items: center; background: var(--item-bg); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px 14px; margin-bottom: 8px; font-size: 13px; }
        .payment-detail-row span:first-child { color: var(--text-muted); }
        .payment-detail-row span:last-child { font-weight: 700; }
        .qris-box { text-align: center; background: var(--item-bg); border: 1px solid var(--border-color); border-radius: 10px; padding: 20px; }
        .qris-box img { max-width: 220px; width: 100%; border-radius: 8px; }
        .qris-placeholder { color: var(--text-muted); font-size: 12.5px; padding: 30px 10px; }
        .qris-placeholder i { font-size: 30px; margin-bottom: 10px; display: block; }
    </style>
</head>
<body>
<div class="wrapper">

<?php if (!$pesanan): ?>
    <div class="not-found">
        <p>Pesanan tidak ditemukan.</p>
        <p><a href="index.php">Kembali ke menu &rarr;</a></p>
    </div>
<?php else: ?>

    <div class="success-box">
        <div class="success-icon"><i class="fas fa-check"></i></div>
        <h1>Pesanan Berhasil Dibuat!</h1>
        <p>Terima kasih, <?php echo htmlspecialchars($pesanan['nama_pembeli']); ?>. Pesananmu sedang kami proses.</p>
    </div>

    <div class="invoice-box">
        <div class="invoice-head">
            <div class="invoice-id">Pesanan #<?php echo $pesanan['id']; ?></div>
            <div class="invoice-status"><?php echo htmlspecialchars($pesanan['status']); ?></div>
        </div>

        <div class="invoice-row"><span>Penerima</span><b><?php echo htmlspecialchars($pesanan['nama_pembeli']); ?></b></div>
        <div class="invoice-row"><span>WhatsApp</span><b><?php echo htmlspecialchars($pesanan['no_telepon']); ?></b></div>
        <div class="invoice-row"><span>Alamat</span><b style="text-align:right; max-width:60%;"><?php echo htmlspecialchars($pesanan['alamat']); ?></b></div>
        <div class="invoice-row"><span>Pembayaran</span><b><?php echo htmlspecialchars($metode_pembayaran_list[$pesanan['metode_pembayaran']]['label'] ?? $pesanan['metode_pembayaran']); ?></b></div>
        <?php if (!empty($pesanan['catatan'])): ?>
        <div class="invoice-row"><span>Catatan</span><b style="text-align:right; max-width:60%;"><?php echo htmlspecialchars($pesanan['catatan']); ?></b></div>
        <?php endif; ?>

        <div style="margin-top:16px; padding-top:14px; border-top: 1px dashed var(--border-color);">
            <?php foreach ($detail as $d): ?>
                <div class="item-line">
                    <span><?php echo $d['jumlah']; ?>x <?php echo htmlspecialchars($d['nama_menu']); ?></span>
                    <span><?php echo rupiah($d['subtotal']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="invoice-total">
            <span>Total Bayar</span>
            <span><?php echo rupiah($pesanan['total_harga']); ?></span>
        </div>
    </div>

    <?php $mp_terpilih = $metode_pembayaran_list[$pesanan['metode_pembayaran']] ?? null; ?>
    <?php if ($mp_terpilih): ?>
    <div class="payment-box">
        <div class="payment-head"><i class="fas <?php echo htmlspecialchars($mp_terpilih['icon']); ?>"></i> <?php echo htmlspecialchars($mp_terpilih['label']); ?></div>
        <div class="payment-desc"><?php echo htmlspecialchars($mp_terpilih['catatan']); ?></div>

        <?php if ($pesanan['metode_pembayaran'] === 'Transfer Bank'): ?>
            <div class="payment-detail-row"><span>Bank</span><span><?php echo htmlspecialchars($mp_terpilih['bank']); ?></span></div>
            <div class="payment-detail-row"><span>No. Rekening</span><span><?php echo htmlspecialchars($mp_terpilih['no_rekening']); ?></span></div>
            <div class="payment-detail-row"><span>Atas Nama</span><span><?php echo htmlspecialchars($mp_terpilih['atas_nama']); ?></span></div>
        <?php elseif ($pesanan['metode_pembayaran'] === 'QRIS'): ?>
            <div class="qris-box">
                <?php
                    $qris_path = 'uploads/' . ($mp_terpilih['gambar_qris'] ?? '');
                    if (!empty($mp_terpilih['gambar_qris']) && file_exists($qris_path)):
                ?>
                    <img src="<?php echo htmlspecialchars($qris_path); ?>" alt="Kode QRIS">
                <?php else: ?>
                    <div class="qris-placeholder">
                        <i class="fas fa-qrcode"></i>
                        Gambar QRIS belum diunggah admin.<br>Silakan hubungi admin lewat WhatsApp untuk kode QRIS.
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="action-row">
        <a href="struk.php?id=<?php echo $pesanan['id']; ?>" class="btn btn-struk"><i class="fas fa-receipt"></i> Lihat Struk</a>
        <a href="https://wa.me/<?php echo $wa_admin; ?>?text=<?php echo urlencode($pesan_wa); ?>" target="_blank" class="btn btn-wa"><i class="fab fa-whatsapp"></i> Konfirmasi via WA</a>
    </div>
    <div class="action-row" style="margin-top:10px;">
        <a href="index.php" class="btn btn-menu"><i class="fas fa-utensils"></i> Pesan Lagi</a>
    </div>

    <div class="footer-link">
        Dibuat oleh Nabela Chusnul Chotimah &mdash; <a href="<?php echo LINK_CV; ?>">Lihat Profil / CV</a>
    </div>

<?php endif; ?>

</div>
</body>
</html>
