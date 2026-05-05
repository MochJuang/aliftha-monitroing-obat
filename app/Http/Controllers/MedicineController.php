<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicineRequest;
use App\Models\Medicine;
use App\Models\MedicineStock;
use App\Models\StockMutation;
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
        $medicines = Medicine::query()
            ->with(['category', 'unit'])
            ->select('medicines.*')
            ->selectSub(
                MedicineStock::query()
                    ->select('quantity')
                    ->whereColumn('medicine_id', 'medicines.id')
                    ->orderByDesc('period')
                    ->orderByDesc('id')
                    ->limit(1),
                'current_stock'
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
        $medicine->load(['category', 'unit']);

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
                'delete' => 'Data obat tidak bisa dihapus karena masih dipakai pada RKO, mutasi stok, atau monitoring.',
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
                'medicine_type' => $medicine->medicine_type,
                'brand' => $medicine->brand,
                'dosage' => $medicine->dosage,
                'category_name' => $medicine->category?->name,
                'unit_name' => $medicine->unit?->name,
                'unit_symbol' => $medicine->unit?->symbol,
                'minimum_stock' => (int) $medicine->minimum_stock,
                'standard_price' => (float) ($medicine->standard_price ?? 0),
                'description' => $medicine->description,
                'is_active' => (bool) $medicine->is_active,
                'current_stock' => (int) ($medicine->current_stock ?? 0),
                'movement_summary' => [
                    'total_in' => (int) $movements->sum('qty_in'),
                    'total_out' => (int) $movements->sum('qty_out'),
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
        return DB::table('stock_mutation_items')
            ->join('stock_mutations', 'stock_mutations.id', '=', 'stock_mutation_items.stock_mutation_id')
            ->where('stock_mutation_items.medicine_id', $medicineId)
            ->orderByDesc('mutation_date')
            ->orderByDesc('stock_mutation_items.id')
            ->get()
            ->map(fn ($mutation) => [
                'movement_date' => Carbon::parse($mutation->mutation_date)->format('d M Y'),
                'type' => $mutation->mutation_type === 'MASUK' ? 'realisasi_pengadaan' : 'mutasi_stok',
                'reference_number' => $mutation->mutation_number ?: $mutation->reference,
                'counterpart_name' => null,
                'notes' => $mutation->notes,
                'qty_in' => $mutation->mutation_type === 'MASUK' ? (int) $mutation->quantity : 0,
                'qty_out' => $mutation->mutation_type === 'KELUAR' ? (int) $mutation->quantity : 0,
                'sort_date' => $mutation->mutation_date,
            ])
            ->sortByDesc('sort_date')
            ->values()
            ->map(function (array $movement) {
                $movement['type_label'] = match ($movement['type']) {
                    'realisasi_pengadaan' => 'Realisasi Pengadaan',
                    default => 'Mutasi Stok',
                };

                unset($movement['sort_date']);

                return $movement;
            });
    }
}
