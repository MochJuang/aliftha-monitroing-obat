<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Medicine;
use App\Models\MedicineStock;
use App\Models\RkoHeader;
use App\Models\StockMutation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = now()->toDateString();
        $currentStockSubquery = MedicineStock::query()
            ->select('quantity')
            ->whereColumn('medicine_id', 'medicines.id')
            ->orderByDesc('period')
            ->orderByDesc('id')
            ->limit(1);

        $stockSummaryBaseQuery = Medicine::query()
            ->where('is_active', true)
            ->select([
                'medicines.id',
                'medicines.minimum_stock',
            ])
            ->selectSub($currentStockSubquery, 'current_stock');

        $summary = [
            'active_medicines' => Medicine::query()->where('is_active', true)->count(),
            'total_current_stock' => (int) DB::query()
                ->fromSub($stockSummaryBaseQuery->toBase(), 'stock_summary')
                ->selectRaw('COALESCE(SUM(COALESCE(current_stock, 0)), 0) as total_stock')
                ->value('total_stock'),
            'low_stock_medicines' => DB::query()
                ->fromSub($stockSummaryBaseQuery->toBase(), 'stock_summary')
                ->whereRaw('COALESCE(current_stock, 0) > 0 AND COALESCE(current_stock, 0) <= minimum_stock')
                ->count(),
            'empty_stock_medicines' => DB::query()
                ->fromSub($stockSummaryBaseQuery->toBase(), 'stock_summary')
                ->whereRaw('COALESCE(current_stock, 0) = 0')
                ->count(),
        ];

        $rkoSummary = [
            'total_headers' => RkoHeader::count(),
            'approved_headers' => RkoHeader::where('status', 'approved')->count(),
            'total_approved_qty' => (int) DB::table('rko_details')->sum('approved_quantity'),
            'total_realized_qty' => (int) DB::table('stock_mutation_items')
                ->join('stock_mutations', 'stock_mutations.id', '=', 'stock_mutation_items.stock_mutation_id')
                ->whereNotNull('stock_mutations.rko_header_id')
                ->where('stock_mutations.mutation_type', 'MASUK')
                ->sum('stock_mutation_items.quantity'),
        ];

        $rkoSummary['coverage_percent'] = $rkoSummary['total_approved_qty'] > 0
            ? round(($rkoSummary['total_realized_qty'] / $rkoSummary['total_approved_qty']) * 100, 1)
            : 0;

        $todayMovements = [
            'receipts_count' => StockMutation::query()
                ->where('mutation_type', 'MASUK')
                ->whereDate('mutation_date', $today)
                ->count(),
            'receipts_qty' => (int) StockMutation::query()
                ->where('mutation_type', 'MASUK')
                ->whereDate('mutation_date', $today)
                ->sum('quantity'),
            'distributions_count' => StockMutation::query()
                ->where('mutation_type', 'KELUAR')
                ->whereDate('mutation_date', $today)
                ->count(),
            'distributions_qty' => (int) StockMutation::query()
                ->where('mutation_type', 'KELUAR')
                ->whereDate('mutation_date', $today)
                ->sum('quantity'),
        ];

        $lowStockMedicines = DB::query()
            ->fromSub(
                Medicine::query()
                    ->leftJoin('units', 'units.id', '=', 'medicines.unit_id')
                    ->where('medicines.is_active', true)
                    ->select([
                        'medicines.id',
                        'medicines.code',
                        'medicines.name',
                        'medicines.minimum_stock',
                        'units.name as unit_name',
                    ])
                    ->selectSub($currentStockSubquery, 'current_stock')
                    ->toBase(),
                'stock_alerts'
            )
            ->whereRaw('COALESCE(current_stock, 0) <= minimum_stock')
            ->orderBy('current_stock')
            ->orderBy('name')
            ->limit(5)
            ->get();

        $recentTransactions = $this->buildRecentTransactions();

        $recentActivities = ActivityLog::query()
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        return view('dashboard', [
            'summary' => $summary,
            'todayMovements' => $todayMovements,
            'lowStockMedicines' => $lowStockMedicines,
            'recentTransactions' => $recentTransactions,
            'recentActivities' => $recentActivities,
            'activeUser' => Auth::user(),
            'rkoSummary' => $rkoSummary,
        ]);
    }

    private function buildRecentTransactions(): Collection
    {
        return StockMutation::query()
            ->orderByDesc('mutation_date')
            ->orderByDesc('id')
            ->limit(8)
            ->get(['mutation_date as movement_date', 'mutation_type', 'reference as reference_number', 'notes'])
            ->map(fn (StockMutation $item) => [
                'movement_date' => $item->movement_date,
                'sort_type' => $item->mutation_type === 'MASUK' ? 1 : 2,
                'type' => $item->mutation_type === 'MASUK' ? 'stok_masuk' : 'stok_keluar',
                'reference_number' => $item->reference_number,
                'counterpart_name' => null,
                'notes' => $item->notes,
            ])
            ->sortByDesc(fn (array $item) => sprintf('%s-%s', $item['movement_date'], $item['sort_type']))
            ->take(8)
            ->values();
    }
}
