<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DistributionDestinationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FundingSourceController;
use App\Http\Controllers\MedicineCategoryController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProcurementRealizationController;
use App\Http\Controllers\RkoDetailController;
use App\Http\Controllers\RkoHeaderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockMutationController;
use App\Http\Controllers\StockMonitoringController;
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
        Route::resource('funding-sources', FundingSourceController::class);
        Route::resource('distribution-destinations', DistributionDestinationController::class);
        Route::resource('stock-mutations', StockMutationController::class);

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
        });

        Route::prefix('rko')->name('rko.')->group(function () {
            Route::get('sumber-dana', [FundingSourceController::class, 'index'])->name('sumber-dana.index');
            Route::get('sumber-dana/create', [FundingSourceController::class, 'create'])->name('sumber-dana.create');
            Route::post('sumber-dana', [FundingSourceController::class, 'store'])->name('sumber-dana.store');
            Route::get('sumber-dana/{fundingSource}', [FundingSourceController::class, 'show'])->name('sumber-dana.show');
            Route::get('sumber-dana/{fundingSource}/edit', [FundingSourceController::class, 'edit'])->name('sumber-dana.edit');
            Route::match(['put', 'patch'], 'sumber-dana/{fundingSource}', [FundingSourceController::class, 'update'])->name('sumber-dana.update');
            Route::delete('sumber-dana/{fundingSource}', [FundingSourceController::class, 'destroy'])->name('sumber-dana.destroy');

            Route::get('header', [RkoHeaderController::class, 'index'])->name('header.index');
            Route::get('header/create', [RkoHeaderController::class, 'create'])->name('header.create');
            Route::post('header', [RkoHeaderController::class, 'store'])->name('header.store');
            Route::get('header/{rkoHeader}', [RkoHeaderController::class, 'show'])->name('header.show');
            Route::get('header/{rkoHeader}/edit', [RkoHeaderController::class, 'edit'])->name('header.edit');
            Route::match(['put', 'patch'], 'header/{rkoHeader}', [RkoHeaderController::class, 'update'])->name('header.update');
            Route::delete('header/{rkoHeader}', [RkoHeaderController::class, 'destroy'])->name('header.destroy');

            Route::get('detail', [RkoDetailController::class, 'index'])->name('detail.index');
        });

        Route::prefix('transaksi')->name('transaksi.')->group(function () {
            Route::get('mutasi-stok', [StockMutationController::class, 'index'])->name('mutasi.index');
            Route::get('mutasi-stok/create', [StockMutationController::class, 'create'])->name('mutasi.create');
            Route::post('mutasi-stok', [StockMutationController::class, 'store'])->name('mutasi.store');
            Route::get('mutasi-stok/{stockMutation}', [StockMutationController::class, 'show'])->name('mutasi.show');
            Route::get('mutasi-stok/{stockMutation}/edit', [StockMutationController::class, 'edit'])->name('mutasi.edit');
            Route::match(['put', 'patch'], 'mutasi-stok/{stockMutation}', [StockMutationController::class, 'update'])->name('mutasi.update');
            Route::delete('mutasi-stok/{stockMutation}', [StockMutationController::class, 'destroy'])->name('mutasi.destroy');
        });
    });

    Route::middleware('role:admin,petugas_gudang,pimpinan')->group(function () {
        Route::get('procurement-realizations', [ProcurementRealizationController::class, 'index'])->name('procurement-realizations.index');
        Route::get('stock-monitoring/current-stock', [StockMonitoringController::class, 'currentStock'])->name('stock-monitoring.current-stock');
        Route::get('reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
        Route::get('reports/mutations', [ReportController::class, 'mutations'])->name('reports.mutations');
        Route::get('reports/rko-realization', [ReportController::class, 'rkoRealization'])->name('reports.rko-realization');

        Route::prefix('monitoring')->name('monitoring.')->group(function () {
            Route::get('stok-terkini', [StockMonitoringController::class, 'currentStock'])->name('stok.index');
        });

        Route::prefix('laporan')->name('laporan.')->group(function () {
            Route::get('stok', [ReportController::class, 'stock'])->name('stok');
            Route::get('mutasi-stok', [ReportController::class, 'mutations'])->name('mutasi');
            Route::get('rko-vs-realisasi', [ReportController::class, 'rkoRealization'])->name('rko');
        });

        Route::prefix('rko')->name('rko.')->group(function () {
            Route::get('realisasi-pengadaan', [ProcurementRealizationController::class, 'index'])->name('realisasi.index');
        });
    });

    Route::middleware('role:admin,pimpinan')->group(function () {
        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });
});

require __DIR__.'/auth.php';
