# Sistem POS Apotek

Sistem Point of Sale (POS) untuk apotek dengan PHP PDO dan hak akses multi-role.

## Fitur

### 🔐 Sistem Autentikasi
- Login dengan username dan password
- 3 level akses: Admin, Kasir, Gudang
- Session management yang aman

### 👨‍💼 Admin
- Dashboard dengan statistik lengkap
- Manajemen user (CRUD)
- Manajemen kategori obat
- Manajemen supplier
- Laporan penjualan
- Akses ke semua fitur

### 🛒 Kasir
- Dashboard dengan statistik penjualan
- Point of Sale (POS) dengan interface modern
- Pencarian obat otomatis
- Perhitungan PPN otomatis
- Cetak struk transaksi
- Laporan penjualan

### 📦 Gudang
- Dashboard dengan statistik stok
- Manajemen data obat (CRUD)
- Sistem pembelian dari supplier
- Manajemen stok dengan alert
- Penyesuaian stok manual

## Instalasi

### 1. Persyaratan Sistem
- PHP 7.4 atau lebih baru
- MySQL 5.7 atau lebih baru
- Web server (Apache/Nginx)
- PDO MySQL extension

### 2. Setup Database
1. Buat database MySQL dengan nama `pos_apotek`
2. Import file `database/schema.sql` ke database
3. Konfigurasi koneksi database di `config/database.php`

### 3. Konfigurasi
Edit file `config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'pos_apotek';
private $username = 'root';
private $password = '';
```

### 4. Akses Sistem
- URL: `http://localhost/pos_apotek/`
- Otomatis redirect ke halaman login

## Akun Default

| Username | Password | Role  | Akses |
|----------|----------|-------|-------|
| admin    | password | Admin | Semua fitur |
| kasir1   | password | Kasir | POS dan laporan |
| gudang1  | password | Gudang| Obat dan stok |

## Struktur File

```
pos_apotek/
├── assets/
│   └── css/
│       └── style.css          # CSS utama
├── config/
│   ├── config.php             # Konfigurasi umum
│   └── database.php           # Koneksi database
├── database/
│   └── schema.sql             # Database schema
├── models/
│   ├── User.php               # Model user
│   ├── Obat.php               # Model obat
│   ├── Penjualan.php          # Model penjualan
│   ├── Pembelian.php          # Model pembelian
│   ├── KategoriObat.php       # Model kategori
│   └── Supplier.php           # Model supplier
├── index.php                  # Halaman utama
├── login.php                  # Halaman login
├── logout.php                 # Logout
├── dashboard.php              # Dashboard
├── obat.php                   # CRUD obat
├── penjualan.php              # POS kasir
├── struk.php                  # Cetak struk
├── pembelian.php              # Pembelian gudang
├── stok.php                   # Manajemen stok
├── laporan_penjualan.php      # Laporan penjualan
├── kategori.php               # CRUD kategori
├── supplier.php               # CRUD supplier
├── users.php                  # CRUD user
└── unauthorized.php           # Halaman akses ditolak
```

## Fitur Teknis

### 🔒 Keamanan
- Password hashing dengan `password_hash()`
- Input sanitization dengan `htmlspecialchars()`
- SQL injection prevention dengan PDO prepared statements
- Role-based access control

### 💾 Database
- Normalized database design
- Foreign key constraints
- Auto-generated transaction numbers
- Audit trail dengan timestamps

### 🎨 Interface
- Responsive design
- Modern CSS dengan grid dan flexbox
- Interactive JavaScript untuk POS
- Print-friendly receipt design

### 📊 Laporan
- Filter berdasarkan tanggal
- Summary statistik
- Export ke PDF (struk)
- Real-time dashboard updates

## Cara Penggunaan

### 1. Login
- Buka `http://localhost/pos_apotek/`
- Gunakan akun default atau yang dibuat admin

### 2. Kasir - Transaksi Penjualan
1. Masuk ke menu "Penjualan"
2. Cari obat dengan mengetik di search box
3. Klik obat untuk menambah ke keranjang
4. Atur jumlah dengan tombol +/- atau input manual
5. Masukkan jumlah bayar
6. Klik "Proses Transaksi"
7. Struk akan otomatis dicetak

### 3. Gudang - Pembelian Obat
1. Masuk ke menu "Pembelian"
2. Pilih supplier dan tanggal
3. Tambah item obat dengan jumlah dan harga
4. Simpan pembelian
5. Klik "Terima" untuk update stok

### 4. Admin - Manajemen
1. Buat user baru di "Manajemen User"
2. Kelola kategori di "Kategori Obat"
3. Kelola supplier di "Supplier"
4. Lihat laporan di "Laporan Penjualan"

## Troubleshooting

### Database Connection Error
- Pastikan MySQL service running
- Check username/password di `config/database.php`
- Pastikan database `pos_apotek` sudah dibuat

### Permission Denied
- Pastikan web server memiliki akses read/write ke folder
- Check file permissions (755 untuk folder, 644 untuk file)

### CSS Tidak Load
- Pastikan path ke `assets/css/style.css` benar
- Check browser console untuk error

## Pengembangan

### Menambah Fitur Baru
1. Buat model di folder `models/`
2. Buat controller/view di root
3. Update sidebar navigation
4. Update role permissions di `requireRole()`

### Custom Styling
- Edit `assets/css/style.css`
- Gunakan CSS Grid dan Flexbox
- Responsive design untuk mobile

## Lisensi

Project ini dibuat untuk keperluan pembelajaran dan dapat digunakan secara bebas.

## Support

Untuk pertanyaan atau bug report, silakan buat issue di repository atau hubungi developer.
