-- Database Schema for Profil Pribadi Nabela Chusnul Chotimah
CREATE DATABASE IF NOT EXISTS profil_nabela;
USE profil_nabela;

-- Table for Personal Information
CREATE TABLE IF NOT EXISTS profil (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    tentang TEXT,
    email VARCHAR(100),
    telepon VARCHAR(20),
    alamat VARCHAR(255),
    foto VARCHAR(255) DEFAULT 'default.jpg'
);

-- Table for Education History
CREATE TABLE IF NOT EXISTS riwayat_pendidikan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tingkat VARCHAR(50) NOT NULL,
    nama_instansi VARCHAR(150) NOT NULL,
    tahun_masuk VARCHAR(10),
    tahun_lulus VARCHAR(10),
    keterangan VARCHAR(255)
);

-- Insert Default Profil
INSERT INTO profil (nama, tentang, email, telepon, alamat, foto) VALUES 
('Nabela Chusnul Chotimah', 'Saya adalah seorang profesional yang berdedikasi, bersemangat belajar hal baru, dan memiliki motivasi tinggi dalam mengembangkan keahlian diri serta berorganisasi.', 'nabela@example.com', '081234567890', 'Indonesia', 'default.png');

-- Insert Sample Education Data
INSERT INTO riwayat_pendidikan (tingkat, nama_instansi, tahun_masuk, tahun_lulus, keterangan) VALUES 
('SD / MI', 'SD Negeri 1', '2012', '2018', 'Lulus dengan nilai memuaskan'),
('SMP / MTs', 'SMP Negeri 1', '2018', '2021', 'Aktif dalam organisasi OSIS'),
('SMA / SMK', 'SMA Negeri 1', '2021', '2024', 'Jurusan IPA / Kejuruan');

-- Table for Projek & Portofolio (Usaha)
CREATE TABLE IF NOT EXISTS usaha (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_usaha VARCHAR(150) NOT NULL,
    deskripsi TEXT,
    link_usaha VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Hubungkan CV ke web penjualan makanan & minuman (toko_nabela ada di dalam folder profil_nabela)
INSERT INTO usaha (nama_usaha, deskripsi, link_usaha) VALUES
('Kedai Nabela', 'Website dinamis untuk penjualan makanan & minuman: pelanggan dapat melihat menu, menambahkan ke keranjang, dan melakukan checkout secara online.', 'toko_nabela/index.php');
