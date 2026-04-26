<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockMonitoringController extends Controller
{
    public function currentStock(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $status = trim((string) $request->string('status'));
        $categoryId = trim((string) $request->string('category_id'));

        $today = now()->toDateString();
        $almostExpiredDate = now()->addDays(30)->toDateString();
        $currentStockSubquery = $this->buildCurrentStockSubquery($today);

        $baseMedicinesQuery = Medicine::query()
            ->leftJoin('medicine_categories', 'medicine_categories.id', '=', 'medicines.category_id')
            ->leftJoin('units', 'units.id', '=', 'medicines.unit_id')
            ->where('medicines.is_active', true)
            ->select([
                'medicines.id',
                'medicines.code',
                'medicines.name',
                'medicines.brand',
                'medicines.dosage',
                'medicines.minimum_stock',
                'medicines.category_id',
                'medicine_categories.name as category_name',
                'units.name as unit_name',
            ])
            ->selectSub($currentStockSubquery, 'current_stock')
            ->selectSub(
                MedicineBatch::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('medicine_id', 'medicines.id')
                    ->where('qty_remaining', '>', 0)
                    ->whereDate('expired_at', '>=', $today),
                'active_batch_count'
            )
            ->selectSub(
                MedicineBatch::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('medicine_id', 'medicines.id')
                    ->where('qty_remaining', '>', 0)
                    ->whereBetween('expired_at', [$today, $almostExpiredDate]),
                'almost_expired_batch_count'
            )
            ->selectSub(
                MedicineBatch::query()
                    ->select('expired_at')
                    ->whereColumn('medicine_id', 'medicines.id')
                    ->where('qty_remaining', '>', 0)
                    ->whereDate('expired_at', '>=', $today)
                    ->orderBy('expired_at')
                    ->limit(1),
                'nearest_expired_at'
            )
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('medicines.code', 'like', "%{$search}%")
                        ->orWhere('medicines.name', 'like', "%{$search}%")
                        ->orWhere('medicines.brand', 'like', "%{$search}%");
                });
            })
            ->when($categoryId !== '', fn (Builder $query) => $query->where('medicines.category_id', $categoryId));

        $medicines = DB::query()
            ->fromSub($baseMedicinesQuery->toBase(), 'stock_monitoring')
            ->when($status !== '', function ($query) use ($status) {
                match ($status) {
                    'safe' => $query->whereRaw('COALESCE(current_stock, 0) > minimum_stock'),
                    'low' => $query->whereRaw('COALESCE(current_stock, 0) > 0 AND COALESCE(current_stock, 0) <= minimum_stock'),
                    'empty' => $query->whereRaw('COALESCE(current_stock, 0) = 0'),
                    'almost_expired' => $query->whereRaw('COALESCE(almost_expired_batch_count, 0) > 0'),
                    default => null,
                };
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $summaryBaseQuery = Medicine::query()
            ->where('is_active', true)
            ->select([
                'medicines.id',
                'medicines.minimum_stock',
            ])
            ->selectSub($currentStockSubquery, 'current_stock');

        $summary = [
            'total_medicines' => Medicine::query()->where('is_active', true)->count(),
            'total_current_stock' => (int) MedicineBatch::query()
                ->available()
                ->whereDate('expired_at', '>=', $today)
                ->sum('qty_remaining'),
            'low_stock_count' => DB::query()
                ->fromSub($summaryBaseQuery->toBase(), 'stock_summary')
                ->whereRaw('COALESCE(current_stock, 0) > 0 AND COALESCE(current_stock, 0) <= minimum_stock')
                ->count(),
            'empty_stock_count' => DB::query()
                ->fromSub($summaryBaseQuery->toBase(), 'stock_summary')
                ->whereRaw('COALESCE(current_stock, 0) = 0')
                ->count(),
            'almost_expired_batch_count' => MedicineBatch::query()
                ->available()
                ->whereBetween('expired_at', [$today, $almostExpiredDate])
                ->count(),
        ];

        $categories = DB::table('medicine_categories')
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        return view('stock-monitoring.current-stock', compact(
            'medicines',
            'summary',
            'categories',
            'search',
            'status',
            'categoryId'
        ));
    }

    public function batches(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $status = trim((string) $request->string('status'));
        $categoryId = trim((string) $request->string('category_id'));

        $today = now()->startOfDay();
        $almostExpiredDate = now()->addDays(30)->endOfDay();

        $batches = MedicineBatch::query()
            ->with([
                'medicine.category',
                'medicine.unit',
                'receiptItem.stockReceipt.source',
            ])
            ->where('qty_remaining', '>', 0)
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('batch_number', 'like', "%{$search}%")
                        ->orWhereHas('medicine', function (Builder $medicineQuery) use ($search) {
                            $medicineQuery->where('code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('brand', 'like', "%{$search}%");
                        })
                        ->orWhereHas('receiptItem.stockReceipt.source', fn (Builder $sourceQuery) => $sourceQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($categoryId !== '', fn (Builder $query) => $query->whereHas('medicine', fn (Builder $medicineQuery) => $medicineQuery->where('category_id', $categoryId)))
            ->when($status !== '', function (Builder $query) use ($status, $today, $almostExpiredDate) {
                match ($status) {
                    'expired' => $query->whereDate('expired_at', '<', $today->toDateString()),
                    'almost_expired' => $query->whereBetween('expired_at', [$today->toDateString(), $almostExpiredDate->toDateString()]),
                    'safe' => $query->whereDate('expired_at', '>', $almostExpiredDate->toDateString()),
                    default => null,
                };
            })
            ->orderBy('expired_at')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        $summary = [
            'total_batches' => MedicineBatch::query()->where('qty_remaining', '>', 0)->count(),
            'expired_batches' => MedicineBatch::query()
                ->where('qty_remaining', '>', 0)
                ->whereDate('expired_at', '<', $today->toDateString())
                ->count(),
            'almost_expired_batches' => MedicineBatch::query()
                ->where('qty_remaining', '>', 0)
                ->whereBetween('expired_at', [$today->toDateString(), $almostExpiredDate->toDateString()])
                ->count(),
            'safe_batches' => MedicineBatch::query()
                ->where('qty_remaining', '>', 0)
                ->whereDate('expired_at', '>', $almostExpiredDate->toDateString())
                ->count(),
            'expired_stock_qty' => (int) MedicineBatch::query()
                ->where('qty_remaining', '>', 0)
                ->whereDate('expired_at', '<', $today->toDateString())
                ->sum('qty_remaining'),
        ];

        $categories = DB::table('medicine_categories')
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        return view('stock-monitoring.batches', compact(
            'batches',
            'summary',
            'categories',
            'search',
            'status',
            'categoryId'
        ));
    }

    private function buildCurrentStockSubquery(string $today): Builder
    {
        return MedicineBatch::query()
            ->selectRaw('SUM(qty_remaining)')
            ->whereColumn('medicine_id', 'medicines.id')
            ->where('qty_remaining', '>', 0)
            ->whereDate('expired_at', '>=', $today);
    }
}
