<?php

namespace App\Http\Controllers;

use App\Models\DistributionDestination;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\StockAdjustment;
use App\Models\StockDistribution;
use App\Models\StockReceipt;
use App\Models\StockSource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function stock(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $categoryId = trim((string) $request->string('category_id'));
        $status = trim((string) $request->string('status'));

        $today = now()->toDateString();
        $almostExpiredDate = now()->addDays(30)->toDateString();

        $baseQuery = Medicine::query()
            ->leftJoin('medicine_categories', 'medicine_categories.id', '=', 'medicines.category_id')
            ->leftJoin('units', 'units.id', '=', 'medicines.unit_id')
            ->where('medicines.is_active', true)
            ->select([
                'medicines.id',
                'medicines.code',
                'medicines.name',
                'medicines.minimum_stock',
                'medicine_categories.name as category_name',
                'units.name as unit_name',
            ])
            ->selectSub($this->currentStockSubquery($today), 'current_stock')
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
                        ->orWhere('medicines.name', 'like', "%{$search}%");
                });
            })
            ->when($categoryId !== '', fn (Builder $query) => $query->where('medicines.category_id', $categoryId));

        $reports = DB::query()
            ->fromSub($baseQuery->toBase(), 'stock_reports')
            ->when($status !== '', function ($query) use ($status) {
                match ($status) {
                    'low' => $query->whereRaw('COALESCE(current_stock, 0) > 0 AND COALESCE(current_stock, 0) <= minimum_stock'),
                    'empty' => $query->whereRaw('COALESCE(current_stock, 0) = 0'),
                    'safe' => $query->whereRaw('COALESCE(current_stock, 0) > minimum_stock'),
                    default => null,
                };
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $summaryBaseQuery = Medicine::query()
            ->where('is_active', true)
            ->select(['medicines.id', 'medicines.minimum_stock'])
            ->selectSub($this->currentStockSubquery($today), 'current_stock');

        $summary = [
            'total_stock_qty' => (int) MedicineBatch::query()
                ->where('qty_remaining', '>', 0)
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
            'almost_expired_batches' => MedicineBatch::query()
                ->where('qty_remaining', '>', 0)
                ->whereBetween('expired_at', [$today, $almostExpiredDate])
                ->count(),
        ];

        $categories = DB::table('medicine_categories')->select(['id', 'name'])->orderBy('name')->get();

        return view('reports.stock', compact('reports', 'summary', 'categories', 'search', 'categoryId', 'status'));
    }

    public function receipts(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $sourceId = trim((string) $request->string('source_id'));
        $status = trim((string) $request->string('status'));
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to'));

        $reports = StockReceipt::query()
            ->with(['source', 'receiver'])
            ->withCount('items')
            ->withSum('items', 'quantity')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('receipt_number', 'like', "%{$search}%")
                        ->orWhereHas('source', fn (Builder $sourceQuery) => $sourceQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($sourceId !== '', fn (Builder $query) => $query->where('source_id', $sourceId))
            ->when(in_array($status, ['draft', 'posted', 'cancelled'], true), fn (Builder $query) => $query->where('status', $status))
            ->when($dateFrom !== '', fn (Builder $query) => $query->whereDate('received_date', '>=', $dateFrom))
            ->when($dateTo !== '', fn (Builder $query) => $query->whereDate('received_date', '<=', $dateTo))
            ->latest('received_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $summary = [
            'transaction_count' => $reports->total(),
            'total_qty' => (int) DB::table('stock_receipt_items')
                ->join('stock_receipts', 'stock_receipts.id', '=', 'stock_receipt_items.receipt_id')
                ->when($sourceId !== '', fn ($query) => $query->where('stock_receipts.source_id', $sourceId))
                ->when(in_array($status, ['draft', 'posted', 'cancelled'], true), fn ($query) => $query->where('stock_receipts.status', $status))
                ->when($dateFrom !== '', fn ($query) => $query->whereDate('stock_receipts.received_date', '>=', $dateFrom))
                ->when($dateTo !== '', fn ($query) => $query->whereDate('stock_receipts.received_date', '<=', $dateTo))
                ->sum('stock_receipt_items.quantity'),
            'posted_count' => StockReceipt::query()
                ->when($sourceId !== '', fn (Builder $query) => $query->where('source_id', $sourceId))
                ->when($dateFrom !== '', fn (Builder $query) => $query->whereDate('received_date', '>=', $dateFrom))
                ->when($dateTo !== '', fn (Builder $query) => $query->whereDate('received_date', '<=', $dateTo))
                ->where('status', 'posted')
                ->count(),
        ];

        $sources = StockSource::query()->orderBy('name')->get(['id', 'name']);

        return view('reports.receipts', compact('reports', 'summary', 'sources', 'search', 'sourceId', 'status', 'dateFrom', 'dateTo'));
    }

    public function distributions(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $destinationId = trim((string) $request->string('destination_id'));
        $status = trim((string) $request->string('status'));
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to'));

        $reports = StockDistribution::query()
            ->with(['destination', 'distributor'])
            ->withCount('items')
            ->withSum('items', 'quantity')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('distribution_number', 'like', "%{$search}%")
                        ->orWhereHas('destination', fn (Builder $destinationQuery) => $destinationQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($destinationId !== '', fn (Builder $query) => $query->where('destination_id', $destinationId))
            ->when(in_array($status, ['draft', 'posted', 'cancelled'], true), fn (Builder $query) => $query->where('status', $status))
            ->when($dateFrom !== '', fn (Builder $query) => $query->whereDate('distributed_date', '>=', $dateFrom))
            ->when($dateTo !== '', fn (Builder $query) => $query->whereDate('distributed_date', '<=', $dateTo))
            ->latest('distributed_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $summary = [
            'transaction_count' => $reports->total(),
            'total_qty' => (int) DB::table('stock_distribution_items')
                ->join('stock_distributions', 'stock_distributions.id', '=', 'stock_distribution_items.distribution_id')
                ->when($destinationId !== '', fn ($query) => $query->where('stock_distributions.destination_id', $destinationId))
                ->when(in_array($status, ['draft', 'posted', 'cancelled'], true), fn ($query) => $query->where('stock_distributions.status', $status))
                ->when($dateFrom !== '', fn ($query) => $query->whereDate('stock_distributions.distributed_date', '>=', $dateFrom))
                ->when($dateTo !== '', fn ($query) => $query->whereDate('stock_distributions.distributed_date', '<=', $dateTo))
                ->sum('stock_distribution_items.quantity'),
            'posted_count' => StockDistribution::query()
                ->when($destinationId !== '', fn (Builder $query) => $query->where('destination_id', $destinationId))
                ->when($dateFrom !== '', fn (Builder $query) => $query->whereDate('distributed_date', '>=', $dateFrom))
                ->when($dateTo !== '', fn (Builder $query) => $query->whereDate('distributed_date', '<=', $dateTo))
                ->where('status', 'posted')
                ->count(),
        ];

        $destinations = DistributionDestination::query()->orderBy('name')->get(['id', 'name']);

        return view('reports.distributions', compact('reports', 'summary', 'destinations', 'search', 'destinationId', 'status', 'dateFrom', 'dateTo'));
    }

    public function adjustments(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $type = trim((string) $request->string('type'));
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to'));

        $reports = StockAdjustment::query()
            ->with('creator')
            ->withCount('items')
            ->withSum('items', 'difference_qty')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('adjustment_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->when(in_array($type, ['opname', 'koreksi', 'expired', 'rusak'], true), fn (Builder $query) => $query->where('adjustment_type', $type))
            ->when($dateFrom !== '', fn (Builder $query) => $query->whereDate('adjustment_date', '>=', $dateFrom))
            ->when($dateTo !== '', fn (Builder $query) => $query->whereDate('adjustment_date', '<=', $dateTo))
            ->latest('adjustment_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $summary = [
            'transaction_count' => $reports->total(),
            'total_difference' => (int) DB::table('stock_adjustment_items')
                ->join('stock_adjustments', 'stock_adjustments.id', '=', 'stock_adjustment_items.adjustment_id')
                ->when(in_array($type, ['opname', 'koreksi', 'expired', 'rusak'], true), fn ($query) => $query->where('stock_adjustments.adjustment_type', $type))
                ->when($dateFrom !== '', fn ($query) => $query->whereDate('stock_adjustments.adjustment_date', '>=', $dateFrom))
                ->when($dateTo !== '', fn ($query) => $query->whereDate('stock_adjustments.adjustment_date', '<=', $dateTo))
                ->sum('stock_adjustment_items.difference_qty'),
            'expired_count' => StockAdjustment::query()
                ->when($dateFrom !== '', fn (Builder $query) => $query->whereDate('adjustment_date', '>=', $dateFrom))
                ->when($dateTo !== '', fn (Builder $query) => $query->whereDate('adjustment_date', '<=', $dateTo))
                ->where('adjustment_type', 'expired')
                ->count(),
        ];

        return view('reports.adjustments', compact('reports', 'summary', 'search', 'type', 'dateFrom', 'dateTo'));
    }

    private function currentStockSubquery(string $today): Builder
    {
        return MedicineBatch::query()
            ->selectRaw('SUM(qty_remaining)')
            ->whereColumn('medicine_id', 'medicines.id')
            ->where('qty_remaining', '>', 0)
            ->whereDate('expired_at', '>=', $today);
    }
}
