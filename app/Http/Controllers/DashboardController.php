<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\RkoHeader;
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

        $currentStockSubquery = MedicineBatch::query()
            ->selectRaw('SUM(qty_remaining)')
            ->whereColumn('medicine_id', 'medicines.id')
            ->where('qty_remaining', '>', 0)
            ->whereDate('expired_at', '>=', $today);

        $stockSummaryBaseQuery = Medicine::query()
            ->where('is_active', true)
            ->select([
                'medicines.id',
                'medicines.minimum_stock',
            ])
            ->selectSub($currentStockSubquery, 'current_stock');

        $summary = [
            'active_medicines' => Medicine::query()->where('is_active', true)->count(),
            'total_current_stock' => (int) MedicineBatch::query()
                ->where('qty_remaining', '>', 0)
                ->whereDate('expired_at', '>=', $today)
                ->sum('qty_remaining'),
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
            'total_realized_qty' => (int) DB::table('stock_receipt_items')
                ->join('stock_receipts', 'stock_receipts.id', '=', 'stock_receipt_items.receipt_id')
                ->whereNotNull('stock_receipts.rko_header_id')
                ->where('stock_receipts.status', 'posted')
                ->sum('stock_receipt_items.quantity'),
        ];

        $rkoSummary['coverage_percent'] = $rkoSummary['total_approved_qty'] > 0
            ? round(($rkoSummary['total_realized_qty'] / $rkoSummary['total_approved_qty']) * 100, 1)
            : 0;

        $todayMovements = [
            'receipts_count' => DB::table('stock_receipts')
                ->where('status', 'posted')
                ->whereDate('received_date', $today)
                ->count(),
            'receipts_qty' => (int) DB::table('stock_receipt_items')
                ->join('stock_receipts', 'stock_receipts.id', '=', 'stock_receipt_items.receipt_id')
                ->where('stock_receipts.status', 'posted')
                ->whereDate('stock_receipts.received_date', $today)
                ->sum('stock_receipt_items.quantity'),
            'distributions_count' => DB::table('stock_distributions')
                ->where('status', 'posted')
                ->whereDate('distributed_date', $today)
                ->count(),
            'distributions_qty' => (int) DB::table('stock_distribution_items')
                ->join('stock_distributions', 'stock_distributions.id', '=', 'stock_distribution_items.distribution_id')
                ->where('stock_distributions.status', 'posted')
                ->whereDate('stock_distributions.distributed_date', $today)
                ->sum('stock_distribution_items.quantity'),
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
        $recentReceipts = DB::table('stock_receipts')
            ->join('stock_sources', 'stock_sources.id', '=', 'stock_receipts.source_id')
            ->where('stock_receipts.status', 'posted')
            ->orderByDesc('stock_receipts.received_date')
            ->orderByDesc('stock_receipts.id')
            ->limit(5)
            ->get([
                'stock_receipts.received_date as movement_date',
                'stock_receipts.receipt_number as reference_number',
                'stock_sources.name as counterpart_name',
                'stock_receipts.notes',
            ])
            ->map(fn ($item) => [
                'movement_date' => $item->movement_date,
                'sort_type' => 1,
                'type' => 'stok_masuk',
                'reference_number' => $item->reference_number,
                'counterpart_name' => $item->counterpart_name,
                'notes' => $item->notes,
            ]);

        $recentDistributions = DB::table('stock_distributions')
            ->join('distribution_destinations', 'distribution_destinations.id', '=', 'stock_distributions.destination_id')
            ->where('stock_distributions.status', 'posted')
            ->orderByDesc('stock_distributions.distributed_date')
            ->orderByDesc('stock_distributions.id')
            ->limit(5)
            ->get([
                'stock_distributions.distributed_date as movement_date',
                'stock_distributions.distribution_number as reference_number',
                'distribution_destinations.name as counterpart_name',
                'stock_distributions.notes',
            ])
            ->map(fn ($item) => [
                'movement_date' => $item->movement_date,
                'sort_type' => 2,
                'type' => 'stok_keluar',
                'reference_number' => $item->reference_number,
                'counterpart_name' => $item->counterpart_name,
                'notes' => $item->notes,
            ]);

        return $recentReceipts
            ->concat($recentDistributions)
            ->sortByDesc(fn (array $item) => sprintf('%s-%s', $item['movement_date'], $item['sort_type']))
            ->take(8)
            ->values();
    }
}
