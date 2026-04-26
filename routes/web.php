<?php

use App\Http\Controllers\DistributionDestinationController;
use App\Http\Controllers\MedicineCategoryController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockDistributionController;
use App\Http\Controllers\StockMonitoringController;
use App\Http\Controllers\StockReceiptController;
use App\Http\Controllers\StockSourceController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'active'])->name('dashboard');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:admin')->group(function () {
        // Route admin-only berikutnya ditambahkan di sini.
    });

    Route::middleware('role:admin,petugas_gudang')->group(function () {
        Route::resource('medicine-categories', MedicineCategoryController::class);
        Route::resource('units', UnitController::class);
        Route::resource('medicines', MedicineController::class);
        Route::resource('stock-sources', StockSourceController::class);
        Route::resource('distribution-destinations', DistributionDestinationController::class);
        Route::resource('stock-receipts', StockReceiptController::class);
        Route::resource('stock-distributions', StockDistributionController::class);
        Route::resource('stock-adjustments', StockAdjustmentController::class)->only(['index', 'create', 'store', 'show']);
    });

    Route::middleware('role:admin,petugas_gudang,pimpinan')->group(function () {
        Route::get('stock-monitoring/current-stock', [StockMonitoringController::class, 'currentStock'])->name('stock-monitoring.current-stock');
        Route::get('stock-monitoring/batches', [StockMonitoringController::class, 'batches'])->name('stock-monitoring.batches');
        Route::get('stock-monitoring/stock-card', [StockMonitoringController::class, 'stockCard'])->name('stock-monitoring.stock-card');
    });
});

require __DIR__.'/auth.php';
