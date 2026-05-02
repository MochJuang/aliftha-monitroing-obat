<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

        $this->attachBatchDetailsToCurrentStock($medicines, $today, $almostExpiredDate);

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

    public function stockCard(Request $request): View
    {
        $medicineId = trim((string) $request->string('medicine_id'));
        $batchNumber = trim((string) $request->string('batch_number'));
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to'));

        $medicines = Medicine::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $selectedMedicine = $medicineId !== ''
            ? Medicine::query()->with(['category', 'unit'])->find($medicineId)
            : null;

        $summary = [
            'opening_balance' => 0,
            'total_in' => 0,
            'total_out' => 0,
            'total_adjustment' => 0,
            'closing_balance' => 0,
        ];

        $movements = collect();

        if ($selectedMedicine) {
            $openingBalance = $this->calculateOpeningBalance(
                $selectedMedicine->id,
                $dateFrom !== '' ? $dateFrom : null,
                $batchNumber !== '' ? $batchNumber : null
            );

            $movements = $this->buildStockCardMovements(
                $selectedMedicine->id,
                $dateFrom !== '' ? $dateFrom : null,
                $dateTo !== '' ? $dateTo : null,
                $batchNumber !== '' ? $batchNumber : null
            );

            $runningBalance = $openingBalance;
            $movements = $movements->map(function (array $movement) use (&$runningBalance) {
                $runningBalance += $movement['net_qty'];
                $movement['running_balance'] = $runningBalance;

                return $movement;
            });

            $summary = [
                'opening_balance' => $openingBalance,
                'total_in' => (int) $movements->sum('qty_in'),
                'total_out' => (int) $movements->sum('qty_out'),
                'total_adjustment' => (int) $movements->sum('adjustment_qty'),
                'closing_balance' => $runningBalance,
            ];
        }

        $paginatedMovements = $this->paginateCollection($movements, 10, $request, 'movements_page');

        return view('stock-monitoring.stock-card', compact(
            'medicines',
            'selectedMedicine',
            'summary',
            'paginatedMovements',
            'medicineId',
            'batchNumber',
            'dateFrom',
            'dateTo'
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

    private function attachBatchDetailsToCurrentStock(LengthAwarePaginator $medicines, string $today, string $almostExpiredDate): void
    {
        $medicineIds = $medicines->getCollection()->pluck('id')->filter()->all();

        if ($medicineIds === []) {
            return;
        }

        $batchMap = MedicineBatch::query()
            ->with(['receiptItem.stockReceipt.source'])
            ->whereIn('medicine_id', $medicineIds)
            ->where('qty_remaining', '>', 0)
            ->whereDate('expired_at', '>=', $today)
            ->orderBy('expired_at')
            ->orderBy('id')
            ->get()
            ->groupBy('medicine_id');

        $medicines->setCollection(
            $medicines->getCollection()->map(function ($medicine) use ($batchMap, $almostExpiredDate) {
                $medicine->batch_details = ($batchMap->get($medicine->id) ?? collect())
                    ->map(fn (MedicineBatch $batch) => [
                        'batch_number' => $batch->batch_number,
                        'expired_at' => $batch->expired_at->format('d M Y'),
                        'qty_received' => (int) $batch->qty_received,
                        'qty_remaining' => (int) $batch->qty_remaining,
                        'source_name' => $batch->receiptItem?->stockReceipt?->source?->name,
                        'received_date' => $batch->receiptItem?->stockReceipt?->received_date?->format('d M Y'),
                        'status_label' => $batch->expired_at->toDateString() <= $almostExpiredDate ? 'Hampir Expired' : 'Aman',
                        'status_color' => $batch->expired_at->toDateString() <= $almostExpiredDate ? 'sky' : 'emerald',
                    ])
                    ->values()
                    ->all();

                return $medicine;
            })
        );
    }

    private function calculateOpeningBalance(int $medicineId, ?string $dateFrom, ?string $batchNumber): int
    {
        if (! $dateFrom) {
            return 0;
        }

        $receiptTotal = DB::table('stock_receipt_items')
            ->join('stock_receipts', 'stock_receipts.id', '=', 'stock_receipt_items.receipt_id')
            ->when($batchNumber, fn ($query) => $query->where('stock_receipt_items.batch_number', $batchNumber))
            ->where('stock_receipt_items.medicine_id', $medicineId)
            ->where('stock_receipts.status', 'posted')
            ->whereDate('stock_receipts.received_date', '<', $dateFrom)
            ->sum('stock_receipt_items.quantity');

        $distributionTotal = DB::table('stock_distribution_items')
            ->join('stock_distributions', 'stock_distributions.id', '=', 'stock_distribution_items.distribution_id')
            ->join('medicine_batches', 'medicine_batches.id', '=', 'stock_distribution_items.batch_id')
            ->when($batchNumber, fn ($query) => $query->where('medicine_batches.batch_number', $batchNumber))
            ->where('stock_distribution_items.medicine_id', $medicineId)
            ->where('stock_distributions.status', 'posted')
            ->whereDate('stock_distributions.distributed_date', '<', $dateFrom)
            ->sum('stock_distribution_items.quantity');

        $adjustmentTotal = DB::table('stock_adjustment_items')
            ->join('stock_adjustments', 'stock_adjustments.id', '=', 'stock_adjustment_items.adjustment_id')
            ->join('medicine_batches', 'medicine_batches.id', '=', 'stock_adjustment_items.batch_id')
            ->when($batchNumber, fn ($query) => $query->where('medicine_batches.batch_number', $batchNumber))
            ->where('stock_adjustment_items.medicine_id', $medicineId)
            ->whereDate('stock_adjustments.adjustment_date', '<', $dateFrom)
            ->sum('stock_adjustment_items.difference_qty');

        return (int) $receiptTotal - (int) $distributionTotal + (int) $adjustmentTotal;
    }

    private function buildStockCardMovements(int $medicineId, ?string $dateFrom, ?string $dateTo, ?string $batchNumber): Collection
    {
        $receiptMovements = DB::table('stock_receipt_items')
            ->join('stock_receipts', 'stock_receipts.id', '=', 'stock_receipt_items.receipt_id')
            ->join('stock_sources', 'stock_sources.id', '=', 'stock_receipts.source_id')
            ->where('stock_receipt_items.medicine_id', $medicineId)
            ->where('stock_receipts.status', 'posted')
            ->when($batchNumber, fn ($query) => $query->where('stock_receipt_items.batch_number', $batchNumber))
            ->when($dateFrom, fn ($query) => $query->whereDate('stock_receipts.received_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('stock_receipts.received_date', '<=', $dateTo))
            ->orderBy('stock_receipts.received_date')
            ->orderBy('stock_receipt_items.id')
            ->get([
                'stock_receipt_items.id as row_id',
                'stock_receipts.received_date as movement_date',
                'stock_receipts.receipt_number as reference_number',
                'stock_receipt_items.batch_number',
                'stock_receipt_items.quantity',
                'stock_receipt_items.notes',
                'stock_sources.name as counterpart_name',
            ])
            ->map(fn ($item) => [
                'movement_date' => $item->movement_date,
                'sort_type' => 1,
                'row_id' => 'receipt-'.$item->row_id,
                'type' => 'stok_masuk',
                'reference_number' => $item->reference_number,
                'batch_number' => $item->batch_number,
                'counterpart_name' => $item->counterpart_name,
                'notes' => $item->notes,
                'qty_in' => (int) $item->quantity,
                'qty_out' => 0,
                'adjustment_qty' => 0,
                'net_qty' => (int) $item->quantity,
            ]);

        $distributionMovements = DB::table('stock_distribution_items')
            ->join('stock_distributions', 'stock_distributions.id', '=', 'stock_distribution_items.distribution_id')
            ->join('distribution_destinations', 'distribution_destinations.id', '=', 'stock_distributions.destination_id')
            ->join('medicine_batches', 'medicine_batches.id', '=', 'stock_distribution_items.batch_id')
            ->where('stock_distribution_items.medicine_id', $medicineId)
            ->where('stock_distributions.status', 'posted')
            ->when($batchNumber, fn ($query) => $query->where('medicine_batches.batch_number', $batchNumber))
            ->when($dateFrom, fn ($query) => $query->whereDate('stock_distributions.distributed_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('stock_distributions.distributed_date', '<=', $dateTo))
            ->orderBy('stock_distributions.distributed_date')
            ->orderBy('stock_distribution_items.id')
            ->get([
                'stock_distribution_items.id as row_id',
                'stock_distributions.distributed_date as movement_date',
                'stock_distributions.distribution_number as reference_number',
                'medicine_batches.batch_number',
                'stock_distribution_items.quantity',
                'stock_distribution_items.notes',
                'distribution_destinations.name as counterpart_name',
            ])
            ->map(fn ($item) => [
                'movement_date' => $item->movement_date,
                'sort_type' => 2,
                'row_id' => 'distribution-'.$item->row_id,
                'type' => 'stok_keluar',
                'reference_number' => $item->reference_number,
                'batch_number' => $item->batch_number,
                'counterpart_name' => $item->counterpart_name,
                'notes' => $item->notes,
                'qty_in' => 0,
                'qty_out' => (int) $item->quantity,
                'adjustment_qty' => 0,
                'net_qty' => -1 * (int) $item->quantity,
            ]);

        $adjustmentMovements = DB::table('stock_adjustment_items')
            ->join('stock_adjustments', 'stock_adjustments.id', '=', 'stock_adjustment_items.adjustment_id')
            ->join('medicine_batches', 'medicine_batches.id', '=', 'stock_adjustment_items.batch_id')
            ->where('stock_adjustment_items.medicine_id', $medicineId)
            ->when($batchNumber, fn ($query) => $query->where('medicine_batches.batch_number', $batchNumber))
            ->when($dateFrom, fn ($query) => $query->whereDate('stock_adjustments.adjustment_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('stock_adjustments.adjustment_date', '<=', $dateTo))
            ->orderBy('stock_adjustments.adjustment_date')
            ->orderBy('stock_adjustment_items.id')
            ->get([
                'stock_adjustment_items.id as row_id',
                'stock_adjustments.adjustment_date as movement_date',
                'stock_adjustments.adjustment_number as reference_number',
                'stock_adjustments.adjustment_type',
                'medicine_batches.batch_number',
                'stock_adjustment_items.difference_qty',
                'stock_adjustment_items.reason',
            ])
            ->map(fn ($item) => [
                'movement_date' => $item->movement_date,
                'sort_type' => 3,
                'row_id' => 'adjustment-'.$item->row_id,
                'type' => 'adjustment',
                'reference_number' => $item->reference_number,
                'batch_number' => $item->batch_number,
                'counterpart_name' => ucfirst((string) $item->adjustment_type),
                'notes' => $item->reason,
                'qty_in' => (int) $item->difference_qty > 0 ? (int) $item->difference_qty : 0,
                'qty_out' => (int) $item->difference_qty < 0 ? abs((int) $item->difference_qty) : 0,
                'adjustment_qty' => (int) $item->difference_qty,
                'net_qty' => (int) $item->difference_qty,
            ]);

        return $receiptMovements
            ->concat($distributionMovements)
            ->concat($adjustmentMovements)
            ->sortBy([
                ['movement_date', 'asc'],
                ['sort_type', 'asc'],
                ['row_id', 'asc'],
            ])
            ->values();
    }

    private function paginateCollection(Collection $items, int $perPage, Request $request, string $pageName = 'page'): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);
        $results = $items->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $results,
            $items->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => $pageName,
                'query' => $request->query(),
            ]
        );
    }
}
