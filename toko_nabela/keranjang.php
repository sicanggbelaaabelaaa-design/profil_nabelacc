<?php
include 'fungsi.php';
require_login('customer');
include 'koneksi.php';

$items = [];
$total = 0;

if (!empty($_SESSION['keranjang'])) {
    $ids = array_keys($_SESSION['keranjang']);
    $ids_safe = array_map('intval', $ids);
    $in_clause = implode(',', $ids_safe);
    if ($in_clause !== '') {
        $result = mysqli_query($koneksi, "SELECT * FROM menu WHERE id IN ($in_clause)");
        while ($row = mysqli_fetch_assoc($result)) {
            $qty = $_SESSION['keranjang'][$row['id']];
            $subtotal = $qty * $row['harga'];
            $total += $subtotal;
            $items[] = [
                'id' => $row['id'],
                'nama' => $row['nama_menu'],
                'icon' => $row['icon'],
                'harga' => $row['harga'],
                'qty' => $qty,
                'subtotal' => $subtotal,
            ];
        }
    }
}

$error = $_GET['error'] ?? '';

// Ambil data akun customer untuk auto-isi form
$q_profil = mysqli_query($koneksi, "SELECT * FROM users WHERE id = " . (int) $_SESSION['user']['id']);
$profil_user = mysqli_fetch_assoc($q_profil);

include 'config_pembayaran.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang | Kedai Nabela</title>
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

        .alert-error { background: rgba(220,38,38,0.12); border: 1px solid rgba(220,38,38,0.3); color: #FCA5A5; padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 20px; }

        .cart-item { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 10px; padding: 16px; display: flex; align-items: center; gap: 14px; margin-bottom: 12px; }
        .cart-icon { width: 44px; height: 44px; border-radius: 8px; background: var(--item-bg); display: flex; align-items: center; justify-content: center; color: var(--accent); font-size: 16px; flex-shrink: 0; }
        .cart-info { flex-grow: 1; }
        .cart-nama { font-size: 14px; font-weight: 700; }
        .cart-harga { font-size: 12.5px; color: var(--text-muted); }
        .qty-control { display: flex; align-items: center; gap: 8px; }
        .qty-btn { width: 26px; height: 26px; border-radius: 6px; background: var(--item-bg); border: 1px solid var(--border-color); color: var(--text-main); display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 12px; }
        .qty-btn:hover { border-color: var(--accent); color: var(--accent); }
        .qty-num { font-size: 13px; font-weight: 700; width: 20px; text-align: center; }
        .cart-subtotal { font-size: 13.5px; font-weight: 700; color: var(--accent); width: 90px; text-align: right; }
        .btn-remove { color: #FCA5A5; text-decoration: none; font-size: 14px; margin-left: 8px; }

        .empty-cart { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        .empty-cart i { font-size: 40px; margin-bottom: 14px; color: var(--border-color); }
        .empty-cart a { color: var(--accent); text-decoration: none; font-weight: 600; }

        .summary-box { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 10px; padding: 20px; margin-top: 20px; }
        .summary-total { display: flex; justify-content: space-between; font-size: 16px; font-weight: 700; margin-bottom: 18px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color); }
        .summary-total span:last-child { color: var(--accent); }

        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 12.5px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; }
        .form-group input, .form-group textarea { width: 100%; background: var(--item-bg); border: 1px solid var(--border-color); color: var(--text-main); padding: 10px 12px; border-radius: 7px; font-size: 13.5px; font-family: inherit; }
        .form-group textarea { resize: vertical; min-height: 60px; }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: var(--accent); }

        .btn-checkout { width: 100%; background: var(--accent); color: #16191E; border: none; padding: 13px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; margin-top: 6px; }
        .btn-checkout:hover { background: var(--accent-hover); }

        .payment-options { display: flex; flex-direction: column; gap: 8px; }
        .payment-option { display: flex; align-items: center; gap: 12px; background: var(--item-bg); border: 1px solid var(--border-color); border-radius: 8px; padding: 12px 14px; cursor: pointer; transition: border-color 0.15s ease; }
        .payment-option:hover { border-color: var(--accent); }
        .payment-option input[type="radio"] { accent-color: var(--accent); width: 16px; height: 16px; flex-shrink: 0; }
        .payment-option i.payment-icon { color: var(--accent); font-size: 16px; width: 20px; text-align: center; flex-shrink: 0; }
        .payment-option-text { flex-grow: 1; }
        .payment-option-label { font-size: 13.5px; font-weight: 700; }
        .payment-option-desc { font-size: 11.5px; color: var(--text-muted); margin-top: 2px; }
        .payment-option:has(input:checked) { border-color: var(--accent); background: var(--sidebar-bg); }
    </style>
</head>
<body>

<div class="navbar">
    <div class="brand"><i class="fas fa-utensils"></i> Kedai Nabela</div>
    <a href="index.php" class="nav-link"><i class="fas fa-arrow-left"></i> Kembali ke Menu</a>
</div>

<div class="wrapper">
    <div class="page-title"><i class="fas fa-cart-shopping"></i> Keranjang Belanja</div>

    <?php if ($error): ?>
        <div class="alert-error"><i class="fas fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (count($items) === 0): ?>
        <div class="empty-cart">
            <i class="fas fa-cart-shopping"></i>
            <p>Keranjang kamu masih kosong.</p>
            <p><a href="index.php">Lihat menu &rarr;</a></p>
        </div>
    <?php else: ?>

        <?php foreach ($items as $it): ?>
            <div class="cart-item">
                <div class="cart-icon"><i class="fas <?php echo htmlspecialchars($it['icon']); ?>"></i></div>
                <div class="cart-info">
                    <div class="cart-nama"><?php echo htmlspecialchars($it['nama']); ?></div>
                    <div class="cart-harga"><?php echo rupiah($it['harga']); ?> / porsi</div>
                </div>
                <div class="qty-control">
                    <a href="update_keranjang.php?action=decrease&id=<?php echo $it['id']; ?>" class="qty-btn"><i class="fas fa-minus"></i></a>
                    <div class="qty-num"><?php echo $it['qty']; ?></div>
                    <a href="update_keranjang.php?action=increase&id=<?php echo $it['id']; ?>" class="qty-btn"><i class="fas fa-plus"></i></a>
                </div>
                <div class="cart-subtotal"><?php echo rupiah($it['subtotal']); ?></div>
                <a href="update_keranjang.php?action=remove&id=<?php echo $it['id']; ?>" class="btn-remove" onclick="return confirm('Hapus menu ini dari keranjang?');"><i class="fas fa-trash"></i></a>
            </div>
        <?php endforeach; ?>

        <div class="summary-box">
            <div class="summary-total">
                <span>Total Belanja</span>
                <span><?php echo rupiah($total); ?></span>
            </div>

            <form action="proses_pesan.php" method="POST">
                <div class="form-group">
                    <label>Nama Penerima</label>
                    <input type="text" name="nama_pembeli" required placeholder="Nama lengkap kamu" value="<?php echo htmlspecialchars($profil_user['nama'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Nomor WhatsApp</label>
                    <input type="text" name="no_telepon" required placeholder="Contoh: +62 896-7189-6069" value="<?php echo htmlspecialchars($profil_user['no_telepon'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Alamat Pengiriman</label>
                    <textarea name="alamat" required placeholder="Alamat lengkap untuk pengantaran"><?php echo htmlspecialchars($profil_user['alamat'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Catatan (opsional)</label>
                    <textarea name="catatan" placeholder="Contoh: tidak pedas, tanpa bawang, dsb."></textarea>
                </div>
                <div class="form-group">
                    <label>Metode Pembayaran</label>
                    <div class="payment-options">
                        <?php $first = true; ?>
                        <?php foreach ($metode_pembayaran_list as $kode => $mp): ?>
                            <label class="payment-option">
                                <input type="radio" name="metode_pembayaran" value="<?php echo htmlspecialchars($kode); ?>" <?php echo $first ? 'checked' : ''; ?>>
                                <i class="fas <?php echo htmlspecialchars($mp['icon']); ?> payment-icon"></i>
                                <div class="payment-option-text">
                                    <div class="payment-option-label"><?php echo htmlspecialchars($mp['label']); ?></div>
                                    <div class="payment-option-desc"><?php echo htmlspecialchars($mp['catatan']); ?></div>
                                </div>
                            </label>
                            <?php $first = false; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="submit" class="btn-checkout"><i class="fas fa-check"></i> Buat Pesanan</button>
            </form>
        </div>

    <?php endif; ?>
</div>

</body>
</html>
