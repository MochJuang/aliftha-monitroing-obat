<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DistributionDestinationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MedicineCategoryController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RkoDetailController;
use App\Http\Controllers\RkoHeaderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockDistributionController;
use App\Http\Controllers\StockMonitoringController;
use App\Http\Controllers\StockReceiptController;
use App\Http\Controllers\StockSourceController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified', 'active'])
    ->name('dashboard');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserManagementController::class)->except(['destroy']);
        Route::patch('users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
    });

    Route::middleware('role:admin,petugas_gudang')->group(function () {
        Route::resource('medicine-categories', MedicineCategoryController::class);
        Route::resource('units', UnitController::class);
        Route::resource('medicines', MedicineController::class);
        Route::resource('rko-headers', RkoHeaderController::class);
        Route::resource('stock-sources', StockSourceController::class);
        Route::resource('distribution-destinations', DistributionDestinationController::class);
        Route::resource('stock-receipts', StockReceiptController::class);
        Route::resource('stock-distributions', StockDistributionController::class);
        Route::resource('stock-adjustments', StockAdjustmentController::class)->only(['index', 'create', 'store', 'show']);

        Route::prefix('master-obat')->name('master-obat.')->group(function () {
            Route::get('kategori-obat', [MedicineCategoryController::class, 'index'])->name('kategori.index');
            Route::get('kategori-obat/create', [MedicineCategoryController::class, 'create'])->name('kategori.create');
            Route::post('kategori-obat', [MedicineCategoryController::class, 'store'])->name('kategori.store');
            Route::get('kategori-obat/{medicineCategory}', [MedicineCategoryController::class, 'show'])->name('kategori.show');
            Route::get('kategori-obat/{medicineCategory}/edit', [MedicineCategoryController::class, 'edit'])->name('kategori.edit');
            Route::match(['put', 'patch'], 'kategori-obat/{medicineCategory}', [MedicineCategoryController::class, 'update'])->name('kategori.update');
            Route::delete('kategori-obat/{medicineCategory}', [MedicineCategoryController::class, 'destroy'])->name('kategori.destroy');

            Route::get('satuan', [UnitController::class, 'index'])->name('satuan.index');
            Route::get('satuan/create', [UnitController::class, 'create'])->name('satuan.create');
            Route::post('satuan', [UnitController::class, 'store'])->name('satuan.store');
            Route::get('satuan/{unit}', [UnitController::class, 'show'])->name('satuan.show');
            Route::get('satuan/{unit}/edit', [UnitController::class, 'edit'])->name('satuan.edit');
            Route::match(['put', 'patch'], 'satuan/{unit}', [UnitController::class, 'update'])->name('satuan.update');
            Route::delete('satuan/{unit}', [UnitController::class, 'destroy'])->name('satuan.destroy');

            Route::get('data-obat', [MedicineController::class, 'index'])->name('obat.index');
            Route::get('data-obat/create', [MedicineController::class, 'create'])->name('obat.create');
            Route::post('data-obat', [MedicineController::class, 'store'])->name('obat.store');
            Route::get('data-obat/{medicine}', [MedicineController::class, 'show'])->name('obat.show');
            Route::get('data-obat/{medicine}/edit', [MedicineController::class, 'edit'])->name('obat.edit');
            Route::match(['put', 'patch'], 'data-obat/{medicine}', [MedicineController::class, 'update'])->name('obat.update');
            Route::delete('data-obat/{medicine}', [MedicineController::class, 'destroy'])->name('obat.destroy');
        });

        Route::prefix('faskes')->name('faskes.')->group(function () {
            Route::get('/', [DistributionDestinationController::class, 'index'])->name('index');
            Route::get('create', [DistributionDestinationController::class, 'create'])->name('create');
            Route::post('/', [DistributionDestinationController::class, 'store'])->name('store');
            Route::get('{distributionDestination}', [DistributionDestinationController::class, 'show'])->name('show');
            Route::get('{distributionDestination}/edit', [DistributionDestinationController::class, 'edit'])->name('edit');
            Route::match(['put', 'patch'], '{distributionDestination}', [DistributionDestinationController::class, 'update'])->name('update');
            Route::delete('{distributionDestination}', [DistributionDestinationController::class, 'destroy'])->name('destroy');

            Route::prefix('distribusi-obat')->name('distribusi.')->group(function () {
                Route::get('/', [StockDistributionController::class, 'index'])->name('index');
                Route::get('create', [StockDistributionController::class, 'create'])->name('create');
                Route::post('/', [StockDistributionController::class, 'store'])->name('store');
                Route::get('{stockDistribution}', [StockDistributionController::class, 'show'])->name('show');
                Route::get('{stockDistribution}/edit', [StockDistributionController::class, 'edit'])->name('edit');
                Route::match(['put', 'patch'], '{stockDistribution}', [StockDistributionController::class, 'update'])->name('update');
                Route::delete('{stockDistribution}', [StockDistributionController::class, 'destroy'])->name('destroy');
            });
        });

        Route::prefix('pengadaan')->name('pengadaan.')->group(function () {
            Route::prefix('sumber')->name('sumber.')->group(function () {
                Route::get('/', [StockSourceController::class, 'index'])->name('index');
                Route::get('create', [StockSourceController::class, 'create'])->name('create');
                Route::post('/', [StockSourceController::class, 'store'])->name('store');
                Route::get('{stockSource}', [StockSourceController::class, 'show'])->name('show');
                Route::get('{stockSource}/edit', [StockSourceController::class, 'edit'])->name('edit');
                Route::match(['put', 'patch'], '{stockSource}', [StockSourceController::class, 'update'])->name('update');
                Route::delete('{stockSource}', [StockSourceController::class, 'destroy'])->name('destroy');
            });

            Route::get('realisasi', [StockReceiptController::class, 'index'])->name('index');
            Route::get('realisasi/create', [StockReceiptController::class, 'create'])->name('create');
            Route::post('realisasi', [StockReceiptController::class, 'store'])->name('store');
            Route::get('realisasi/{stockReceipt}', [StockReceiptController::class, 'show'])->name('show');
            Route::get('realisasi/{stockReceipt}/edit', [StockReceiptController::class, 'edit'])->name('edit');
            Route::match(['put', 'patch'], 'realisasi/{stockReceipt}', [StockReceiptController::class, 'update'])->name('update');
            Route::delete('realisasi/{stockReceipt}', [StockReceiptController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('monitoring')->name('monitoring.')->group(function () {
            Route::get('penyesuaian-stok', [StockAdjustmentController::class, 'index'])->name('penyesuaian.index');
            Route::get('penyesuaian-stok/create', [StockAdjustmentController::class, 'create'])->name('penyesuaian.create');
            Route::post('penyesuaian-stok', [StockAdjustmentController::class, 'store'])->name('penyesuaian.store');
            Route::get('penyesuaian-stok/{stockAdjustment}', [StockAdjustmentController::class, 'show'])->name('penyesuaian.show');
        });

        Route::prefix('rko')->name('rko.')->group(function () {
            Route::get('header', [RkoHeaderController::class, 'index'])->name('header.index');
            Route::get('header/create', [RkoHeaderController::class, 'create'])->name('header.create');
            Route::post('header', [RkoHeaderController::class, 'store'])->name('header.store');
            Route::get('header/{rkoHeader}', [RkoHeaderController::class, 'show'])->name('header.show');
            Route::get('header/{rkoHeader}/edit', [RkoHeaderController::class, 'edit'])->name('header.edit');
            Route::match(['put', 'patch'], 'header/{rkoHeader}', [RkoHeaderController::class, 'update'])->name('header.update');
            Route::delete('header/{rkoHeader}', [RkoHeaderController::class, 'destroy'])->name('header.destroy');

            Route::get('detail', [RkoDetailController::class, 'index'])->name('detail.index');
        });
    });

    Route::middleware('role:admin,petugas_gudang,pimpinan')->group(function () {
        Route::get('stock-monitoring/current-stock', [StockMonitoringController::class, 'currentStock'])->name('stock-monitoring.current-stock');
        Route::get('stock-monitoring/batches', [StockMonitoringController::class, 'batches'])->name('stock-monitoring.batches');
        Route::get('stock-monitoring/stock-card', [StockMonitoringController::class, 'stockCard'])->name('stock-monitoring.stock-card');
        Route::get('reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
        Route::get('reports/receipts', [ReportController::class, 'receipts'])->name('reports.receipts');
        Route::get('reports/distributions', [ReportController::class, 'distributions'])->name('reports.distributions');
        Route::get('reports/adjustments', [ReportController::class, 'adjustments'])->name('reports.adjustments');
        Route::get('reports/rko-realization', [ReportController::class, 'rkoRealization'])->name('reports.rko-realization');

        Route::prefix('monitoring')->name('monitoring.')->group(function () {
            Route::get('stok-terkini', [StockMonitoringController::class, 'currentStock'])->name('stok.index');
            Route::get('batch-kedaluwarsa', [StockMonitoringController::class, 'batches'])->name('batch.index');
            Route::get('kartu-stok', [StockMonitoringController::class, 'stockCard'])->name('kartu-stok.index');
        });

        Route::prefix('laporan')->name('laporan.')->group(function () {
            Route::get('stok', [ReportController::class, 'stock'])->name('stok');
            Route::get('realisasi-pengadaan', [ReportController::class, 'receipts'])->name('pengadaan');
            Route::get('distribusi-obat', [ReportController::class, 'distributions'])->name('distribusi');
            Route::get('penyesuaian-stok', [ReportController::class, 'adjustments'])->name('penyesuaian');
            Route::get('rko-vs-realisasi', [ReportController::class, 'rkoRealization'])->name('rko');
        });
    });

    Route::middleware('role:admin,pimpinan')->group(function () {
        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });
});

require __DIR__.'/auth.php';
