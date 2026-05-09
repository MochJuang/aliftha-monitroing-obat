<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicineStock;
use App\Models\RkoHeader;
use App\Models\StockMutation;
use App\Support\SimpleXlsxExporter;
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
            ->selectSub($this->currentStockSubquery(), 'current_stock')
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
            ->selectSub($this->currentStockSubquery(), 'current_stock');

        $summary = [
            'total_stock_qty' => (int) DB::query()
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
            'safe_stock_count' => DB::query()
                ->fromSub($summaryBaseQuery->toBase(), 'stock_summary')
                ->whereRaw('COALESCE(current_stock, 0) > minimum_stock')
                ->count(),
        ];

        $categories = DB::table('medicine_categories')->select(['id', 'name'])->orderBy('name')->get();

        return view('reports.stock', compact('reports', 'summary', 'categories', 'search', 'categoryId', 'status'));
    }

    public function stockExport(Request $request, string $format)
    {
        $rows = $this->stockReportQuery($request)
            ->get()
            ->map(fn ($report) => [
                $report->code,
                $report->name,
                $report->category_name ?? '-',
                $report->unit_name ?? '-',
                number_format((int) ($report->current_stock ?? 0)),
                number_format((int) $report->minimum_stock),
            ])
            ->all();

        return $this->exportReport(
            $format,
            'Laporan Stok',
            ['Kode', 'Obat', 'Kategori', 'Satuan', 'Stok', 'Minimum'],
            $rows,
            'laporan-stok'
        );
    }

    public function mutations(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $type = trim((string) $request->string('type'));
        $medicineId = trim((string) $request->string('medicine_id'));
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to'));

        $reports = StockMutation::query()
            ->with('medicine.unit')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('reference', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('medicine', function (Builder $medicineQuery) use ($search) {
                            $medicineQuery->where('code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($type, ['MASUK', 'KELUAR'], true), fn (Builder $query) => $query->where('mutation_type', $type))
            ->when($medicineId !== '', fn (Builder $query) => $query->where('medicine_id', $medicineId))
            ->when($dateFrom !== '', fn (Builder $query) => $query->whereDate('mutation_date', '>=', $dateFrom))
            ->when($dateTo !== '', fn (Builder $query) => $query->whereDate('mutation_date', '<=', $dateTo))
            ->latest('mutation_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $baseMutationQuery = StockMutation::query()
            ->when($medicineId !== '', fn (Builder $query) => $query->where('medicine_id', $medicineId))
            ->when($dateFrom !== '', fn (Builder $query) => $query->whereDate('mutation_date', '>=', $dateFrom))
            ->when($dateTo !== '', fn (Builder $query) => $query->whereDate('mutation_date', '<=', $dateTo));

        $summary = [
            'transaction_count' => $reports->total(),
            'total_in' => (int) (clone $baseMutationQuery)->where('mutation_type', 'MASUK')->sum('quantity'),
            'total_out' => (int) (clone $baseMutationQuery)->where('mutation_type', 'KELUAR')->sum('quantity'),
            'net_mutation' => (int) ((clone $baseMutationQuery)->where('mutation_type', 'MASUK')->sum('quantity') - (clone $baseMutationQuery)->where('mutation_type', 'KELUAR')->sum('quantity')),
        ];

        $medicines = Medicine::query()->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);

        return view('reports.mutations', compact('reports', 'summary', 'medicines', 'search', 'type', 'medicineId', 'dateFrom', 'dateTo'));
    }

    public function mutationsExport(Request $request, string $format)
    {
        $rows = $this->mutationReportQuery($request)
            ->get()
            ->map(fn (StockMutation $report) => [
                $report->mutation_date->format('d M Y'),
                $report->medicine?->code ?? '-',
                $report->medicine?->name ?? '-',
                $report->mutation_type,
                number_format((int) $report->quantity),
                $report->reference ?: '-',
                $report->notes ?: '-',
            ])
            ->all();

        return $this->exportReport(
            $format,
            'Laporan Mutasi Stok',
            ['Tanggal', 'Kode Obat', 'Obat', 'Jenis', 'Jumlah', 'Referensi', 'Keterangan'],
            $rows,
            'laporan-mutasi-stok'
        );
    }

    public function rkoRealization(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $status = trim((string) $request->string('status'));
        $periodYear = trim((string) $request->string('period_year'));

        $reports = RkoHeader::query()
            ->with(['submitter', 'items.medicine.unit'])
            ->withCount(['items', 'procurementRealizations as realization_rows_count'])
            ->withSum('items', 'planned_quantity')
            ->withSum('items', 'approved_quantity')
            ->selectSub(
                DB::table('procurement_realizations')
                    ->selectRaw('COALESCE(SUM(procurement_realizations.realized_quantity), 0)')
                    ->whereColumn('procurement_realizations.rko_header_id', 'rko_headers.id'),
                'posted_realized_quantity'
            )
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('rko_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['draft', 'submitted', 'approved', 'rejected'], true), fn (Builder $query) => $query->where('status', $status))
            ->when($periodYear !== '', fn (Builder $query) => $query->where('period_year', (int) $periodYear))
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $headerIds = $reports->getCollection()->pluck('id')->all();

        $realizationByHeaderAndMedicine = DB::table('procurement_realizations')
            ->whereIn('procurement_realizations.rko_header_id', $headerIds)
            ->groupBy('procurement_realizations.rko_header_id', 'procurement_realizations.medicine_id')
            ->selectRaw('procurement_realizations.rko_header_id, procurement_realizations.medicine_id, SUM(procurement_realizations.realized_quantity) as realized_quantity')
            ->get()
            ->keyBy(fn ($item) => $item->rko_header_id.'-'.$item->medicine_id);

        $reports->setCollection(
            $reports->getCollection()->map(function (RkoHeader $report) use ($realizationByHeaderAndMedicine) {
                $report->item_rows = $report->items
                    ->map(function ($item) use ($report, $realizationByHeaderAndMedicine) {
                        $realizedQty = (int) optional($realizationByHeaderAndMedicine->get($report->id.'-'.$item->medicine_id))->realized_quantity;
                        $approvedQty = (int) $item->approved_quantity;

                        return [
                            'medicine_code' => $item->medicine->code,
                            'medicine_name' => $item->medicine->name,
                            'unit_name' => $item->medicine->unit?->name,
                            'planned_quantity' => (int) $item->planned_quantity,
                            'approved_quantity' => $approvedQty,
                            'realized_quantity' => $realizedQty,
                            'difference_quantity' => $realizedQty - $approvedQty,
                            'coverage_percent' => $approvedQty > 0 ? round(($realizedQty / $approvedQty) * 100, 1) : 0,
                            'notes' => $item->notes,
                        ];
                    })
                    ->values();

                return $report;
            })
        );

        $baseSummaryQuery = RkoHeader::query()
            ->when(in_array($status, ['draft', 'submitted', 'approved', 'rejected'], true), fn (Builder $query) => $query->where('status', $status))
            ->when($periodYear !== '', fn (Builder $query) => $query->where('period_year', (int) $periodYear));

        $summary = [
            'total_headers' => (clone $baseSummaryQuery)->count(),
            'total_planned_qty' => (int) DB::table('rko_details')
                ->join('rko_headers', 'rko_headers.id', '=', 'rko_details.rko_header_id')
                ->when(in_array($status, ['draft', 'submitted', 'approved', 'rejected'], true), fn ($query) => $query->where('rko_headers.status', $status))
                ->when($periodYear !== '', fn ($query) => $query->where('rko_headers.period_year', (int) $periodYear))
                ->sum('rko_details.planned_quantity'),
            'total_approved_qty' => (int) DB::table('rko_details')
                ->join('rko_headers', 'rko_headers.id', '=', 'rko_details.rko_header_id')
                ->when(in_array($status, ['draft', 'submitted', 'approved', 'rejected'], true), fn ($query) => $query->where('rko_headers.status', $status))
                ->when($periodYear !== '', fn ($query) => $query->where('rko_headers.period_year', (int) $periodYear))
                ->sum('rko_details.approved_quantity'),
            'total_realized_qty' => (int) DB::table('procurement_realizations')
                ->join('rko_headers', 'rko_headers.id', '=', 'procurement_realizations.rko_header_id')
                ->when(in_array($status, ['draft', 'submitted', 'approved', 'rejected'], true), fn ($query) => $query->where('rko_headers.status', $status))
                ->when($periodYear !== '', fn ($query) => $query->where('rko_headers.period_year', (int) $periodYear))
                ->sum('procurement_realizations.realized_quantity'),
        ];

        $summary['coverage_percent'] = $summary['total_approved_qty'] > 0
            ? round(($summary['total_realized_qty'] / $summary['total_approved_qty']) * 100, 1)
            : 0;

        $availableYears = RkoHeader::query()
            ->select('period_year')
            ->distinct()
            ->orderByDesc('period_year')
            ->pluck('period_year');

        return view('reports.rko-realization', compact('reports', 'summary', 'availableYears', 'search', 'status', 'periodYear'));
    }

    public function rkoRealizationExport(Request $request, string $format)
    {
        $rows = $this->rkoRealizationReportCollection($request)
            ->flatMap(function (RkoHeader $report) {
                return $report->item_rows->map(fn (array $item) => [
                    $report->rko_number,
                    sprintf('%02d', $report->period_month).'/'.$report->period_year,
                    match ($report->status) {
                        'draft' => 'Draft',
                        'submitted' => 'Diajukan',
                        'approved' => 'Disetujui',
                        default => 'Ditolak',
                    },
                    $item['medicine_code'],
                    $item['medicine_name'],
                    $item['unit_name'] ?? '-',
                    number_format($item['planned_quantity']),
                    number_format($item['approved_quantity']),
                    number_format($item['realized_quantity']),
                    number_format($item['difference_quantity']),
                    number_format($item['coverage_percent'], 1).'%',
                    $item['notes'] ?: '-',
                ]);
            })
            ->values()
            ->all();

        return $this->exportReport(
            $format,
            'Laporan RKO vs Realisasi',
            ['Nomor RKO', 'Periode', 'Status', 'Kode Obat', 'Obat', 'Satuan', 'Rencana', 'Disetujui', 'Realisasi', 'Selisih', 'Cakupan', 'Catatan'],
            $rows,
            'laporan-rko-vs-realisasi'
        );
    }

    private function currentStockSubquery(): Builder
    {
        return MedicineStock::query()
            ->select('quantity')
            ->whereColumn('medicine_id', 'medicines.id')
            ->orderByDesc('period')
            ->orderByDesc('id')
            ->limit(1);
    }

    private function stockReportQuery(Request $request)
    {
        $search = trim((string) $request->string('search'));
        $categoryId = trim((string) $request->string('category_id'));
        $status = trim((string) $request->string('status'));

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
            ->selectSub($this->currentStockSubquery(), 'current_stock')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('medicines.code', 'like', "%{$search}%")
                        ->orWhere('medicines.name', 'like', "%{$search}%");
                });
            })
            ->when($categoryId !== '', fn (Builder $query) => $query->where('medicines.category_id', $categoryId));

        return DB::query()
            ->fromSub($baseQuery->toBase(), 'stock_reports')
            ->when($status !== '', function ($query) use ($status) {
                match ($status) {
                    'low' => $query->whereRaw('COALESCE(current_stock, 0) > 0 AND COALESCE(current_stock, 0) <= minimum_stock'),
                    'empty' => $query->whereRaw('COALESCE(current_stock, 0) = 0'),
                    'safe' => $query->whereRaw('COALESCE(current_stock, 0) > minimum_stock'),
                    default => null,
                };
            })
            ->orderBy('name');
    }

    private function mutationReportQuery(Request $request): Builder
    {
        $search = trim((string) $request->string('search'));
        $type = trim((string) $request->string('type'));
        $medicineId = trim((string) $request->string('medicine_id'));
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to'));

        return StockMutation::query()
            ->with('medicine.unit')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('reference', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('medicine', function (Builder $medicineQuery) use ($search) {
                            $medicineQuery->where('code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($type, ['MASUK', 'KELUAR'], true), fn (Builder $query) => $query->where('mutation_type', $type))
            ->when($medicineId !== '', fn (Builder $query) => $query->where('medicine_id', $medicineId))
            ->when($dateFrom !== '', fn (Builder $query) => $query->whereDate('mutation_date', '>=', $dateFrom))
            ->when($dateTo !== '', fn (Builder $query) => $query->whereDate('mutation_date', '<=', $dateTo))
            ->latest('mutation_date')
            ->latest('id');
    }

    private function rkoRealizationReportCollection(Request $request)
    {
        $search = trim((string) $request->string('search'));
        $status = trim((string) $request->string('status'));
        $periodYear = trim((string) $request->string('period_year'));

        $reports = RkoHeader::query()
            ->with(['items.medicine.unit'])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('rko_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['draft', 'submitted', 'approved', 'rejected'], true), fn (Builder $query) => $query->where('status', $status))
            ->when($periodYear !== '', fn (Builder $query) => $query->where('period_year', (int) $periodYear))
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->orderByDesc('id')
            ->get();

        $headerIds = $reports->pluck('id')->all();

        $realizationByHeaderAndMedicine = DB::table('procurement_realizations')
            ->whereIn('procurement_realizations.rko_header_id', $headerIds)
            ->groupBy('procurement_realizations.rko_header_id', 'procurement_realizations.medicine_id')
            ->selectRaw('procurement_realizations.rko_header_id, procurement_realizations.medicine_id, SUM(procurement_realizations.realized_quantity) as realized_quantity')
            ->get()
            ->keyBy(fn ($item) => $item->rko_header_id.'-'.$item->medicine_id);

        return $reports->map(function (RkoHeader $report) use ($realizationByHeaderAndMedicine) {
            $report->item_rows = $report->items
                ->map(function ($item) use ($report, $realizationByHeaderAndMedicine) {
                    $realizedQty = (int) optional($realizationByHeaderAndMedicine->get($report->id.'-'.$item->medicine_id))->realized_quantity;
                    $approvedQty = (int) $item->approved_quantity;

                    return [
                        'medicine_code' => $item->medicine->code,
                        'medicine_name' => $item->medicine->name,
                        'unit_name' => $item->medicine->unit?->name,
                        'planned_quantity' => (int) $item->planned_quantity,
                        'approved_quantity' => $approvedQty,
                        'realized_quantity' => $realizedQty,
                        'difference_quantity' => $realizedQty - $approvedQty,
                        'coverage_percent' => $approvedQty > 0 ? round(($realizedQty / $approvedQty) * 100, 1) : 0,
                        'notes' => $item->notes,
                    ];
                })
                ->values();

            return $report;
        });
    }

    private function exportReport(string $format, string $title, array $headings, array $rows, string $filename)
    {
        abort_unless(in_array($format, ['pdf', 'excel'], true), 404);

        if ($format === 'excel') {
            return SimpleXlsxExporter::download($title, $headings, $rows, $filename);
        }

        return view('reports.exports.print', compact('title', 'headings', 'rows'));
    }
}
