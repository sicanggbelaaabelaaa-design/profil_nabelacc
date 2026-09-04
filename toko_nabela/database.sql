-- Database Skema untuk Web Penjualan Makanan & Minuman (Kedai Nabela)
CREATE DATABASE IF NOT EXISTS toko_nabela;
USE toko_nabela;

-- Tabel Menu (Makanan & Minuman)
CREATE TABLE IF NOT EXISTS menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kategori VARCHAR(50) NOT NULL,
    nama_menu VARCHAR(150) NOT NULL,
    deskripsi VARCHAR(255),
    harga INT NOT NULL,
    icon VARCHAR(50) DEFAULT 'fa-utensils',
    foto VARCHAR(255) DEFAULT NULL,
    tersedia TINYINT(1) DEFAULT 1
);

-- Tabel Users (akun admin & customer)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','customer') NOT NULL DEFAULT 'customer',
    no_telepon VARCHAR(20),
    alamat VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- Catatan: akun admin default (admin@kedainabela.com / admin123) dibuat
-- OTOMATIS oleh auth.php saat halaman pertama kali diakses, dengan
-- password di-hash langsung oleh PHP (lebih aman daripada hash statis di sini).

-- Tabel Pesanan (Header)
CREATE TABLE IF NOT EXISTS pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    nama_pembeli VARCHAR(100) NOT NULL,
    no_telepon VARCHAR(20) NOT NULL,
    alamat VARCHAR(255) NOT NULL,
    catatan VARCHAR(255),
    metode_pembayaran VARCHAR(30) NOT NULL DEFAULT 'COD',
    total_harga INT NOT NULL DEFAULT 0,
    status VARCHAR(30) DEFAULT 'Menunggu Konfirmasi',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabel Detail Pesanan (Isi Keranjang yang sudah di-checkout)
CREATE TABLE IF NOT EXISTS detail_pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    menu_id INT,
    nama_menu VARCHAR(150) NOT NULL,
    harga INT NOT NULL,
    jumlah INT NOT NULL,
    subtotal INT NOT NULL,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE
);

-- Tabel Pengaturan Toko (alamat, jam buka, kontak)
CREATE TABLE IF NOT EXISTS pengaturan_toko (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alamat VARCHAR(255) DEFAULT NULL,
    jam_buka VARCHAR(255) DEFAULT NULL,
    telepon VARCHAR(30) DEFAULT NULL,
    whatsapp VARCHAR(30) DEFAULT NULL,
    instagram VARCHAR(150) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO pengaturan_toko (alamat, jam_buka, telepon, whatsapp, instagram) VALUES (
    'Jl. Contoh Alamat No. 123, Magetan, Jawa Timur',
    'Senin - Sabtu: 08.00 - 21.00\nMinggu: 09.00 - 22.00',
    '081234567890',
    '6281234567890',
    'https://instagram.com/kedainabela'
);

-- Tabel Ulasan / Rating Pelanggan
CREATE TABLE IF NOT EXISTS ulasan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    rating TINYINT NOT NULL,
    komentar TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Data Menu Awal
INSERT INTO menu (kategori, nama_menu, deskripsi, harga, icon) VALUES
('Makanan Utama', 'Nasi Goreng Spesial', 'Nasi goreng dengan telur, ayam suwir, dan acar segar', 18000, 'fa-bowl-rice'),
('Makanan Utama', 'Mie Ayam Bakso', 'Mie ayam kuah gurih lengkap dengan bakso sapi', 15000, 'fa-bowl-food'),
('Makanan Utama', 'Ayam Geprek Sambal Matah', 'Ayam crispy digeprek dengan sambal matah pedas', 20000, 'fa-drumstick-bite'),
('Makanan Utama', 'Nasi Uduk Komplit', 'Nasi uduk dengan ayam goreng, tempe, dan sambal kacang', 17000, 'fa-plate-wheat'),
('Minuman', 'Es Teh Manis', 'Teh manis segar dengan es batu', 5000, 'fa-glass-water'),
('Minuman', 'Es Jeruk Segar', 'Perasan jeruk asli tanpa pengawet', 7000, 'fa-lemon'),
('Minuman', 'Kopi Susu Gula Aren', 'Kopi susu creamy dengan gula aren asli', 12000, 'fa-mug-hot'),
('Minuman', 'Es Cendol', 'Cendol khas dengan santan dan gula merah', 10000, 'fa-mug-saucer'),
('Camilan & Dessert', 'Pisang Goreng Coklat Keju', 'Pisang goreng crispy topping coklat dan keju', 12000, 'fa-cookie-bite'),
('Camilan & Dessert', 'Roti Bakar Susu', 'Roti bakar lembut dengan olesan susu kental manis', 13000, 'fa-bread-slice'),
('Camilan & Dessert', 'Risoles Mayo', 'Risoles isi sosis, telur, dan mayonaise', 9000, 'fa-cheese'),
('Camilan & Dessert', 'Puding Coklat', 'Puding coklat lembut dengan vla vanilla', 8000, 'fa-ice-cream');
