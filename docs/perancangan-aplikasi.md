# Perancangan Awal Aplikasi Monitoring Obat Kontrasepsi

Dokumen ini berisi rancangan awal database dan daftar halaman untuk aplikasi **Monitoring Obat Kontrasepsi berbasis Laravel + MySQL**.

## Asumsi Bisnis

Beberapa asumsi yang dipakai supaya struktur data lebih rapi dan siap dikembangkan:

- Setiap pengguna memiliki satu role, misalnya `admin`, `petugas_gudang`, atau `pimpinan`.
- Stok masuk dicatat per transaksi penerimaan dan memiliki detail item.
- Stok disimpan per batch agar nomor batch dan tanggal kedaluwarsa bisa dipantau.
- Stok keluar dicatat per transaksi distribusi ke fasilitas kesehatan.
- Penyesuaian stok (`stock adjustment`) dipakai untuk koreksi selisih hasil stok opname.
- Laporan tidak perlu tabel khusus karena bisa dihasilkan dari tabel transaksi.

## ERD (DBDiagram / DBML)

```dbml
Table roles {
  id bigint [pk, increment]
  name varchar(50) [not null, unique]
  description varchar(255)
  created_at timestamp
  updated_at timestamp
}

Table users {
  id bigint [pk, increment]
  role_id bigint [not null, ref: > roles.id]
  name varchar(100) [not null]
  username varchar(50) [not null, unique]
  email varchar(100) [unique]
  phone varchar(20)
  password varchar(255) [not null]
  is_active boolean [not null, default: true]
  last_login_at timestamp
  created_at timestamp
  updated_at timestamp
}

Table medicine_categories {
  id bigint [pk, increment]
  name varchar(100) [not null, unique]
  description varchar(255)
  created_at timestamp
  updated_at timestamp
}

Table units {
  id bigint [pk, increment]
  name varchar(50) [not null, unique]
  symbol varchar(20) [not null]
  created_at timestamp
  updated_at timestamp
}

Table medicines {
  id bigint [pk, increment]
  category_id bigint [not null, ref: > medicine_categories.id]
  unit_id bigint [not null, ref: > units.id]
  code varchar(50) [not null, unique]
  name varchar(150) [not null]
  brand varchar(100)
  dosage varchar(100)
  minimum_stock int [not null, default: 0]
  description text
  is_active boolean [not null, default: true]
  created_at timestamp
  updated_at timestamp
}

Table stock_sources {
  id bigint [pk, increment]
  name varchar(150) [not null]
  source_type varchar(50) [not null, note: 'dinkes, bkkbn, supplier, lainnya']
  address text
  phone varchar(20)
  contact_person varchar(100)
  created_at timestamp
  updated_at timestamp
}

Table distribution_destinations {
  id bigint [pk, increment]
  code varchar(50) [not null, unique]
  name varchar(150) [not null]
  destination_type varchar(50) [not null, note: 'puskesmas, klinik, bidan, lainnya']
  address text
  phone varchar(20)
  contact_person varchar(100)
  is_active boolean [not null, default: true]
  created_at timestamp
  updated_at timestamp
}

Table stock_receipts {
  id bigint [pk, increment]
  receipt_number varchar(50) [not null, unique]
  source_id bigint [not null, ref: > stock_sources.id]
  received_date date [not null]
  received_by bigint [not null, ref: > users.id]
  notes text
  status varchar(20) [not null, default: 'posted', note: 'draft, posted, cancelled']
  created_at timestamp
  updated_at timestamp
}

Table stock_receipt_items {
  id bigint [pk, increment]
  receipt_id bigint [not null, ref: > stock_receipts.id]
  medicine_id bigint [not null, ref: > medicines.id]
  batch_number varchar(100) [not null]
  expired_at date [not null]
  quantity int [not null]
  unit_cost decimal(15,2) [default: 0]
  notes varchar(255)
  created_at timestamp
  updated_at timestamp
}

Table medicine_batches {
  id bigint [pk, increment]
  medicine_id bigint [not null, ref: > medicines.id]
  receipt_item_id bigint [not null, ref: > stock_receipt_items.id]
  batch_number varchar(100) [not null]
  expired_at date [not null]
  qty_received int [not null]
  qty_remaining int [not null]
  created_at timestamp
  updated_at timestamp
}

Table stock_distributions {
  id bigint [pk, increment]
  distribution_number varchar(50) [not null, unique]
  destination_id bigint [not null, ref: > distribution_destinations.id]
  distributed_date date [not null]
  distributed_by bigint [not null, ref: > users.id]
  notes text
  status varchar(20) [not null, default: 'posted', note: 'draft, posted, cancelled']
  created_at timestamp
  updated_at timestamp
}

Table stock_distribution_items {
  id bigint [pk, increment]
  distribution_id bigint [not null, ref: > stock_distributions.id]
  batch_id bigint [not null, ref: > medicine_batches.id]
  medicine_id bigint [not null, ref: > medicines.id]
  quantity int [not null]
  notes varchar(255)
  created_at timestamp
  updated_at timestamp
}

Table stock_adjustments {
  id bigint [pk, increment]
  adjustment_number varchar(50) [not null, unique]
  adjustment_date date [not null]
  adjustment_type varchar(20) [not null, note: 'opname, koreksi, expired, rusak']
  created_by bigint [not null, ref: > users.id]
  notes text
  created_at timestamp
  updated_at timestamp
}

Table stock_adjustment_items {
  id bigint [pk, increment]
  adjustment_id bigint [not null, ref: > stock_adjustments.id]
  batch_id bigint [not null, ref: > medicine_batches.id]
  medicine_id bigint [not null, ref: > medicines.id]
  system_qty int [not null]
  actual_qty int [not null]
  difference_qty int [not null]
  reason varchar(255)
  created_at timestamp
  updated_at timestamp
}

Table activity_logs {
  id bigint [pk, increment]
  user_id bigint [ref: > users.id]
  module varchar(100) [not null]
  action varchar(50) [not null]
  description text
  ip_address varchar(45)
  created_at timestamp
}
```

## Relasi Inti

- `roles` ke `users`: satu role dipakai banyak user.
- `medicine_categories` ke `medicines`: satu kategori punya banyak obat.
- `units` ke `medicines`: satu satuan dipakai banyak obat.
- `stock_sources` ke `stock_receipts`: satu sumber dapat memiliki banyak transaksi stok masuk.
- `stock_receipts` ke `stock_receipt_items`: satu transaksi stok masuk punya banyak item.
- `stock_receipt_items` ke `medicine_batches`: setiap item penerimaan membentuk batch stok.
- `distribution_destinations` ke `stock_distributions`: satu tujuan distribusi punya banyak transaksi stok keluar.
- `stock_distributions` ke `stock_distribution_items`: satu transaksi stok keluar punya banyak item.
- `medicine_batches` ke `stock_distribution_items`: stok keluar sebaiknya mengambil batch tertentu agar FIFO/FEFO bisa diterapkan.
- `stock_adjustments` ke `stock_adjustment_items`: satu transaksi penyesuaian punya banyak item koreksi.

## Halaman yang Dibuat

### 1. Halaman Autentikasi

- Login
- Lupa password
- Ubah password
- Profil pengguna

### 2. Dashboard

- Ringkasan total jenis obat
- Ringkasan total stok tersedia
- Ringkasan stok masuk bulan ini
- Ringkasan stok keluar bulan ini
- Notifikasi stok minimum
- Notifikasi obat mendekati kedaluwarsa
- Grafik pergerakan stok

### 3. Master Data

- Data kategori obat
- Data satuan obat
- Data obat kontrasepsi
- Data sumber/pemasok obat
- Data tujuan distribusi
- Data pengguna
- Data role / hak akses

### 4. Transaksi Stok Masuk

- Daftar stok masuk
- Tambah stok masuk
- Detail stok masuk
- Cetak bukti penerimaan

Field penting pada form:

- Nomor transaksi
- Tanggal penerimaan
- Sumber obat
- Daftar item obat
- Nomor batch
- Tanggal kedaluwarsa
- Jumlah
- Harga satuan jika diperlukan
- Catatan

### 5. Transaksi Stok Keluar

- Daftar stok keluar
- Tambah stok keluar
- Detail stok keluar
- Cetak bukti distribusi

Field penting pada form:

- Nomor transaksi
- Tanggal distribusi
- Tujuan distribusi
- Daftar item obat
- Batch yang dipilih
- Jumlah keluar
- Catatan

### 6. Penyesuaian Stok

- Daftar penyesuaian stok
- Tambah penyesuaian
- Detail penyesuaian

Kegunaan halaman ini:

- Koreksi selisih stok opname
- Mencatat obat rusak
- Mencatat obat kedaluwarsa

### 7. Monitoring Stok

- Stok terkini semua obat
- Stok per batch
- Stok minimum
- Stok hampir habis
- Stok mendekati kedaluwarsa
- Riwayat kartu stok per obat

### 8. Laporan

- Laporan stok obat
- Laporan stok masuk per periode
- Laporan stok keluar per periode
- Laporan distribusi per fasilitas
- Laporan obat kedaluwarsa
- Laporan penyesuaian stok
- Export PDF / Excel

### 9. Audit dan Aktivitas

- Log aktivitas pengguna
- Riwayat perubahan data penting

## Saran Struktur Menu Sidebar

```text
Dashboard
Master Data
  - Kategori Obat
  - Satuan
  - Obat
  - Sumber Obat
  - Tujuan Distribusi
Transaksi
  - Stok Masuk
  - Stok Keluar
  - Penyesuaian Stok
Monitoring
  - Stok Terkini
  - Batch & Kedaluwarsa
  - Kartu Stok
Laporan
Pengguna & Hak Akses
Log Aktivitas
Profil
```

## Saran Modul Laravel

- `Auth`: login, logout, ubah password, profil
- `UserManagement`: user dan role
- `MasterData`: obat, kategori, satuan, sumber, tujuan distribusi
- `InventoryIn`: stok masuk dan batch
- `InventoryOut`: stok keluar
- `InventoryAdjustment`: stok opname dan koreksi
- `Monitoring`: dashboard, stok minimum, kedaluwarsa, kartu stok
- `Reporting`: PDF, Excel, filter periode
- `AuditTrail`: log aktivitas pengguna

## Catatan Implementasi

- Jika ingin versi awal yang lebih sederhana, modul `stock_adjustments` dan `activity_logs` bisa dibuat setelah fitur utama selesai.
- Untuk pengeluaran stok, lebih aman memakai metode **FEFO** (`first expired first out`) agar batch dengan tanggal kedaluwarsa terdekat diprioritaskan.
- Laravel yang disarankan: migration per tabel, Eloquent relationship lengkap, dan authorization memakai middleware atau policy.
