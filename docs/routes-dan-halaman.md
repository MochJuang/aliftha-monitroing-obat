# Blueprint Route, Controller, dan Halaman

Dokumen ini memetakan halaman aplikasi ke route Laravel dan controller yang disarankan.

## Struktur Route Utama

Saran pembagian route:

- route publik: login
- route auth: seluruh halaman internal
- route admin: user, role, dan pengaturan akses

## Contoh Route `web.php`

```php
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DistributionDestinationController;
use App\Http\Controllers\MedicineCategoryController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockDistributionController;
use App\Http\Controllers\StockMonitoringController;
use App\Http\Controllers\StockReceiptController;
use App\Http\Controllers\StockSourceController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::resource('medicine-categories', MedicineCategoryController::class);
    Route::resource('units', UnitController::class);
    Route::resource('medicines', MedicineController::class);
    Route::resource('stock-sources', StockSourceController::class);
    Route::resource('distribution-destinations', DistributionDestinationController::class);

    Route::resource('stock-receipts', StockReceiptController::class);
    Route::get('stock-receipts/{stock_receipt}/print', [StockReceiptController::class, 'print'])->name('stock-receipts.print');

    Route::resource('stock-distributions', StockDistributionController::class);
    Route::get('stock-distributions/{stock_distribution}/print', [StockDistributionController::class, 'print'])->name('stock-distributions.print');

    Route::resource('stock-adjustments', StockAdjustmentController::class);

    Route::get('/monitoring/stocks', [StockMonitoringController::class, 'index'])->name('monitoring.stocks.index');
    Route::get('/monitoring/batches', [StockMonitoringController::class, 'batches'])->name('monitoring.batches.index');
    Route::get('/monitoring/expired', [StockMonitoringController::class, 'expired'])->name('monitoring.expired.index');
    Route::get('/monitoring/stock-card', [StockMonitoringController::class, 'stockCard'])->name('monitoring.stock-card.index');

    Route::get('/reports/stocks', [ReportController::class, 'stocks'])->name('reports.stocks');
    Route::get('/reports/stock-receipts', [ReportController::class, 'stockReceipts'])->name('reports.stock-receipts');
    Route::get('/reports/stock-distributions', [ReportController::class, 'stockDistributions'])->name('reports.stock-distributions');
    Route::get('/reports/expired-medicines', [ReportController::class, 'expiredMedicines'])->name('reports.expired-medicines');
    Route::get('/reports/stock-adjustments', [ReportController::class, 'stockAdjustments'])->name('reports.stock-adjustments');

    Route::middleware('role:admin')->group(function () {
        Route::resource('roles', RoleController::class);
        Route::resource('users', UserController::class);
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });
});
```

## Daftar Controller yang Dibutuhkan

### Controller inti

- `DashboardController`
- `ProfileController`

### Controller master data

- `MedicineCategoryController`
- `UnitController`
- `MedicineController`
- `StockSourceController`
- `DistributionDestinationController`
- `RoleController`
- `UserController`

### Controller transaksi

- `StockReceiptController`
- `StockDistributionController`
- `StockAdjustmentController`

### Controller monitoring

- `StockMonitoringController`

### Controller laporan

- `ReportController`

### Controller audit

- `ActivityLogController`

## Daftar Halaman Blade yang Disarankan

### 1. Layout dan komponen utama

- `resources/views/layouts/app.blade.php`
- `resources/views/components/sidebar.blade.php`
- `resources/views/components/navbar.blade.php`
- `resources/views/components/alert.blade.php`
- `resources/views/components/table-actions.blade.php`

### 2. Auth

- `resources/views/auth/login.blade.php`

### 3. Dashboard

- `resources/views/dashboard/index.blade.php`

Isi halaman dashboard:

- kartu ringkasan stok
- grafik transaksi bulanan
- daftar stok minimum
- daftar batch mendekati kedaluwarsa

### 4. Profile

- `resources/views/profile/edit.blade.php`

### 5. Master data

Untuk setiap modul resource, minimal buat:

- `index.blade.php`
- `create.blade.php`
- `edit.blade.php`
- `show.blade.php`
- `_form.blade.php`

Modul:

- `resources/views/medicine-categories/`
- `resources/views/units/`
- `resources/views/medicines/`
- `resources/views/stock-sources/`
- `resources/views/distribution-destinations/`
- `resources/views/roles/`
- `resources/views/users/`

### 6. Stok masuk

Folder:

- `resources/views/stock-receipts/`

Halaman:

- `index.blade.php`
- `create.blade.php`
- `show.blade.php`
- `print.blade.php`
- `_form.blade.php`
- `_item-row.blade.php`

Komponen form penting:

- data header transaksi
- repeater item obat
- pilihan obat
- batch number
- tanggal expired
- jumlah
- harga satuan

### 7. Stok keluar

Folder:

- `resources/views/stock-distributions/`

Halaman:

- `index.blade.php`
- `create.blade.php`
- `show.blade.php`
- `print.blade.php`
- `_form.blade.php`
- `_item-row.blade.php`

Komponen form penting:

- tujuan distribusi
- pilihan obat
- batch tersedia
- stok tersedia per batch
- jumlah keluar

### 8. Penyesuaian stok

Folder:

- `resources/views/stock-adjustments/`

Halaman:

- `index.blade.php`
- `create.blade.php`
- `show.blade.php`
- `_form.blade.php`

Komponen form penting:

- tipe penyesuaian
- batch
- stok sistem
- stok aktual
- alasan koreksi

### 9. Monitoring

Folder:

- `resources/views/monitoring/`

Halaman:

- `stocks.blade.php`
- `batches.blade.php`
- `expired.blade.php`
- `stock-card.blade.php`

Isi utama:

- filter tanggal
- filter obat
- filter fasilitas
- tabel stok
- status minimum
- status expired

### 10. Laporan

Folder:

- `resources/views/reports/`

Halaman:

- `stocks.blade.php`
- `stock-receipts.blade.php`
- `stock-distributions.blade.php`
- `expired-medicines.blade.php`
- `stock-adjustments.blade.php`

Fitur yang disarankan:

- filter periode
- filter obat
- filter tujuan distribusi
- tombol export PDF
- tombol export Excel
- tombol print

### 11. Audit

Folder:

- `resources/views/activity-logs/`

Halaman:

- `index.blade.php`

## Aksi per Modul

Supaya implementasinya lebih jelas, berikut aksi minimal per controller.

### `MedicineController`

- `index`
- `create`
- `store`
- `show`
- `edit`
- `update`
- `destroy`

### `StockReceiptController`

- `index`
- `create`
- `store`
- `show`
- `edit` opsional jika status masih `draft`
- `update` opsional jika status masih `draft`
- `destroy` opsional jika status masih `draft`
- `print`

### `StockDistributionController`

- `index`
- `create`
- `store`
- `show`
- `edit` opsional jika status masih `draft`
- `update` opsional jika status masih `draft`
- `destroy` opsional jika status masih `draft`
- `print`

### `StockAdjustmentController`

- `index`
- `create`
- `store`
- `show`

### `StockMonitoringController`

- `index`
- `batches`
- `expired`
- `stockCard`

### `ReportController`

- `stocks`
- `stockReceipts`
- `stockDistributions`
- `expiredMedicines`
- `stockAdjustments`
- `exportPdf` opsional
- `exportExcel` opsional

## Middleware yang Disarankan

- `auth`
- `role:admin`
- `can:view-report`
- `can:manage-master-data`

## Saran Nama Menu

```text
Dashboard
Data Master
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
  - Batch Obat
  - Kedaluwarsa
  - Kartu Stok
Laporan
Pengguna
Log Aktivitas
Profil
```

## Prioritas Pembuatan Halaman

Jika ingin MVP lebih cepat, urutannya bisa:

1. Login
2. Dashboard
3. Master data obat
4. Master data sumber dan tujuan
5. Stok masuk
6. Monitoring stok
7. Stok keluar
8. Laporan
9. User management
10. Activity log

## Catatan UX

- Form transaksi stok masuk dan keluar sebaiknya mendukung tambah banyak item dalam satu halaman.
- Monitoring stok perlu warna status yang jelas untuk stok minimum dan batch hampir expired.
- Halaman laporan sebaiknya punya filter sederhana di atas tabel agar nyaman dipakai petugas.
