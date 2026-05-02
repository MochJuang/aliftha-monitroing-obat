# Flow Aksi Aplikasi

Dokumen ini menjelaskan alur setiap aksi utama dalam aplikasi, termasuk proses bisnis, validasi, dan tabel apa saja yang berubah.

## Prinsip Umum

- Semua aksi transaksi stok sebaiknya memakai `DB::transaction()`.
- Setiap perubahan transaksi penting sebaiknya dicatat ke `activity_logs`.
- Data master tidak langsung mengubah stok, tetapi mempengaruhi pilihan data saat transaksi.
- Stok aktif dibaca dari `medicine_batches.qty_remaining`.

## Ringkasan Tabel yang Sering Berubah

- `stock_receipts`
- `stock_receipt_items`
- `medicine_batches`
- `stock_distributions`
- `stock_distribution_items`
- `stock_adjustments`
- `stock_adjustment_items`
- `activity_logs`

## 1. Login

### Tujuan

Memastikan user yang masuk memiliki akun aktif dan role yang valid.

### Flow

1. User membuka halaman login.
2. User mengisi `username/email` dan `password`.
3. Sistem mencari user.
4. Sistem memverifikasi password.
5. Sistem memeriksa apakah `is_active = true`.
6. Jika valid, user diarahkan ke dashboard.

### Validasi

- Username atau email harus terdaftar.
- Password harus benar.
- User harus aktif.

### Tabel yang Berubah

#### `users`

- update `last_login_at`

#### `activity_logs`

- insert log login

## 2. Tambah Data Master Obat

### Tujuan

Menambahkan jenis obat baru agar dapat dipakai pada transaksi stok.

### Flow

1. Admin atau petugas membuka menu `Data Obat`.
2. User klik tambah data.
3. User mengisi kategori, satuan, kode, nama, dosis, dan stok minimum.
4. Sistem memvalidasi data.
5. Sistem menyimpan data obat.

### Validasi

- `code` harus unik.
- `category_id` harus ada di `medicine_categories`.
- `unit_id` harus ada di `units`.
- `minimum_stock` tidak boleh negatif.

### Tabel yang Berubah

#### `medicines`

- insert data obat baru

#### `activity_logs`

- insert log create medicine

## 3. Ubah Data Master Obat

### Tujuan

Memperbarui data referensi obat tanpa mengubah histori transaksi lama.

### Flow

1. User membuka detail atau edit obat.
2. User memperbarui informasi seperti nama, merek, dosis, atau stok minimum.
3. Sistem memvalidasi perubahan.
4. Sistem menyimpan update.

### Validasi

- `code` tetap unik.
- Tidak boleh menghapus relasi kategori atau satuan ke nilai yang tidak ada.

### Tabel yang Berubah

#### `medicines`

- update kolom yang diubah

#### `activity_logs`

- insert log update medicine

## 4. Nonaktifkan Data Obat

### Tujuan

Menghentikan pemakaian obat pada transaksi baru tanpa menghapus histori lama.

### Flow

1. User membuka halaman obat.
2. User klik nonaktifkan.
3. Sistem memastikan aksi ini aman.
4. Sistem mengubah status obat menjadi tidak aktif.

### Validasi

- Obat sebaiknya tidak dihapus fisik jika sudah pernah dipakai transaksi.
- Lebih aman memakai `is_active = false`.

### Tabel yang Berubah

#### `medicines`

- update `is_active` menjadi `false`

#### `activity_logs`

- insert log deactivate medicine

## 5. Tambah Stok Masuk

### Tujuan

Mencatat obat yang diterima dari BKKBN, Dinkes, atau supplier.

### Flow

1. Petugas membuka menu `Stok Masuk`.
2. Petugas klik tambah transaksi.
3. Petugas mengisi header transaksi:
   - nomor penerimaan
   - tanggal penerimaan
   - sumber obat
   - catatan
4. Petugas menambahkan item obat:
   - obat
   - nomor batch
   - tanggal kedaluwarsa
   - jumlah
   - harga satuan jika ada
5. Sistem memvalidasi semua item.
6. Sistem menyimpan header ke `stock_receipts`.
7. Sistem menyimpan detail ke `stock_receipt_items`.
8. Sistem membentuk batch di `medicine_batches`.
9. Sistem membuat activity log.

### Validasi

- `receipt_number` harus unik.
- `source_id` harus valid.
- `medicine_id` harus valid dan aktif.
- `expired_at` harus di masa depan atau minimal tidak lebih lama dari tanggal penerimaan.
- `quantity` harus lebih dari `0`.
- Satu item wajib punya `batch_number`.

### Tabel yang Berubah

#### `stock_receipts`

- insert header transaksi stok masuk

#### `stock_receipt_items`

- insert detail item stok masuk

#### `medicine_batches`

- insert batch baru
- `qty_received = quantity`
- `qty_remaining = quantity`

#### `activity_logs`

- insert log create stock receipt

### Contoh

Saat membuat `RCV-2026-0001`:

- insert ke `stock_receipts`
- insert 3 baris ke `stock_receipt_items`
- insert 3 baris batch awal ke `medicine_batches`

## 6. Edit Stok Masuk

### Tujuan

Mengoreksi transaksi stok masuk yang masih aman untuk diubah.

### Aturan Penting

- Edit hanya boleh dilakukan jika status transaksi masih `draft`.
- Jika status sudah `posted`, sebaiknya tidak diedit langsung.
- Jika transaksi sudah mempengaruhi batch aktif, perubahan harus lewat mekanisme pembatalan atau adjustment.

### Flow

1. User membuka transaksi draft.
2. User mengubah data header atau item.
3. Sistem memvalidasi ulang.
4. Sistem menyimpan perubahan.

### Tabel yang Berubah

Jika masih `draft` dan batch belum dibentuk:

#### `stock_receipts`

- update header

#### `stock_receipt_items`

- update, insert, atau delete item detail

#### `activity_logs`

- insert log update stock receipt

## 7. Hapus atau Batalkan Stok Masuk

### Tujuan

Membatalkan transaksi penerimaan yang salah input.

### Aturan Penting

- Jika transaksi masih `draft`, boleh dihapus.
- Jika sudah `posted`, lebih aman diubah menjadi `cancelled`.
- Pembatalan hanya boleh dilakukan jika batch dari transaksi itu belum dipakai stok keluar atau adjustment.

### Flow

1. User memilih transaksi stok masuk.
2. Sistem cek status transaksi.
3. Jika `draft`, data bisa dihapus.
4. Jika `posted`, sistem cek apakah batch sudah dipakai.
5. Jika belum dipakai, status diubah ke `cancelled` dan batch dinonaktifkan atau diset `qty_remaining = 0` sesuai aturan bisnis.
6. Sistem menyimpan log.

### Validasi

- Tidak boleh cancel jika batch sudah dipakai di `stock_distribution_items`.
- Tidak boleh cancel jika batch sudah dipakai di `stock_adjustment_items`.

### Tabel yang Berubah

#### Jika `draft`

- `stock_receipts`: delete
- `stock_receipt_items`: delete
- `activity_logs`: insert log delete

#### Jika `posted` dan aman dicancel

- `stock_receipts`: update `status = cancelled`
- `medicine_batches`: update sesuai kebijakan pembatalan
- `activity_logs`: insert log cancel

## 8. Tambah Stok Keluar

### Tujuan

Mencatat distribusi obat ke puskesmas, klinik, atau bidan.

### Flow

1. Petugas membuka menu `Stok Keluar`.
2. Petugas mengisi header:
   - nomor distribusi
   - tanggal distribusi
   - tujuan distribusi
   - catatan
3. Petugas memilih obat yang akan keluar.
4. Sistem menampilkan batch yang tersedia.
5. Batch dipilih berdasarkan FEFO, yaitu batch dengan expired terdekat diprioritaskan.
6. Petugas mengisi jumlah keluar per batch.
7. Sistem memvalidasi stok batch.
8. Sistem menyimpan header ke `stock_distributions`.
9. Sistem menyimpan detail ke `stock_distribution_items`.
10. Sistem mengurangi `medicine_batches.qty_remaining`.
11. Sistem membuat activity log.

### Validasi

- `distribution_number` harus unik.
- `destination_id` harus valid.
- `batch_id` harus valid.
- `quantity` harus lebih dari `0`.
- `quantity` tidak boleh melebihi `qty_remaining`.
- `medicine_id` pada item harus sesuai dengan `batch_id` yang dipilih.

### Tabel yang Berubah

#### `stock_distributions`

- insert header transaksi stok keluar

#### `stock_distribution_items`

- insert detail stok keluar

#### `medicine_batches`

- update `qty_remaining = qty_remaining - quantity`

#### `activity_logs`

- insert log create stock distribution

### Contoh

Saat membuat `DST-2026-0001`:

- insert ke `stock_distributions`
- insert 3 baris ke `stock_distribution_items`
- update batch:
  - batch `PIL240301` berkurang `60`
  - batch `SNT240215` berkurang `30`
  - batch `IUD260401` berkurang `10`

## 9. Edit Stok Keluar

### Tujuan

Mengoreksi distribusi yang masih draft.

### Aturan Penting

- Edit aman hanya saat status masih `draft`.
- Jika sudah `posted`, ubah langsung berisiko karena batch sudah berkurang.
- Untuk transaksi yang sudah `posted`, koreksi sebaiknya lewat pembatalan atau adjustment.

### Tabel yang Berubah

Jika masih `draft`:

- `stock_distributions`: update header
- `stock_distribution_items`: update detail
- `activity_logs`: insert log update

## 10. Hapus atau Batalkan Stok Keluar

### Tujuan

Mengembalikan stok yang terlanjur dicatat keluar secara salah.

### Flow

1. User memilih transaksi stok keluar.
2. Sistem cek status transaksi.
3. Jika `draft`, transaksi boleh dihapus.
4. Jika `posted`, transaksi lebih aman dibatalkan.
5. Sistem mengembalikan `qty_remaining` ke batch sesuai item distribusi.
6. Sistem mengubah status distribusi menjadi `cancelled`.
7. Sistem menyimpan log.

### Validasi

- Pastikan distribusi memang bisa dibatalkan sesuai kebijakan bisnis.
- Pastikan tidak ada proses lanjutan lain yang bergantung pada distribusi tersebut.

### Tabel yang Berubah

#### Jika `draft`

- `stock_distributions`: delete
- `stock_distribution_items`: delete
- `activity_logs`: insert log delete

#### Jika `posted`

- `stock_distributions`: update `status = cancelled`
- `medicine_batches`: update `qty_remaining = qty_remaining + quantity item`
- `activity_logs`: insert log cancel

## 11. Adjustment Stok

### Tujuan

Menyesuaikan stok sistem dengan kondisi fisik nyata.

### Kapan Dipakai

- hasil stok opname berbeda dengan sistem
- ada obat rusak
- ada obat expired
- ada kesalahan input lama yang perlu koreksi

### Flow

1. Petugas membuka menu `Penyesuaian Stok`.
2. Petugas membuat transaksi adjustment.
3. Petugas mengisi:
   - nomor adjustment
   - tanggal adjustment
   - tipe adjustment
   - catatan
4. Petugas memilih batch yang akan disesuaikan.
5. Sistem mengambil `system_qty` dari `medicine_batches.qty_remaining`.
6. Petugas mengisi `actual_qty`.
7. Sistem menghitung:
   - `difference_qty = actual_qty - system_qty`
8. Petugas mengisi alasan koreksi.
9. Sistem menyimpan header ke `stock_adjustments`.
10. Sistem menyimpan detail ke `stock_adjustment_items`.
11. Sistem mengubah `medicine_batches.qty_remaining` menjadi `actual_qty`.
12. Sistem membuat activity log.

### Validasi

- `adjustment_number` harus unik.
- `batch_id` harus valid.
- `actual_qty` tidak boleh negatif.
- Jika tipe adjustment `expired` atau `rusak`, biasanya `actual_qty <= system_qty`.
- `system_qty` harus diambil dari sistem, bukan input manual user.

### Tabel yang Berubah

#### `stock_adjustments`

- insert header transaksi adjustment

#### `stock_adjustment_items`

- insert detail adjustment

#### `medicine_batches`

- update `qty_remaining = actual_qty`

#### `activity_logs`

- insert log create stock adjustment

### Contoh Detail

Kasus pada sample data:

- batch `PIL240301`
- `system_qty = 400`
- stok fisik ditemukan `395`
- `difference_qty = 395 - 400 = -5`

Perubahan yang terjadi:

- insert ke `stock_adjustments` nomor `ADJ-2026-0001`
- insert ke `stock_adjustment_items`
- update `medicine_batches.qty_remaining` batch `PIL240301` dari `400` menjadi `395`
- insert log ke `activity_logs`

### Dampak Adjustment ke Data Lain

Adjustment tidak mengubah:

- `stock_receipts`
- `stock_receipt_items`
- `stock_distributions`
- `stock_distribution_items`

Adjustment hanya mengubah stok berjalan pada batch aktif dan membuat histori koreksi tersendiri.

## 12. Monitoring Stok Terkini

### Tujuan

Menampilkan stok real-time tanpa mengubah data.

### Flow

1. User membuka halaman monitoring stok.
2. Sistem mengambil data dari `medicines` dan `medicine_batches`.
3. Sistem menjumlahkan `qty_remaining` per obat.
4. Sistem membandingkan total stok dengan `minimum_stock`.
5. Sistem menampilkan status aman atau di bawah minimum.

### Tabel yang Dibaca

- `medicines`
- `medicine_batches`
- `units`
- `medicine_categories`

### Tabel yang Berubah

- tidak ada

## 13. Monitoring Batch dan Kedaluwarsa

### Tujuan

Mengetahui batch mana yang akan expired lebih dulu.

### Flow

1. User membuka halaman batch atau expired.
2. Sistem mengambil batch dengan `qty_remaining > 0`.
3. Sistem mengurutkan berdasarkan `expired_at`.
4. Sistem menandai batch yang mendekati expired.

### Tabel yang Dibaca

- `medicine_batches`
- `medicines`

### Tabel yang Berubah

- tidak ada

## 14. Laporan Stok Masuk

### Tujuan

Menampilkan rekap transaksi penerimaan dalam periode tertentu.

### Flow

1. User memilih periode.
2. Sistem mengambil data `stock_receipts` dan `stock_receipt_items`.
3. Sistem menampilkan tabel atau export PDF/Excel.

### Tabel yang Dibaca

- `stock_receipts`
- `stock_receipt_items`
- `stock_sources`
- `medicines`

### Tabel yang Berubah

- tidak ada

## 15. Laporan Stok Keluar

### Tujuan

Menampilkan rekap distribusi obat.

### Tabel yang Dibaca

- `stock_distributions`
- `stock_distribution_items`
- `distribution_destinations`
- `medicines`

### Tabel yang Berubah

- tidak ada

## 16. Laporan Adjustment

### Tujuan

Menampilkan histori koreksi stok.

### Tabel yang Dibaca

- `stock_adjustments`
- `stock_adjustment_items`
- `medicine_batches`
- `medicines`
- `users`

### Tabel yang Berubah

- tidak ada

## 17. Kelola User

### Tambah User

Tabel yang berubah:

- `users`
- `activity_logs`

### Ubah User

Tabel yang berubah:

- `users`
- `activity_logs`

### Nonaktifkan User

Tabel yang berubah:

- `users` update `is_active`
- `activity_logs`

## 18. Ringkasan Perubahan per Aksi

| Aksi | Insert | Update | Delete | Tabel utama yang berubah |
|---|---|---|---|---|
| Login | `activity_logs` | `users.last_login_at` | - | `users`, `activity_logs` |
| Tambah obat | `medicines`, `activity_logs` | - | - | `medicines` |
| Ubah obat | `activity_logs` | `medicines` | - | `medicines` |
| Nonaktifkan obat | `activity_logs` | `medicines.is_active` | - | `medicines` |
| Stok masuk | `stock_receipts`, `stock_receipt_items`, `medicine_batches`, `activity_logs` | - | - | transaksi masuk dan batch |
| Edit stok masuk draft | `activity_logs` | `stock_receipts`, `stock_receipt_items` | item opsional | transaksi masuk |
| Cancel stok masuk posted | `activity_logs` | `stock_receipts.status`, `medicine_batches` | - | transaksi masuk dan batch |
| Stok keluar | `stock_distributions`, `stock_distribution_items`, `activity_logs` | `medicine_batches.qty_remaining` | - | distribusi dan batch |
| Cancel stok keluar posted | `activity_logs` | `stock_distributions.status`, `medicine_batches.qty_remaining` | - | distribusi dan batch |
| Adjustment | `stock_adjustments`, `stock_adjustment_items`, `activity_logs` | `medicine_batches.qty_remaining` | - | adjustment dan batch |
| Monitoring | - | - | - | baca data saja |
| Laporan | - | - | - | baca data saja |

## Rekomendasi Implementasi Service

- `StockReceiptService::store()`
- `StockReceiptService::cancel()`
- `StockDistributionService::store()`
- `StockDistributionService::cancel()`
- `StockAdjustmentService::store()`
- `StockMonitoringService::getCurrentStock()`
- `ActivityLogService::log()`

## Catatan Penting

- Jangan pernah langsung menghitung stok hanya dari tabel transaksi di setiap halaman jika aplikasi mulai besar.
- Gunakan `medicine_batches.qty_remaining` sebagai stok berjalan.
- Histori tetap aman karena tabel transaksi dan adjustment disimpan terpisah.
- Untuk kebutuhan audit yang lebih kuat, nanti bisa ditambah tabel `stock_cards` jika ingin jejak mutasi pergerakan stok yang lebih detail.
