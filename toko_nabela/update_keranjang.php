<?php
include 'fungsi.php';
require_login('customer');

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    switch ($action) {
        case 'add':
            if (!isset($_SESSION['keranjang'][$id])) {
                $_SESSION['keranjang'][$id] = 0;
            }
            $_SESSION['keranjang'][$id]++;
            break;

        case 'increase':
            if (isset($_SESSION['keranjang'][$id])) {
                $_SESSION['keranjang'][$id]++;
            }
            break;

        case 'decrease':
            if (isset($_SESSION['keranjang'][$id])) {
                $_SESSION['keranjang'][$id]--;
                if ($_SESSION['keranjang'][$id] <= 0) {
                    unset($_SESSION['keranjang'][$id]);
                }
            }
            break;

        case 'remove':
            unset($_SESSION['keranjang'][$id]);
            break;
    }
}

// Redirect kembali ke halaman asal
if ($action === 'add') {
    header('Location: index.php?sukses=1');
} else {
    header('Location: keranjang.php');
}
exit;
?>
