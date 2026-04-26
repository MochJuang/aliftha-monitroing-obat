# Step by Step Pembuatan Aplikasi

Dokumen ini berisi tahapan pembuatan aplikasi **Monitoring Obat Kontrasepsi** menggunakan **Laravel** dan **MySQL** secara bertahap.

## Tujuan Dokumen

Dokumen ini dibuat agar proses development lebih terarah, rapi, dan mudah diikuti dari awal sampai aplikasi siap dipakai.

## Gambaran Tahapan Besar

1. Analisis kebutuhan
2. Setup project Laravel
3. Desain database
4. Implementasi autentikasi dan hak akses
5. Implementasi master data
6. Implementasi stok masuk
7. Implementasi stok keluar
8. Implementasi adjustment stok
9. Implementasi monitoring
10. Implementasi laporan
11. Testing
12. Deployment

## 1. Analisis Kebutuhan

### Yang dikerjakan

- Menentukan tujuan aplikasi
- Menentukan siapa pengguna aplikasi
- Menentukan proses bisnis utama
- Menentukan fitur utama

### Hasil yang harus ada

- deskripsi aplikasi
- daftar fitur
- daftar user
- flow bisnis dasar

### Output

- [perancangan-aplikasi.md](/Users/mochjuang/projects/php/gudang-obat-kb/docs/perancangan-aplikasi.md)

## 2. Menentukan Struktur Sistem

### Yang dikerjakan

- Menentukan entitas data
- Menentukan relasi tabel
- Menentukan modul Laravel
- Menentukan halaman yang dibutuhkan

### Hasil yang harus ada

- ERD
- daftar tabel
- daftar halaman
- daftar route awal

### Output

- [perancangan-aplikasi.md](/Users/mochjuang/projects/php/gudang-obat-kb/docs/perancangan-aplikasi.md)
- [laravel-migrations.md](/Users/mochjuang/projects/php/gudang-obat-kb/docs/laravel-migrations.md)
- [eloquent-models.md](/Users/mochjuang/projects/php/gudang-obat-kb/docs/eloquent-models.md)
- [routes-dan-halaman.md](/Users/mochjuang/projects/php/gudang-obat-kb/docs/routes-dan-halaman.md)

## 3. Setup Project Laravel

### Yang dikerjakan

1. Install Laravel
2. Buat database MySQL
3. Atur file `.env`
4. Jalankan project
5. Siapkan auth bawaan

### Langkah teknis

```bash
composer create-project laravel/laravel gudang-obat-kb
cd gudang-obat-kb
cp .env.example .env
php artisan key:generate
```

### Setting `.env`

Atur bagian database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gudang_obat_kb
DB_USERNAME=root
DB_PASSWORD=
```

### Jalankan project

```bash
php artisan serve
```

### Hasil yang harus ada

- project Laravel berhasil jalan
- koneksi database berhasil
- halaman welcome Laravel muncul

## 4. Menyiapkan Authentication

### Yang dikerjakan

- Menambahkan login
- Menambahkan logout
- Menambahkan profile user
- Menambahkan ubah password

### Opsi implementasi

- Laravel Breeze
- Laravel UI

### Rekomendasi

Gunakan **Laravel Breeze** karena ringan dan cocok untuk CRUD admin panel.

### Contoh langkah

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
npm run build
php artisan migrate
```

### Hasil yang harus ada

- halaman login
- session auth
- logout
- halaman profile

## 5. Membuat Migration Database

### Yang dikerjakan

- Membuat migration sesuai desain database
- Menentukan foreign key
- Menentukan index

### Urutan pengerjaan

1. roles
2. users
3. medicine_categories
4. units
5. medicines
6. stock_sources
7. distribution_destinations
8. stock_receipts
9. stock_receipt_items
10. medicine_batches
11. stock_distributions
12. stock_distribution_items
13. stock_adjustments
14. stock_adjustment_items
15. activity_logs

### Output

- migration Laravel
- database siap dipakai

### Referensi

- [laravel-migrations.md](/Users/mochjuang/projects/php/gudang-obat-kb/docs/laravel-migrations.md)

## 6. Membuat Seeder Awal

### Yang dikerjakan

- Seeder role
- Seeder user admin
- Seeder kategori
- Seeder satuan
- Seeder sample data jika perlu

### Hasil yang harus ada

- akun admin awal
- data master awal

### Referensi

- [sample-data.md](/Users/mochjuang/projects/php/gudang-obat-kb/docs/sample-data.md)

## 7. Membuat Model dan Relasi Eloquent

### Yang dikerjakan

- Membuat semua model
- Menambahkan `$fillable`
- Menambahkan relation antar model
- Menambahkan cast tanggal dan boolean

### Model inti

- `Role`
- `User`
- `Medicine`
- `MedicineBatch`
- `StockReceipt`
- `StockDistribution`
- `StockAdjustment`

### Hasil yang harus ada

- relasi antar tabel berjalan
- query dasar bisa dipakai

### Referensi

- [eloquent-models.md](/Users/mochjuang/projects/php/gudang-obat-kb/docs/eloquent-models.md)

## 8. Membuat Middleware Role dan Hak Akses

### Yang dikerjakan

- Membuat middleware role
- Batasi menu berdasarkan role
- Batasi route admin

### Contoh role

- `admin`
- `petugas_gudang`
- `pimpinan`

### Hasil yang harus ada

- admin bisa kelola semua data
- petugas bisa kelola transaksi
- pimpinan fokus ke monitoring dan laporan

## 9. Membuat Layout Admin Panel

### Yang dikerjakan

- Layout utama
- Sidebar
- Navbar
- Flash message
- Tabel data
- Form reusable

### Halaman dasar

- dashboard
- profile
- halaman master data
- halaman transaksi

### Hasil yang harus ada

- tampilan admin panel konsisten
- mudah dipakai untuk CRUD berikutnya

## 10. Membuat Modul Master Data

### Modul yang dibuat

1. kategori obat
2. satuan
3. obat
4. sumber obat
5. tujuan distribusi
6. user
7. role

### Flow pengerjaan

1. buat route resource
2. buat controller
3. buat request validation
4. buat blade index, create, edit, show
5. buat fitur search dan pagination

### Hasil yang harus ada

- semua data referensi dapat dikelola dari UI

## 11. Membuat Modul Stok Masuk

### Yang dikerjakan

- Form stok masuk
- Simpan header transaksi
- Simpan item transaksi
- Bentuk batch stok
- Simpan activity log

### Flow sistem

1. user input transaksi stok masuk
2. data masuk ke `stock_receipts`
3. detail item masuk ke `stock_receipt_items`
4. setiap item membuat data di `medicine_batches`
5. `qty_remaining` batch diisi sesuai jumlah masuk

### Hasil yang harus ada

- transaksi stok masuk berhasil
- batch terbentuk
- stok bisa dimonitor

### Referensi

- [flow-aksi-aplikasi.md](/Users/mochjuang/projects/php/gudang-obat-kb/docs/flow-aksi-aplikasi.md)

## 12. Membuat Modul Stok Keluar

### Yang dikerjakan

- Form stok keluar
- Pilih tujuan distribusi
- Pilih batch aktif
- Terapkan FEFO
- Kurangi `qty_remaining`

### Flow sistem

1. user input transaksi stok keluar
2. sistem pilih batch dengan expired terdekat
3. data masuk ke `stock_distributions`
4. detail masuk ke `stock_distribution_items`
5. stok batch dikurangi

### Hasil yang harus ada

- stok keluar tercatat
- stok batch berkurang
- tidak bisa keluar melebihi stok

### Catatan penting

- modul ini wajib pakai validasi stok
- sebaiknya proses simpan dibungkus transaction database

## 13. Membuat Modul Adjustment Stok

### Yang dikerjakan

- Form penyesuaian stok
- Ambil stok sistem dari batch
- Input stok fisik
- Hitung selisih
- Update batch

### Flow sistem

1. user pilih batch
2. sistem tampilkan `system_qty`
3. user input `actual_qty`
4. sistem hitung `difference_qty`
5. simpan ke `stock_adjustments`
6. simpan detail ke `stock_adjustment_items`
7. update `medicine_batches.qty_remaining`

### Hasil yang harus ada

- selisih stok tercatat
- stok batch menyesuaikan kondisi fisik

## 14. Membuat Modul Monitoring Stok

### Yang dikerjakan

- Halaman stok terkini
- Halaman stok minimum
- Halaman batch
- Halaman obat mendekati expired
- Kartu stok

### Sumber data

- `medicines`
- `medicine_batches`
- transaksi terkait

### Hasil yang harus ada

- user bisa melihat stok real-time
- user bisa tahu obat yang hampir habis
- user bisa tahu batch yang akan expired

## 15. Membuat Dashboard

### Yang dikerjakan

- Total jenis obat
- Total stok
- Stok masuk bulan ini
- Stok keluar bulan ini
- Obat di bawah minimum
- Batch mendekati expired
- Grafik pergerakan stok

### Hasil yang harus ada

- user langsung melihat kondisi gudang saat login

## 16. Membuat Modul Laporan

### Jenis laporan

- laporan stok obat
- laporan stok masuk
- laporan stok keluar
- laporan adjustment
- laporan obat expired
- laporan distribusi per fasilitas

### Fitur laporan

- filter tanggal
- filter obat
- filter tujuan distribusi
- print
- export PDF
- export Excel

### Hasil yang harus ada

- pimpinan dan petugas dapat membuat laporan dengan cepat

## 17. Membuat Activity Log

### Yang dikerjakan

- Catat login
- Catat tambah data
- Catat edit data
- Catat hapus atau cancel transaksi
- Catat adjustment

### Hasil yang harus ada

- histori aktivitas user tercatat
- memudahkan audit

## 18. Membuat Validasi dan Error Handling

### Yang dikerjakan

- Form request validation
- Validasi stok tidak boleh minus
- Validasi kode unik
- Validasi foreign key
- Menampilkan pesan error yang jelas

### Hasil yang harus ada

- input user lebih aman
- bug transaksi berkurang

## 19. Testing

### Yang dikerjakan

- Testing login
- Testing CRUD master data
- Testing stok masuk
- Testing stok keluar
- Testing adjustment
- Testing monitoring
- Testing laporan

### Jenis testing

- manual testing
- feature test Laravel
- database test

### Skenario penting

- stok masuk berhasil membentuk batch
- stok keluar mengurangi batch yang benar
- adjustment mengubah stok batch dengan benar
- stok tidak bisa minus

## 20. Refactor dan Rapikan Kode

### Yang dikerjakan

- Pindahkan logika bisnis ke service class
- Rapikan controller
- Pisahkan validation ke Form Request
- Tambahkan helper atau trait bila perlu

### Service yang disarankan

- `StockReceiptService`
- `StockDistributionService`
- `StockAdjustmentService`
- `StockMonitoringService`
- `ActivityLogService`

### Hasil yang harus ada

- kode lebih bersih
- lebih mudah dirawat
- lebih mudah dites

## 21. Persiapan Deployment

### Yang dikerjakan

- Setup production `.env`
- Optimasi cache config dan route
- Build asset frontend
- Siapkan database production
- Siapkan web server

### Langkah umum

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Hasil yang harus ada

- aplikasi siap dijalankan di server

## 22. Deployment

### Yang dikerjakan

- Upload source code ke server
- Import database atau migrate
- Atur permission folder
- Hubungkan domain
- Testing aplikasi di server

### Hasil yang harus ada

- aplikasi bisa diakses user
- login berjalan
- transaksi berjalan
- laporan berjalan

## 23. Maintenance dan Pengembangan Lanjutan

### Yang dikerjakan

- Backup database rutin
- Monitoring bug
- Tambah fitur bila dibutuhkan
- Optimasi performa query

### Fitur lanjutan yang bisa ditambah

- notifikasi stok minimum
- notifikasi batch mendekati expired
- export excel lanjutan
- approval distribusi
- audit stok opname berkala

## Rekomendasi Urutan Pengerjaan MVP

Kalau ingin cepat jadi versi awal, urutannya bisa seperti ini:

1. Setup Laravel dan database
2. Login dan role user
3. Master data obat, kategori, satuan
4. Master data sumber dan tujuan distribusi
5. Stok masuk
6. Monitoring stok
7. Stok keluar
8. Adjustment
9. Dashboard
10. Laporan
11. Activity log

## Checklist Siap Coding

- ERD selesai
- daftar halaman selesai
- migration plan selesai
- model relation plan selesai
- sample data tersedia
- flow aksi tersedia

## Referensi Dokumen yang Sudah Ada

- [perancangan-aplikasi.md](/Users/mochjuang/projects/php/gudang-obat-kb/docs/perancangan-aplikasi.md)
- [laravel-migrations.md](/Users/mochjuang/projects/php/gudang-obat-kb/docs/laravel-migrations.md)
- [eloquent-models.md](/Users/mochjuang/projects/php/gudang-obat-kb/docs/eloquent-models.md)
- [routes-dan-halaman.md](/Users/mochjuang/projects/php/gudang-obat-kb/docs/routes-dan-halaman.md)
- [sample-data.md](/Users/mochjuang/projects/php/gudang-obat-kb/docs/sample-data.md)
- [flow-aksi-aplikasi.md](/Users/mochjuang/projects/php/gudang-obat-kb/docs/flow-aksi-aplikasi.md)
