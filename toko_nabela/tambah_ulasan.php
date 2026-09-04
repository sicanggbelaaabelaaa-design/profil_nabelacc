<?php
include 'fungsi.php';
require_login('customer');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating   = (int) ($_POST['rating'] ?? 0);
    $komentar = trim($_POST['komentar'] ?? '');

    if ($rating >= 1 && $rating <= 5) {
        $user_id     = (int) $_SESSION['user']['id'];
        $nama_esc    = mysqli_real_escape_string($koneksi, $_SESSION['user']['nama']);
        $komentar_esc = mysqli_real_escape_string($koneksi, $komentar);

        mysqli_query($koneksi, "INSERT INTO ulasan (user_id, nama, rating, komentar)
            VALUES ($user_id, '$nama_esc', $rating, '$komentar_esc')");
    }
}

header('Location: index.php#ulasan');
exit;
?>
