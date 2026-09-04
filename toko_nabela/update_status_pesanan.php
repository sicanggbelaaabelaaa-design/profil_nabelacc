<?php
include 'fungsi.php';
require_login('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int) ($_POST['id'] ?? 0);
    $status = mysqli_real_escape_string($koneksi, $_POST['status'] ?? '');

    $status_valid = ['Menunggu Konfirmasi', 'Diproses', 'Diantar', 'Selesai', 'Dibatalkan'];

    if ($id > 0 && in_array($status, $status_valid)) {
        mysqli_query($koneksi, "UPDATE pesanan SET status = '$status' WHERE id = $id");
    }
}

header('Location: pesanan_masuk.php?sukses=1');
exit;
?>
