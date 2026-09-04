<?php
include 'koneksi.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    mysqli_query($koneksi, "DELETE FROM keahlian_coding WHERE id = $id");
}

header("Location: index.php");
exit;