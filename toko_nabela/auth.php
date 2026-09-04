<?php
/*
 * =============================================================
 * AUTH.PHP - Modul autentikasi & manajemen role (admin & customer)
 * =============================================================
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'koneksi.php';

// Buat tabel users jika belum ada
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','customer') NOT NULL DEFAULT 'customer',
    no_telepon VARCHAR(20),
    alamat VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Tambahkan kolom user_id ke tabel pesanan jika belum ada (auto-migrasi)
$cek_kolom_user = mysqli_query($koneksi, "SHOW COLUMNS FROM pesanan LIKE 'user_id'");
if ($cek_kolom_user && mysqli_num_rows($cek_kolom_user) == 0) {
    mysqli_query($koneksi, "ALTER TABLE pesanan ADD COLUMN user_id INT NULL AFTER id");
}

// Tambahkan kolom metode_pembayaran ke tabel pesanan jika belum ada (auto-migrasi)
$cek_kolom_bayar = mysqli_query($koneksi, "SHOW COLUMNS FROM pesanan LIKE 'metode_pembayaran'");
if ($cek_kolom_bayar && mysqli_num_rows($cek_kolom_bayar) == 0) {
    mysqli_query($koneksi, "ALTER TABLE pesanan ADD COLUMN metode_pembayaran VARCHAR(30) NOT NULL DEFAULT 'COD' AFTER catatan");
}

// Tabel pengaturan toko: alamat, jam buka, kontak (satu baris data)
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS pengaturan_toko (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alamat VARCHAR(255) DEFAULT NULL,
    jam_buka VARCHAR(255) DEFAULT NULL,
    telepon VARCHAR(30) DEFAULT NULL,
    whatsapp VARCHAR(30) DEFAULT NULL,
    instagram VARCHAR(150) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$cek_pengaturan = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM pengaturan_toko");
if ($cek_pengaturan && mysqli_fetch_assoc($cek_pengaturan)['jml'] == 0) {
    mysqli_query($koneksi, "INSERT INTO pengaturan_toko (alamat, jam_buka, telepon, whatsapp, instagram) VALUES (
        'Jl. Contoh Alamat No. 123, Magetan, Jawa Timur',
        'Senin - Sabtu: 08.00 - 21.00\nMinggu: 09.00 - 22.00',
        '081234567890',
        '6281234567890',
        'https://instagram.com/kedainabela'
    )");
}

// Tabel ulasan / rating pelanggan
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS ulasan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    rating TINYINT NOT NULL,
    komentar TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

// Auto-seed akun admin default (dicek berdasarkan email spesifik,
// supaya tidak bentrok kalau sempat ada percobaan data lain)
$email_admin_default = 'admin@kedainabela.com';
$email_admin_esc = mysqli_real_escape_string($koneksi, $email_admin_default);
$cek_admin = mysqli_query($koneksi, "SELECT id, role FROM users WHERE email = '$email_admin_esc'");
$admin_row = $cek_admin ? mysqli_fetch_assoc($cek_admin) : null;

if (!$admin_row) {
    // Belum ada sama sekali -> buat baru
    $default_pass = password_hash('admin123', PASSWORD_DEFAULT);
    mysqli_query($koneksi, "INSERT INTO users (nama, email, password, role) VALUES
        ('Admin Kedai Nabela', '$email_admin_esc', '$default_pass', 'admin')");
} elseif ($admin_row['role'] !== 'admin') {
    // Ada tapi rolenya bukan admin -> jadikan admin
    mysqli_query($koneksi, "UPDATE users SET role = 'admin' WHERE id = " . (int) $admin_row['id']);
}

/**
 * Mengembalikan data user yang sedang login, atau null jika belum login.
 */
function current_user() {
    return $_SESSION['user'] ?? null;
}

function is_logged_in() {
    return isset($_SESSION['user']);
}

function is_admin() {
    return is_logged_in() && $_SESSION['user']['role'] === 'admin';
}

function is_customer() {
    return is_logged_in() && $_SESSION['user']['role'] === 'customer';
}

/**
 * Wajibkan user sudah login. Jika $role diisi ('admin'/'customer'),
 * wajibkan juga role-nya sesuai, kalau tidak akan ditolak.
 */
function require_login($role = null) {
    if (!is_logged_in()) {
        $tujuan = basename($_SERVER['PHP_SELF']);
        header('Location: login.php?redirect=' . urlencode($tujuan));
        exit;
    }
    if ($role !== null && $_SESSION['user']['role'] !== $role) {
        $pesan = $role === 'admin'
            ? 'Halaman ini khusus untuk admin.'
            : 'Halaman ini khusus untuk customer.';
        header('Location: index.php?akses_ditolak=' . urlencode($pesan));
        exit;
    }
}
?>
