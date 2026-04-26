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

    private function buildCurrentStockSubquery(string $today): Builder
    {
        return MedicineBatch::query()
            ->selectRaw('SUM(qty_remaining)')
            ->whereColumn('medicine_id', 'medicines.id')
            ->where('qty_remaining', '>', 0)
            ->whereDate('expired_at', '>=', $today);
    }
}
