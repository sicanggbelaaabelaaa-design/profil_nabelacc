<?php
/*
 * =============================================================
 * KONFIGURASI METODE PEMBAYARAN
 * -------------------------------------------------------------
 * Ganti nilai-nilai di bawah sesuai info pembayaran kedai kamu.
 * File ini dipakai di keranjang.php (checkout) dan selesai.php
 * (instruksi pembayaran).
 * =============================================================
 */

$metode_pembayaran_list = [
    'COD' => [
        'label'   => 'Bayar di Tempat (COD)',
        'icon'    => 'fa-money-bill-wave',
        'catatan' => 'Bayar tunai langsung ke kurir/kasir saat pesanan diantar atau diambil.',
    ],
    'Transfer Bank' => [
        'label'   => 'Transfer Bank',
        'icon'    => 'fa-building-columns',
        'catatan' => 'Transfer ke rekening berikut, lalu kirim bukti transfer ke WhatsApp admin.',
        'bank'          => 'Bank BCA',
        'no_rekening'   => '1234567890',
        'atas_nama'     => 'Nabela Chusnul Chotimah',
    ],
    'QRIS' => [
        'label'   => 'QRIS',
        'icon'    => 'fa-qrcode',
        'catatan' => 'Scan kode QRIS berikut menggunakan aplikasi e-wallet/m-banking apa pun, lalu kirim bukti pembayaran ke WhatsApp admin.',
        'gambar_qris' => 'qris.png', // taruh file gambar QRIS di folder uploads/ dengan nama ini
    ],
];
?>
