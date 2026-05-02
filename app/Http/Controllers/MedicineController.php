<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicineRequest;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineCategory;
use App\Models\Unit;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MedicineController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $status = (string) $request->string('status');
        $today = now()->toDateString();

        $medicines = Medicine::query()
            ->with(['category', 'unit'])
            ->withSum([
                'batches as current_stock' => fn ($query) => $query
                    ->where('qty_remaining', '>', 0)
                    ->whereDate('expired_at', '>=', $today),
            ], 'qty_remaining')
            ->withCount([
                'batches as active_batch_count' => fn ($query) => $query
                    ->where('qty_remaining', '>', 0)
                    ->whereDate('expired_at', '>=', $today),
            ])
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
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['active', 'inactive'], true), function ($query) use ($status) {
                $query->where('is_active', $status === 'active');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $medicineDetails = $this->buildMedicineDetailsPayload($medicines->getCollection());

        return view('medicines.index', compact('medicines', 'medicineDetails', 'search', 'status'));
    }

    public function create(): View
    {
        $medicine = new Medicine(['is_active' => true]);

        return view('medicines.create', [
            'medicine' => $medicine,
            'categories' => MedicineCategory::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
        ]);
    }

    public function store(MedicineRequest $request): RedirectResponse
    {
        Medicine::create($request->validated());

        return redirect()
            ->route('master-obat.obat.index')
            ->with('success', 'Data obat berhasil ditambahkan.');
    }

    public function show(Medicine $medicine): View
    {
        $medicine->load(['category', 'unit'])->loadCount('batches');

        return view('medicines.show', compact('medicine'));
    }

    public function edit(Medicine $medicine): View
    {
        return view('medicines.edit', [
            'medicine' => $medicine,
            'categories' => MedicineCategory::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
        ]);
    }

    public function update(MedicineRequest $request, Medicine $medicine): RedirectResponse
    {
        $medicine->update($request->validated());

        return redirect()
            ->route('master-obat.obat.index')
            ->with('success', 'Data obat berhasil diperbarui.');
    }

    public function destroy(Medicine $medicine): RedirectResponse
    {
        try {
            $medicine->delete();

            return redirect()
                ->route('master-obat.obat.index')
                ->with('success', 'Data obat berhasil dihapus.');
        } catch (QueryException) {
            return back()->withErrors([
                'delete' => 'Data obat tidak bisa dihapus karena masih dipakai transaksi atau batch.',
            ]);
        }
    }

    /**
     * @param  Collection<int, Medicine>  $medicines
     * @return array<int, array<string, mixed>>
     */
    private function buildMedicineDetailsPayload(Collection $medicines): array
    {
        $details = [];

        foreach ($medicines as $medicine) {
            $movements = $this->buildMedicineMovements((int) $medicine->id);

            $details[$medicine->id] = [
                'id' => (int) $medicine->id,
                'code' => $medicine->code,
                'name' => $medicine->name,
                'brand' => $medicine->brand,
                'dosage' => $medicine->dosage,
                'category_name' => $medicine->category?->name,
                'unit_name' => $medicine->unit?->name,
                'unit_symbol' => $medicine->unit?->symbol,
                'minimum_stock' => (int) $medicine->minimum_stock,
                'description' => $medicine->description,
                'is_active' => (bool) $medicine->is_active,
                'current_stock' => (int) ($medicine->current_stock ?? 0),
                'active_batch_count' => (int) ($medicine->active_batch_count ?? 0),
                'nearest_expired_at' => $medicine->nearest_expired_at
                    ? Carbon::parse($medicine->nearest_expired_at)->format('d M Y')
                    : null,
                'movement_summary' => [
                    'total_in' => (int) $movements->sum('qty_in'),
                    'total_out' => (int) $movements->sum('qty_out'),
                    'total_adjustment' => (int) $movements->sum('adjustment_qty'),
                ],
                'movements' => $movements->take(12)->values()->all(),
            ];
        }

        return $details;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function buildMedicineMovements(int $medicineId): Collection
    {
        $receiptMovements = DB::table('stock_receipt_items')
            ->join('stock_receipts', 'stock_receipts.id', '=', 'stock_receipt_items.receipt_id')
            ->join('stock_sources', 'stock_sources.id', '=', 'stock_receipts.source_id')
            ->where('stock_receipt_items.medicine_id', $medicineId)
            ->where('stock_receipts.status', 'posted')
            ->get([
                'stock_receipts.received_date as movement_date',
                'stock_receipts.receipt_number as reference_number',
                'stock_receipt_items.batch_number',
                'stock_receipt_items.quantity',
                'stock_receipt_items.notes',
                'stock_sources.name as counterpart_name',
            ])
            ->map(fn ($item) => [
                'movement_date' => Carbon::parse($item->movement_date)->format('d M Y'),
                'type' => 'realisasi_pengadaan',
                'reference_number' => $item->reference_number,
                'batch_number' => $item->batch_number,
                'counterpart_name' => $item->counterpart_name,
                'notes' => $item->notes,
                'qty_in' => (int) $item->quantity,
                'qty_out' => 0,
                'adjustment_qty' => 0,
                'sort_date' => $item->movement_date,
            ]);

        $distributionMovements = DB::table('stock_distribution_items')
            ->join('stock_distributions', 'stock_distributions.id', '=', 'stock_distribution_items.distribution_id')
            ->join('distribution_destinations', 'distribution_destinations.id', '=', 'stock_distributions.destination_id')
            ->join('medicine_batches', 'medicine_batches.id', '=', 'stock_distribution_items.batch_id')
            ->where('stock_distribution_items.medicine_id', $medicineId)
            ->where('stock_distributions.status', 'posted')
            ->get([
                'stock_distributions.distributed_date as movement_date',
                'stock_distributions.distribution_number as reference_number',
                'medicine_batches.batch_number',
                'stock_distribution_items.quantity',
                'stock_distribution_items.notes',
                'distribution_destinations.name as counterpart_name',
            ])
            ->map(fn ($item) => [
                'movement_date' => Carbon::parse($item->movement_date)->format('d M Y'),
                'type' => 'distribusi_obat',
                'reference_number' => $item->reference_number,
                'batch_number' => $item->batch_number,
                'counterpart_name' => $item->counterpart_name,
                'notes' => $item->notes,
                'qty_in' => 0,
                'qty_out' => (int) $item->quantity,
                'adjustment_qty' => 0,
                'sort_date' => $item->movement_date,
            ]);

        $adjustmentMovements = DB::table('stock_adjustment_items')
            ->join('stock_adjustments', 'stock_adjustments.id', '=', 'stock_adjustment_items.adjustment_id')
            ->join('medicine_batches', 'medicine_batches.id', '=', 'stock_adjustment_items.batch_id')
            ->where('stock_adjustment_items.medicine_id', $medicineId)
            ->get([
                'stock_adjustments.adjustment_date as movement_date',
                'stock_adjustments.adjustment_number as reference_number',
                'stock_adjustments.adjustment_type',
                'medicine_batches.batch_number',
                'stock_adjustment_items.difference_qty',
                'stock_adjustment_items.reason',
            ])
            ->map(fn ($item) => [
                'movement_date' => Carbon::parse($item->movement_date)->format('d M Y'),
                'type' => 'penyesuaian_stok',
                'reference_number' => $item->reference_number,
                'batch_number' => $item->batch_number,
                'counterpart_name' => ucfirst((string) $item->adjustment_type),
                'notes' => $item->reason,
                'qty_in' => (int) $item->difference_qty > 0 ? (int) $item->difference_qty : 0,
                'qty_out' => (int) $item->difference_qty < 0 ? abs((int) $item->difference_qty) : 0,
                'adjustment_qty' => (int) $item->difference_qty,
                'sort_date' => $item->movement_date,
            ]);

        return $receiptMovements
            ->concat($distributionMovements)
            ->concat($adjustmentMovements)
            ->sortByDesc('sort_date')
            ->values()
            ->map(function (array $movement) {
                $movement['type_label'] = match ($movement['type']) {
                    'realisasi_pengadaan' => 'Realisasi Pengadaan',
                    'distribusi_obat' => 'Distribusi Obat',
                    default => 'Penyesuaian Stok',
                };

                unset($movement['sort_date']);

                return $movement;
            });
    }
}
