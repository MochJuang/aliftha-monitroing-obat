# Planning Refactor Aplikasi Monitoring Obat Kontrasepsi

Dokumen ini berisi rencana refactor aplikasi agar struktur menu, istilah bisnis, dan modul sistem lebih sesuai dengan kebutuhan operasional instansi, khususnya dengan susunan menu berikut:

1. `Faskes`
2. `Master Obat`
3. `Rencana Kebutuhan Obat (RKO)`
4. `Realisasi Pengadaan`
5. `Monitoring`
6. `Laporan`

Dokumen ini difokuskan pada perencanaan perubahan, bukan implementasi langsung. Tujuannya agar proses refactor dapat dilakukan bertahap, aman, dan tetap menjaga fungsi yang sudah berjalan.

Status implementasi saat ini:

- `Tahap 1` sudah diimplementasikan pada level UI, istilah bisnis dan grouping menu utama sudah disesuaikan.
- `Tahap 2` sudah diimplementasikan pada level route alias, redirect controller, dan action utama pada halaman CRUD/laporan.
- route teknis lama masih dipertahankan sementara sebagai fallback agar transisi tetap aman.

Catatan penting:

- meskipun menu utama hanya terdiri dari 6 kelompok, proses `stok keluar` tetap harus dipertahankan karena secara bisnis obat tetap didistribusikan ke fasilitas kesehatan,
- dalam rencana ini, modul `stok keluar` akan diposisikan ulang sebagai bagian dari konteks `Faskes`, dengan nama bisnis `Distribusi Obat`.

## 0. Prinsip Istilah

Agar refactor tidak terasa redundant, istilah dalam sistem dibagi menjadi dua lapisan:

### 0.1 Istilah untuk UI

Istilah yang tampil ke pengguna harus menggunakan istilah bisnis yang sederhana dan konsisten, yaitu:

- `Faskes`
- `Master Obat`
- `RKO`
- `Realisasi Pengadaan`
- `Distribusi Obat`
- `Monitoring`
- `Laporan`
- `Penyesuaian Stok`

### 0.2 Istilah Teknis Internal

Istilah teknis internal digunakan hanya pada:

- nama tabel database,
- nama model atau service lama,
- route internal sementara,
- dokumentasi teknis untuk developer.

Contohnya:

- `distribution_destinations`
- `stock_receipts`
- `stock_distributions`
- `stock_adjustments`

Dengan pendekatan ini:

- user hanya melihat istilah bisnis,
- developer tetap bisa menjaga kompatibilitas kode lama,
- proses refactor menjadi lebih aman karena tidak semua nama teknis harus diubah sekaligus.

## 1. Tujuan Refactor

Tujuan utama refactor ini adalah:

- menyederhanakan navigasi aplikasi agar lebih mudah dipahami oleh pengguna non-teknis,
- menyesuaikan istilah sistem dengan istilah bisnis di instansi,
- menambahkan modul `RKO` sebagai bagian dari alur perencanaan kebutuhan obat,
- mengubah istilah `stok masuk` menjadi `realisasi pengadaan` agar lebih sesuai dengan proses pengadaan atau penerimaan resmi,
- tetap mempertahankan logika inti yang sudah ada, seperti pengelolaan batch, FEFO, monitoring, laporan, dan audit aktivitas.

## 2. Kondisi Saat Ini

Saat ini aplikasi memiliki kelompok menu dan modul sebagai berikut:

- `Master Data`
  - kategori obat
  - satuan
  - obat
  - sumber obat
  - tujuan distribusi
- `Transaksi`
  - stok masuk
  - stok keluar
  - penyesuaian stok
- `Monitoring`
  - stok terkini
  - batch dan kedaluwarsa
  - kartu stok
- `Laporan`
- `Pengguna`
- `Log Aktivitas`

Secara teknis, struktur tabel saat ini juga telah terbentuk dengan pola berikut:

- master obat: `medicine_categories`, `units`, `medicines`
- faskes / tujuan distribusi: `distribution_destinations`
- stok masuk: `stock_receipts`, `stock_receipt_items`, `medicine_batches`
- stok keluar: `stock_distributions`, `stock_distribution_items`
- penyesuaian stok: `stock_adjustments`, `stock_adjustment_items`
- audit: `activity_logs`

## 3. Struktur Menu Target

Struktur menu target yang diinginkan adalah:

### 3.1 Faskes

Menu ini digunakan untuk mengelola data fasilitas kesehatan atau tujuan distribusi obat.

Submenu yang disarankan:

- data faskes
- distribusi obat
- jenis faskes jika nanti diperlukan

Catatan:

- modul ini secara logika dapat memanfaatkan tabel `distribution_destinations`
- istilah `Tujuan Distribusi` diganti menjadi `Faskes`
- modul `distribusi obat` memanfaatkan proses `stock_distributions` yang sudah ada
- pendekatan ini dipilih agar jumlah menu utama tetap 6, tetapi transaksi stok keluar tetap terlihat jelas oleh pengguna

### 3.2 Master Obat

Menu ini digunakan untuk mengelola data obat kontrasepsi.

Submenu yang disarankan:

- kategori obat
- data obat

Catatan:

- `units` dapat tetap dipertahankan sebagai master pendukung
- satuan bisa disembunyikan dari menu utama dan dikelola dari form admin atau submenu pendukung bila diperlukan

### 3.3 Rencana Kebutuhan Obat (RKO)

Menu ini digunakan untuk pengelolaan kebutuhan obat sebelum realisasi pengadaan dilakukan.

Submenu yang disarankan:

- RKO Header
- RKO Detail

Fungsi bisnis:

- mencatat periode atau tahun perencanaan
- menyimpan daftar kebutuhan obat per periode
- menyimpan kuantitas usulan, kuantitas disetujui, dan catatan bila dibutuhkan
- menjadi dasar pembandingan antara rencana dan realisasi

### 3.4 Realisasi Pengadaan

Menu ini digunakan untuk mencatat pengadaan atau penerimaan obat yang benar-benar terjadi.

Submenu yang disarankan:

- daftar realisasi pengadaan
- tambah realisasi pengadaan
- detail realisasi pengadaan

Catatan:

- modul ini secara teknis dapat memanfaatkan modul `stock_receipts`
- istilah `stok masuk` diganti menjadi `realisasi pengadaan`
- logika pembentukan batch tetap dipertahankan

### 3.5 Monitoring

Menu ini digunakan untuk memantau kondisi stok dan riwayat pergerakannya.

Submenu yang disarankan:

- stok terkini
- batch dan kedaluwarsa
- kartu stok
- penyesuaian stok

Catatan:

- `penyesuaian stok` bisa tetap dianggap transaksi, tetapi dari sudut pandang user bisnis lebih mudah dipahami bila diletakkan dekat monitoring
- keputusan final bisa disesuaikan, tetapi secara teknis modul adjustment tetap diperlukan

### 3.6 Laporan

Menu ini digunakan untuk menampilkan laporan berbasis filter dan periode.

Submenu yang disarankan:

- laporan stok
- laporan RKO
- laporan realisasi pengadaan
- laporan distribusi
- laporan penyesuaian stok

## 4. Struktur Menu Final yang Disarankan

Supaya tidak redundant, struktur menu final yang disarankan untuk pengguna adalah sebagai berikut:

### 4.1 Opsi Final 6 Menu

1. `Faskes`
   - Data Faskes
   - Distribusi Obat
2. `Master Obat`
   - Kategori Obat
   - Data Obat
3. `RKO`
   - RKO Header
   - RKO Detail
4. `Realisasi Pengadaan`
5. `Monitoring`
   - Stok Terkini
   - Batch dan Kedaluwarsa
   - Kartu Stok
   - Penyesuaian Stok
6. `Laporan`

### 4.2 Opsi Final 7 Menu

Jika ingin pemisahan transaksi lebih tegas, maka struktur berikut juga baik:

1. `Faskes`
2. `Master Obat`
3. `RKO`
4. `Realisasi Pengadaan`
5. `Distribusi Obat`
6. `Monitoring`
7. `Laporan`

Rekomendasi:

- jika user lebih nyaman dengan jumlah menu yang ringkas, gunakan opsi 6 menu,
- jika transaksi masuk dan keluar ingin terlihat sama pentingnya, gunakan opsi 7 menu.

## 4. Mapping Modul Lama ke Modul Baru

Berikut pemetaan modul saat ini ke struktur refactor:

| Modul Lama | Modul Baru | Catatan |
| --- | --- | --- |
| `distribution-destinations` | `Faskes` | Ubah istilah, tetap bisa pakai tabel lama |
| `medicine-categories` | `Master Obat > Kategori Obat` | Tetap |
| `medicines` | `Master Obat > Data Obat` | Tetap |
| `units` | `Master Obat > Pendukung` | Bisa disembunyikan dari menu utama |
| `stock-receipts` | `Realisasi Pengadaan` | Rename istilah, logika tetap |
| `stock-distributions` | `Faskes > Distribusi Obat` | Tetap sebagai transaksi, tetapi ditampilkan dalam konteks distribusi ke faskes |
| `stock-adjustments` | `Monitoring > Penyesuaian Stok` | Bisa dipindah dari grup transaksi |
| `stock-monitoring.*` | `Monitoring` | Tetap |
| `reports.*` | `Laporan` | Ditambah laporan RKO |

## 5. Mapping Istilah Bisnis dan Istilah Teknis

Tabel berikut hanya digunakan sebagai acuan developer, bukan untuk ditampilkan ke pengguna akhir:

| Istilah UI / Bisnis | Istilah Teknis Internal |
| --- | --- |
| Faskes | `distribution_destinations` |
| Realisasi Pengadaan | `stock_receipts` |
| Distribusi Obat | `stock_distributions` |
| Penyesuaian Stok | `stock_adjustments` |
| Sumber Pengadaan | `stock_sources` |

Catatan:

- daftar ini berfungsi sebagai peta transisi refactor,
- istilah teknis tidak perlu dimunculkan di sidebar, judul halaman, atau label tombol,
- bila nanti route dan controller ikut dirapikan, perubahan dapat dilakukan bertahap berdasarkan mapping ini.

## 6. Perubahan Data Model yang Diperlukan

### 5.1 Tabel yang Bisa Dipertahankan

Tabel berikut tidak perlu diubah secara besar:

- `medicine_categories`
- `units`
- `medicines`
- `distribution_destinations`
- `stock_receipts`
- `stock_receipt_items`
- `medicine_batches`
- `stock_distributions`
- `stock_distribution_items`
- `stock_adjustments`
- `stock_adjustment_items`
- `activity_logs`

### 5.2 Tabel yang Perlu Ditambah

Untuk mendukung `RKO`, disarankan menambah dua tabel baru:

#### `rko_headers`

Fungsi:

- menyimpan data perencanaan utama per periode

Field yang disarankan:

- `id`
- `rko_number`
- `period_month` atau `period_start`
- `period_year`
- `status`
- `submitted_by`
- `approved_by` bila diperlukan
- `notes`
- `created_at`
- `updated_at`

#### `rko_details`

Fungsi:

- menyimpan item kebutuhan obat per RKO

Field yang disarankan:

- `id`
- `rko_header_id`
- `medicine_id`
- `planned_quantity`
- `approved_quantity` nullable
- `notes`
- `created_at`
- `updated_at`

### 5.3 Perubahan Opsional pada `stock_receipts`

Untuk menghubungkan realisasi pengadaan dengan RKO, dapat ditambahkan field:

- `rko_header_id` nullable pada `stock_receipts`

Manfaat:

- realisasi pengadaan dapat ditelusuri berasal dari RKO periode mana

## 7. Strategi Refactor

Refactor disarankan dilakukan secara bertahap agar risiko kerusakan lebih kecil.

### Tahap 1: Refactor UI dan Informasi Menu

Fokus:

- ubah struktur sidebar
- ubah label halaman
- ubah breadcrumb, judul, dan tombol
- pertahankan route dan controller lama terlebih dahulu

Target hasil:

- user sudah melihat istilah baru tanpa perubahan besar pada backend

Contoh:

- route `stock-receipts.index` tetap dipakai, tetapi judul halaman menjadi `Realisasi Pengadaan`
- route `distribution-destinations.index` tetap dipakai, tetapi judul halaman menjadi `Faskes`
- route `stock-distributions.index` tetap dipakai, tetapi judul halaman menjadi `Distribusi Obat`
- menu `Distribusi Obat` muncul sebagai submenu pada kelompok `Faskes`

### Tahap 2: Refactor Route Naming

Fokus:

- buat alias route baru yang lebih sesuai bisnis
- arahkan route lama ke controller yang sama

Target hasil:

- kode frontend dan navigasi makin konsisten

Contoh route target:

- `faskes.index`
- `faskes.distribusi.index`
- `master-obat.kategori.index`
- `master-obat.obat.index`
- `rko.index`
- `pengadaan.index`

### Tahap 3: Tambah Modul RKO

Fokus:

- migration tabel `rko_headers` dan `rko_details`
- model Eloquent
- CRUD dasar
- relasi ke `medicines` dan `users`

Target hasil:

- sistem sudah punya tahap perencanaan kebutuhan obat

### Tahap 4: Integrasi RKO dengan Realisasi Pengadaan

Fokus:

- tambahkan hubungan `rko_header_id` pada realisasi pengadaan bila diperlukan
- tampilkan perbandingan antara rencana dan realisasi

Target hasil:

- user bisa mengetahui apakah realisasi sudah sesuai RKO

### Tahap 5: Rapikan Laporan dan Dashboard

Fokus:

- laporan berbasis struktur baru
- dashboard menampilkan ringkasan RKO, realisasi, stok, dan kedaluwarsa

Target hasil:

- informasi manajerial lebih lengkap dan sesuai kebutuhan instansi

## 8. Perubahan File yang Diperkirakan Terdampak

### 8.1 Sidebar dan Layout

- `resources/views/layouts/sidebar.blade.php`
- kemungkinan `resources/views/layouts/topbar.blade.php`

### 8.2 Routes

- `routes/web.php`

### 8.3 Controller yang terdampak rename label

- `DistributionDestinationController`
- `StockReceiptController`
- `StockDistributionController`
- `StockAdjustmentController`
- `ReportController`
- `DashboardController`

### 8.4 View yang terdampak rename istilah

- `resources/views/distribution-destinations/*`
- `resources/views/stock-receipts/*`
- `resources/views/stock-distributions/*`
- `resources/views/stock-adjustments/*`
- `resources/views/reports/*`

### 8.5 Model baru yang perlu ditambahkan

- `RkoHeader`
- `RkoDetail`

### 8.6 Migration baru

- create `rko_headers`
- create `rko_details`
- optional alter `stock_receipts`

## 9. Risiko dan Hal yang Perlu Dijaga

Beberapa risiko saat refactor:

- istilah di menu berubah tetapi route lama masih dipakai, sehingga user dan developer bisa bingung jika dokumentasi tidak diperbarui,
- perubahan nama route dapat memengaruhi sidebar, redirect, dan form action,
- penambahan modul RKO dapat menambah kompleksitas relasi bila integrasi ke pengadaan tidak dirancang dengan jelas,
- penempatan `stok keluar` di bawah konteks `Faskes` harus dirancang dengan jelas agar user tidak mengira menu tersebut hanya berisi data master faskes,
- perubahan tabel secara langsung berisiko merusak seed, laporan, dan fitur monitoring yang sudah ada.

Untuk mengurangi risiko tersebut, prinsip refactor yang disarankan:

- ubah istilah di UI terlebih dahulu,
- pertahankan kompatibilitas route lama untuk sementara,
- tambahkan modul baru sebelum menghapus struktur lama,
- uji ulang dashboard, monitoring, laporan, dan transaksi setelah setiap tahap selesai.

## 10. Checklist Eksekusi Refactor

Berikut checklist kerja yang bisa dipakai saat implementasi:

### Fase A: UI Refactor

- [x] ubah menu sidebar ke struktur baru
- [x] ubah judul halaman `Tujuan Distribusi` menjadi `Faskes`
- [x] ubah judul halaman `Stok Masuk` menjadi `Realisasi Pengadaan`
- [x] ubah judul halaman `Stok Keluar` menjadi `Distribusi Obat`
- [x] letakkan `Distribusi Obat` di bawah grup `Faskes`
- [x] rapikan label laporan
- [x] pastikan active state menu tetap benar

### Fase A.1: Route Alias Bisnis

- [x] tambah alias route `faskes.*`
- [x] tambah alias route `master-obat.*`
- [x] tambah alias route `pengadaan.*`
- [x] tambah alias route `monitoring.*`
- [x] tambah alias route `laporan.*`
- [x] arahkan redirect controller ke alias route baru
- [x] arahkan form action dan tombol navigasi utama ke alias route baru
- [x] pertahankan route lama sebagai fallback sementara

### Fase B: RKO

- [x] buat migration `rko_headers`
- [x] buat migration `rko_details`
- [x] buat model dan relasi
- [x] buat controller dan request validation
- [x] buat halaman index, create, edit, show
- [x] tambahkan menu RKO

### Fase C: Integrasi

- [x] evaluasi apakah `stock_receipts` perlu `rko_header_id`
- [x] jika ya, tambahkan migration alter table
- [x] tampilkan referensi RKO di halaman realisasi pengadaan
- [x] tampilkan relasi realisasi pengadaan pada halaman detail RKO
- [ ] buat laporan perbandingan RKO vs realisasi

### Fase D: Monitoring dan Laporan

- tambahkan laporan RKO
- tambahkan summary realisasi pengadaan pada dashboard
- pastikan monitoring stok tetap akurat setelah integrasi

## 11. Rekomendasi Implementasi

Rekomendasi implementasi paling aman adalah:

1. mulai dari refactor menu dan istilah di UI tanpa menyentuh database lama,
2. setelah UI stabil, tambahkan modul RKO sebagai modul baru,
3. lakukan integrasi RKO ke realisasi pengadaan setelah kebutuhan bisnis final,
4. baru setelah semua stabil, pertimbangkan rename route atau refactor naming internal secara lebih besar.

Dengan pendekatan tersebut, aplikasi bisa terus dipakai sambil direstrukturisasi secara bertahap, tanpa perlu melakukan perubahan besar sekaligus.
