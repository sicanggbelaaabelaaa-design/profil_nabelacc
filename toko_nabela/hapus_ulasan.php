<?php
include 'fungsi.php';
require_login('admin');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id > 0) {
    mysqli_query($koneksi, "DELETE FROM ulasan WHERE id = $id");
}

header('Location: index.php#ulasan');
exit;
?>
