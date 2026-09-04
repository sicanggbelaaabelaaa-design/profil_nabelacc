WEB PENJUALAN MAKANAN & MINUMAN - KEDAI NABELA
================================================
Web dinamis (PHP + MySQL) dengan 2 role: ADMIN (kelola menu & pesanan) dan
CUSTOMER (belanja & checkout). Semua fitur belanja & kelola menu sekarang
wajib login sesuai role masing-masing.

AKUN LOGIN
- Admin (dibuat otomatis saat pertama kali dibuka):
    Email    : admin@kedainabela.com
    Password : admin123
  (Segera ganti password ini lewat database kalau sudah dipakai serius.)
- Customer: daftar sendiri lewat tombol "Daftar" di halaman utama / login.php.

CARA MENJALANKAN (XAMPP / Laragon)
1. Struktur foldernya: toko_nabela ada DI DALAM folder profil_nabela.
       htdocs/
         profil_nabela/
           index.php        <- CV
           toko_nabela/     <- folder ini (toko)
             index.php

2. Buka phpMyAdmin, import file database.sql milik toko_nabela (otomatis
   membuat database "toko_nabela" beserta tabel & 12 menu contoh). Import
   juga database.sql milik profil_nabela untuk database "profil_nabela".

3. Buka http://localhost/profil_nabela/toko_nabela/ di browser.

MENGHUBUNGKAN KE HALAMAN CV
Buka file fungsi.php, cari baris:
    define('LINK_CV', '../index.php');
Sesuaikan path-nya:
 - Kalau strukturnya seperti di atas (toko_nabela di dalam profil_nabela),
   path di atas SUDAH BENAR, tidak perlu diubah.
 - Kalau sudah online / di-hosting, ganti dengan URL penuh, misalnya:
    define('LINK_CV', 'https://domainkamu.com/profil_nabela/');
Tombol "Profil Pembuat" di navbar dan link di footer setiap halaman otomatis
akan mengarah ke sana.

MENGUBAH NOMOR WHATSAPP ADMIN
Buka file selesai.php, cari baris:
    $wa_admin = '6281234567890';
Ganti dengan nomor WhatsApp kedai/toko kamu (format 62xxxxxxxxxx, tanpa
tanda + atau 0 di depan).

STRUKTUR FILE
- database.sql        -> skema & data awal database
- koneksi.php          -> koneksi ke MySQL
- auth.php              -> session, tabel users, auto-seed admin, helper role
- fungsi.php            -> format rupiah, konfigurasi link CV (otomatis load auth.php)
- login.php             -> form login (admin & customer, redirect sesuai role)
- register.php          -> pendaftaran akun customer baru
- logout.php            -> keluar / hapus sesi
- index.php             -> halaman menu (tampilan beda untuk admin vs customer)
- tambah_menu.php       -> [ADMIN] tambah menu baru (nama, harga, kategori, foto)
- edit_menu.php         -> [ADMIN] edit menu, termasuk ganti/hapus foto
- hapus_menu.php        -> [ADMIN] hapus menu beserta foto terkait
- pesanan_masuk.php     -> [ADMIN] daftar semua pesanan customer + ubah status
- update_status_pesanan.php -> [ADMIN] handler ubah status pesanan
- atur_toko.php         -> [ADMIN] atur alamat, jam buka, dan kontak toko
- tambah_ulasan.php     -> [CUSTOMER] handler simpan ulasan & rating
- hapus_ulasan.php      -> [ADMIN] handler hapus ulasan
- update_keranjang.php  -> [CUSTOMER] proses tambah / kurang / hapus item keranjang
- keranjang.php         -> [CUSTOMER] halaman keranjang + form checkout
- proses_pesan.php      -> [CUSTOMER] simpan pesanan ke database
- selesai.php           -> [CUSTOMER] halaman konfirmasi pesanan + instruksi pembayaran
- riwayat_pesanan.php   -> [CUSTOMER] daftar semua pesanan customer + link ke struk
- struk.php             -> struk pembelian yang bisa dicetak/print (customer lihat miliknya, admin lihat semua)
- config_pembayaran.php -> pengaturan metode pembayaran (COD, Transfer Bank, QRIS)
- uploads/              -> folder tempat foto menu & gambar QRIS tersimpan

PEMBAGIAN ROLE
- ADMIN: kelola menu (tambah/edit/hapus + foto) dan lihat/ubah status semua
  pesanan yang masuk lewat halaman "Pesanan Masuk".
- CUSTOMER: browsing menu, tambah ke keranjang, checkout, dan melihat
  konfirmasi pesanan miliknya sendiri saja.
- Tamu (belum login) tetap bisa melihat daftar menu, tapi begitu klik
  "Tambah" ke keranjang akan diarahkan ke halaman login/daftar dulu.

FITUR FOTO MENU
Setiap menu bisa diberi foto lewat tombol "Tambah Menu Baru" di halaman
utama, atau tombol "Edit" pada tiap kartu menu (khusus admin). Format yang
didukung: JPG, PNG, WEBP (maksimal 2MB). Kalau foto tidak diisi, kartu menu
akan menampilkan ikon bawaan sebagai gantinya. Pastikan folder "uploads"
bisa ditulis oleh server (permission writable) supaya proses upload berhasil.

METODE PEMBAYARAN
Ada 3 metode bawaan: COD (bayar di tempat), Transfer Bank, dan QRIS. Semua
diatur di satu file: config_pembayaran.php.
- Untuk Transfer Bank: ganti nilai 'bank', 'no_rekening', dan 'atas_nama'
  sesuai rekening kedai kamu.
- Untuk QRIS: upload gambar kode QRIS ke folder toko_nabela/uploads/ dengan
  nama file sesuai yang ditulis di 'gambar_qris' (default: qris.png). Kalau
  belum diupload, customer akan diarahkan untuk menghubungi WhatsApp admin.
- Bisa juga menambah metode baru (misal E-Wallet/DANA/GoPay) dengan
  menambah entri baru di array $metode_pembayaran_list.
Customer memilih metode saat checkout di keranjang.php, lalu instruksi
pembayaran (nomor rekening / kode QRIS) otomatis ditampilkan di halaman
konfirmasi pesanan (selesai.php) sesuai metode yang dipilih. Admin juga bisa
lihat metode pembayaran tiap pesanan di halaman "Pesanan Masuk".

CATATAN
- Menu contoh (12 item, 3 kategori: Makanan Utama, Minuman, Camilan &
  Dessert) bisa diedit langsung lewat halaman web (login sebagai admin) atau
  lewat tabel "menu" di phpMyAdmin.
- Keranjang disimpan di session per akun customer yang login, jadi otomatis
  kosong lagi kalau logout / session berakhir.

ALAMAT, JAM BUKA & KONTAK
Admin bisa atur alamat toko, jam buka, nomor telepon, WhatsApp, dan
Instagram lewat halaman "Atur Toko" (atur_toko.php). Info ini otomatis
tampil di halaman utama sebagai kartu info toko yang bisa diklik (WhatsApp
langsung buka chat, telepon langsung dial, Instagram langsung buka profil).

RATING & ULASAN
Customer yang sudah login bisa memberi rating (1-5 bintang) beserta
komentar lewat form di bagian bawah halaman utama (section "Ulasan
Pelanggan"). Rata-rata rating & jumlah ulasan otomatis dihitung dan
ditampilkan di kartu info toko serta ringkasan ulasan. Admin bisa hapus
ulasan yang tidak pantas langsung dari halaman yang sama.

STRUK PEMBELIAN
Setiap pesanan otomatis punya struk yang bisa dilihat (struk.php), didesain
menyerupai struk kasir/thermal printer lengkap dengan nomor struk, rincian
item, metode pembayaran, dan status pesanan.
- Customer: HANYA BISA MELIHAT struk, tidak bisa mencetak. Diakses lewat
  tombol "Lihat Struk" di halaman konfirmasi (selesai.php) atau di menu
  "Riwayat Pesanan". Tombol cetak tidak ditampilkan untuk customer, dan
  kalaupun mereka coba print manual (Ctrl+P / menu browser), struknya tidak
  akan ikut tercetak — yang muncul hanya pesan bahwa struk cetak resmi
  cuma bisa didapat dari admin.
- Admin: satu-satunya yang punya tombol "Cetak Struk" (memicu dialog print
  browser), bisa akses struk pesanan siapa pun lewat halaman "Pesanan
  Masuk" -> tombol "Lihat Struk".
- Keamanan: customer hanya bisa lihat struk miliknya sendiri (dicek lewat
  user_id), tidak bisa lihat punya orang lain walau ID pesanan ditebak.
