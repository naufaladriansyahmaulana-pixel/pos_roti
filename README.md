# Sistem POS Roti

Sistem Point of Sale (POS) untuk toko roti dengan PHP PDO dan hak akses multi-role.

## Fitur

### 🔐 Sistem Autentikasi
- Login dengan username dan password
- 3 level akses: Admin, Kasir, Gudang
- Session management yang aman

### 👨‍💼 Admin
- Dashboard dengan statistik lengkap
- Manajemen user (CRUD)
- Manajemen kategori roti
- Manajemen vendor/supplier
- Laporan penjualan dan pembelian
- Akses ke semua fitur

### 🛒 Kasir
- Dashboard dengan statistik penjualan
- Point of Sale (POS) dengan interface modern
- Pencarian roti otomatis
- Perhitungan PPN otomatis
- Diskon per item
- Multiple metode pembayaran (Cash, QRIS, Transfer)
- Cetak struk transaksi
- Laporan penjualan

### 📦 Gudang
- Dashboard dengan statistik stok
- Manajemen data roti (CRUD)
- Sistem pembelian dari vendor
- Manajemen stok dengan alert
- Penyesuaian stok manual
- Laporan pembelian

## Instalasi

### 1. Persyaratan Sistem
- PHP 7.4 atau lebih baru
- MySQL 5.7 atau lebih baru
- Web server (Apache/Nginx)
- PDO MySQL extension

### 2. Setup Database
1. Buat database MySQL dengan nama `pos_roti`
2. Import file `database/schema.sql` ke database
3. Konfigurasi koneksi database di `config/database.php`

### 3. Konfigurasi
Edit file `config/database.php`:
```
php
private $host = 'localhost';
private $db_name = 'pos_roti';
private $username = 'root';
private $password = '';
```

### 4. Akses Sistem
- URL: `http://localhost/pos_roti/`
- Otomatis redirect ke halaman login

## Akun Default

| Username | Password | Role  | Akses |
|----------|----------|-------|-------|
| admin    | password | Admin | Semua fitur |
| kasir1   | password | Kasir | POS dan laporan |
| gudang1  | password | Gudang| Roti dan stok |

## Struktur File

```
pos_roti/
├── config/
│   ├── config.php             # Konfigurasi umum
│   └── database.php           # Koneksi database
├── models/
│   ├── User.php               # Model user
│   ├── Roti.php              # Model roti
│   ├── Penjualan.php         # Model penjualan
│   ├── Pembelian.php         # Model pembelian
│   ├── KategoriRoti.php      # Model kategori
│   ├── Vendor.php            # Model vendor
│   ├── Customer.php          # Model customer
│   └── Pengaturan.php        # Model pengaturan
├── database/
│   ├── schema.sql            # Database schema
│   ├── pos_roti.sql          # Data awal
│   └── add_nomor_rekening.sql # Migrasi
├── assets/
│   ├── roti_logo.png         # Logo
│   └── css/
│       ├── style.css         # CSS utama
│       └── dynamic.php       # CSS dinamis
├── uploads/
│   └── (gambar roti)
├── index.php                 # Halaman utama
├── login.php                 # Login
├── logout.php                # Logout
├── dashboard.php             # Dashboard
├── roti.php                  # CRUD roti
├── kategori.php              # CRUD kategori
├── vendor.php                # CRUD vendor
├── customer.php              # CRUD customer
├── users.php                 # CRUD user
├── penjualan.php             # POS kasir
├── struk.php                 # Cetak struk
├── pembelian.php             # Pembelian gudang
├── laporan_penjualan.php     # Laporan penjualan
├── laporan_pembelian.php     # Laporan pembelian
├── stok.php                  # Manajemen stok
├── pengaturan.php            # Pengaturan aplikasi
├── install.php               # Instalasi
└── unauthorized.php          # Akses ditolak
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
- Custom theme (bukan Bootstrap/Tailwind)
- Interactive JavaScript untuk POS
- Print-friendly receipt design
- Dynamic CSS berdasarkan pengaturan

### 📊 Laporan
- Filter berdasarkan tanggal
- Summary statistik
- Export ke PDF (struk)
- Real-time dashboard updates

## Cara Penggunaan

### 1. Login
- Buka `http://localhost/pos_roti/`
- Gunakan akun default atau yang dibuat admin

### 2. Kasir - Transaksi Penjualan
1. Masuk ke menu "Penjualan"
2. Cari roti dengan mengetik di search box
3. Klik roti untuk menambah ke keranjang
4. Atur jumlah dengan tombol +/- atau input manual
5. Masukkan jumlah bayar
6. Pilih metode pembayaran (Cash/QRIS/Transfer)
7. Klik "Proses Transaksi"
8. Struk akan otomatis dicetak

### 3. Gudang - Pembelian Roti
1. Masuk ke menu "Pembelian"
2. Pilih vendor dan tanggal
3. Tambah item roti dengan jumlah dan harga
4. Simpan pembelian
5. Klik "Terima" untuk update stok

### 4. Admin - Manajemen
1. Buat user baru di "Manajemen User"
2. Kelola kategori di "Kategori Roti"
3. Kelola vendor di "Vendor"
4. Kelola customer di "Customer"
5. Lihat laporan di "Laporan"
6. Atur aplikasi di "Pengaturan"

## Troubleshooting

### Database Connection Error
- Pastikan MySQL service running
- Check username/password di `config/database.php`
- Pastikan database `pos_roti` sudah dibuat

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
- Atau ubah warna di "Pengaturan" (dynamic.php)

## Lisensi

Project ini dibuat untuk keperluan pembelajaran dan dapat digunakan secara bebas.

## Support

Untuk pertanyaan atau bug report, silakan hubungi developer.
