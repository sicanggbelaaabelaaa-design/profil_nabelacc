<?php
include 'fungsi.php';
require_login('admin');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    $q = mysqli_query($koneksi, "SELECT foto FROM menu WHERE id = $id");
    $item = mysqli_fetch_assoc($q);

    if ($item && !empty($item['foto']) && file_exists('uploads/' . $item['foto'])) {
        unlink('uploads/' . $item['foto']);
    }

    mysqli_query($koneksi, "DELETE FROM menu WHERE id = $id");
}

header('Location: index.php');
exit;
?>
