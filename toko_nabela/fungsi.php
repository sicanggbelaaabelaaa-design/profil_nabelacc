<?php
include_once 'auth.php'; // otomatis: session_start(), koneksi database, & helper role

/*
 * =============================================================
 * KONFIGURASI TAUTAN KE HALAMAN CV / PROFIL
 * -------------------------------------------------------------
 * Struktur folder saat ini: toko_nabela ada DI DALAM profil_nabela
 *   profil_nabela/
 *     index.php
 *     toko_nabela/
 *       index.php  (file ini ada di sini)
 * Jadi untuk naik ke CV, cukup satu tingkat ke atas ("../index.php").
 * Kalau sudah online (hosting), ganti dengan URL penuh, misalnya:
 *      define('LINK_CV', 'https://namadomainkamu.com/profil_nabela/');
 * =============================================================
 */
define('LINK_CV', '../index.php');

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = []; // format: [menu_id => jumlah]
}

function rupiah($angka) {
    return "Rp" . number_format((float)$angka, 0, ',', '.');
}

function total_item_keranjang() {
    return isset($_SESSION['keranjang']) ? array_sum($_SESSION['keranjang']) : 0;
}
?>

