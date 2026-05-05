<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicineStock;
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

        $currentStockSubquery = $this->buildCurrentStockSubquery();

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
            'total_current_stock' => (int) DB::query()
                ->fromSub($summaryBaseQuery->toBase(), 'stock_summary')
                ->selectRaw('COALESCE(SUM(COALESCE(current_stock, 0)), 0) as total_stock')
                ->value('total_stock'),
            'low_stock_count' => DB::query()
                ->fromSub($summaryBaseQuery->toBase(), 'stock_summary')
                ->whereRaw('COALESCE(current_stock, 0) > 0 AND COALESCE(current_stock, 0) <= minimum_stock')
                ->count(),
            'empty_stock_count' => DB::query()
                ->fromSub($summaryBaseQuery->toBase(), 'stock_summary')
                ->whereRaw('COALESCE(current_stock, 0) = 0')
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
    private function buildCurrentStockSubquery(): Builder
    {
        return MedicineStock::query()
            ->select('quantity')
            ->whereColumn('medicine_id', 'medicines.id')
            ->orderByDesc('period')
            ->orderByDesc('id')
            ->limit(1);
    }
}
