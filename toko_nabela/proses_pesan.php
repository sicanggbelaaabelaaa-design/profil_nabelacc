<?php
include 'fungsi.php';
require_login('customer');
include 'koneksi.php';
include 'config_pembayaran.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: keranjang.php');
    exit;
}

if (empty($_SESSION['keranjang'])) {
    header('Location: keranjang.php?error=' . urlencode('Keranjang kosong, silakan pilih menu terlebih dahulu.'));
    exit;
}

$nama_pembeli = trim($_POST['nama_pembeli'] ?? '');
$no_telepon   = trim($_POST['no_telepon'] ?? '');
$alamat       = trim($_POST['alamat'] ?? '');
$catatan      = trim($_POST['catatan'] ?? '');
$metode_bayar = trim($_POST['metode_pembayaran'] ?? '');

if ($nama_pembeli === '' || $no_telepon === '' || $alamat === '') {
    header('Location: keranjang.php?error=' . urlencode('Mohon lengkapi nama, nomor WhatsApp, dan alamat.'));
    exit;
}

if (!array_key_exists($metode_bayar, $metode_pembayaran_list)) {
    header('Location: keranjang.php?error=' . urlencode('Metode pembayaran tidak valid, silakan pilih ulang.'));
    exit;
}

$nama_pembeli_esc = mysqli_real_escape_string($koneksi, $nama_pembeli);
$no_telepon_esc   = mysqli_real_escape_string($koneksi, $no_telepon);
$alamat_esc       = mysqli_real_escape_string($koneksi, $alamat);
$catatan_esc      = mysqli_real_escape_string($koneksi, $catatan);
$metode_bayar_esc = mysqli_real_escape_string($koneksi, $metode_bayar);

// Ambil data menu sesuai keranjang
$ids = array_map('intval', array_keys($_SESSION['keranjang']));
$in_clause = implode(',', $ids);

$result = mysqli_query($koneksi, "SELECT * FROM menu WHERE id IN ($in_clause)");
$daftar_item = [];
$total_harga = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $qty = $_SESSION['keranjang'][$row['id']];
    $subtotal = $qty * $row['harga'];
    $total_harga += $subtotal;
    $daftar_item[] = [
        'menu_id' => $row['id'],
        'nama' => $row['nama_menu'],
        'harga' => $row['harga'],
        'qty' => $qty,
        'subtotal' => $subtotal,
    ];
}

if (count($daftar_item) === 0) {
    header('Location: keranjang.php?error=' . urlencode('Keranjang kosong, silakan pilih menu terlebih dahulu.'));
    exit;
}

// Simpan header pesanan
$user_id = $_SESSION['user']['id'];
mysqli_query($koneksi, "INSERT INTO pesanan (user_id, nama_pembeli, no_telepon, alamat, catatan, metode_pembayaran, total_harga)
    VALUES ($user_id, '$nama_pembeli_esc', '$no_telepon_esc', '$alamat_esc', '$catatan_esc', '$metode_bayar_esc', $total_harga)");

$pesanan_id = mysqli_insert_id($koneksi);

// Simpan detail pesanan
foreach ($daftar_item as $item) {
    $nama_esc = mysqli_real_escape_string($koneksi, $item['nama']);
    mysqli_query($koneksi, "INSERT INTO detail_pesanan (pesanan_id, menu_id, nama_menu, harga, jumlah, subtotal)
        VALUES ($pesanan_id, {$item['menu_id']}, '$nama_esc', {$item['harga']}, {$item['qty']}, {$item['subtotal']})");
}

// Kosongkan keranjang
$_SESSION['keranjang'] = [];

header('Location: selesai.php?id=' . $pesanan_id);
exit;
?>
