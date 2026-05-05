<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockMutationRequest;
use App\Models\ActivityLog;
use App\Models\DistributionDestination;
use App\Models\Medicine;
use App\Models\MedicineStock;
use App\Models\StockMutation;
use App\Models\StockMutationItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockMutationController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $type = trim((string) $request->string('type'));
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to'));

        $mutations = StockMutation::query()
            ->with(['items.medicine.unit', 'rkoHeader', 'destination'])
            ->withCount('items')
            ->withSum('items', 'quantity')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('mutation_number', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('destination', fn (Builder $destinationQuery) => $destinationQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('items.medicine', function (Builder $medicineQuery) use ($search) {
                            $medicineQuery->where('code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($type, ['MASUK', 'KELUAR'], true), fn (Builder $query) => $query->where('mutation_type', $type))
            ->when($dateFrom !== '', fn (Builder $query) => $query->whereDate('mutation_date', '>=', $dateFrom))
            ->when($dateTo !== '', fn (Builder $query) => $query->whereDate('mutation_date', '<=', $dateTo))
            ->latest('mutation_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $summary = [
            'transaction_count' => $mutations->total(),
            'total_in' => (int) DB::table('stock_mutation_items')
                ->join('stock_mutations', 'stock_mutations.id', '=', 'stock_mutation_items.stock_mutation_id')
                ->when($dateFrom !== '', fn ($query) => $query->whereDate('stock_mutations.mutation_date', '>=', $dateFrom))
                ->when($dateTo !== '', fn ($query) => $query->whereDate('stock_mutations.mutation_date', '<=', $dateTo))
                ->where('stock_mutations.mutation_type', 'MASUK')
                ->sum('stock_mutation_items.quantity'),
            'total_out' => (int) DB::table('stock_mutation_items')
                ->join('stock_mutations', 'stock_mutations.id', '=', 'stock_mutation_items.stock_mutation_id')
                ->when($dateFrom !== '', fn ($query) => $query->whereDate('stock_mutations.mutation_date', '>=', $dateFrom))
                ->when($dateTo !== '', fn ($query) => $query->whereDate('stock_mutations.mutation_date', '<=', $dateTo))
                ->where('stock_mutations.mutation_type', 'KELUAR')
                ->sum('stock_mutation_items.quantity'),
        ];

        return view('stock-mutations.index', compact(
            'mutations',
            'summary',
            'search',
            'type',
            'dateFrom',
            'dateTo'
        ));
    }

    public function create(): View
    {
        return view('stock-mutations.create', [
            'mutation' => new StockMutation([
                'mutation_number' => $this->generateNextMutationNumber(),
                'mutation_date' => now()->toDateString(),
                'mutation_type' => 'MASUK',
            ]),
            'medicines' => $this->getMedicines(),
            'destinations' => $this->getDestinations(),
            'formItems' => null,
        ]);
    }

    public function store(StockMutationRequest $request): RedirectResponse
    {
        $mutation = DB::transaction(function () use ($request) {
            $validated = $request->validated();
            $mutation = StockMutation::create($this->buildMutationPayload($validated, null, (int) $request->user()->id));
            $mutation->items()->createMany($this->normalizeItems($validated['items']));
            $this->syncMedicineStocks($mutation->items->pluck('medicine_id')->all());
            $this->logActivity(
                (int) $request->user()->id,
                'stock_mutations',
                'create',
                'Menambahkan mutasi stok '.$mutation->mutation_number,
                $request->ip()
            );

            return $mutation;
        });

        return redirect()
            ->route('transaksi.mutasi.show', $mutation)
            ->with('success', 'Mutasi stok berhasil ditambahkan.');
    }

    public function show(StockMutation $stockMutation): View
    {
        $stockMutation->load(['rkoHeader', 'destination', 'items.medicine.category', 'items.medicine.unit']);

        return view('stock-mutations.show', ['mutation' => $stockMutation]);
    }

    public function edit(StockMutation $stockMutation): View
    {
        $stockMutation->load('items');

        return view('stock-mutations.edit', [
            'mutation' => $stockMutation,
            'medicines' => $this->getMedicines(),
            'destinations' => $this->getDestinations(),
            'formItems' => $stockMutation->items->map(fn (StockMutationItem $item) => [
                'medicine_id' => (string) $item->medicine_id,
                'quantity' => $item->quantity,
                'notes' => $item->notes,
            ])->values()->all(),
        ]);
    }

    public function update(StockMutationRequest $request, StockMutation $stockMutation): RedirectResponse
    {
        DB::transaction(function () use ($request, $stockMutation) {
            $validated = $request->validated();
            $oldMedicineIds = $stockMutation->items()->pluck('medicine_id')->all();
            $stockMutation->update($this->buildMutationPayload($validated, $stockMutation, (int) ($stockMutation->created_by ?? $request->user()->id)));
            $stockMutation->items()->delete();
            $stockMutation->items()->createMany($this->normalizeItems($validated['items']));
            $newMedicineIds = $stockMutation->items()->pluck('medicine_id')->all();
            $this->syncMedicineStocks(array_unique([...$oldMedicineIds, ...$newMedicineIds]));
            $this->logActivity(
                (int) $request->user()->id,
                'stock_mutations',
                'update',
                'Memperbarui mutasi stok '.$stockMutation->mutation_number,
                $request->ip()
            );
        });

        return redirect()
            ->route('transaksi.mutasi.show', $stockMutation)
            ->with('success', 'Mutasi stok berhasil diperbarui.');
    }

    public function destroy(Request $request, StockMutation $stockMutation): RedirectResponse
    {
        DB::transaction(function () use ($request, $stockMutation) {
            $medicineIds = $stockMutation->items()->pluck('medicine_id')->all();
            $mutationNumber = $stockMutation->mutation_number;
            $stockMutation->delete();
            $this->syncMedicineStocks($medicineIds);
            $this->logActivity(
                (int) $request->user()->id,
                'stock_mutations',
                'delete',
                'Menghapus mutasi stok '.$mutationNumber,
                $request->ip()
            );
        });

        return redirect()
            ->route('transaksi.mutasi.index')
            ->with('success', 'Mutasi stok berhasil dihapus.');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Medicine>
     */
    private function getMedicines()
    {
        return Medicine::query()
            ->with('unit')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\DistributionDestination>
     */
    private function getDestinations()
    {
        return DistributionDestination::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'destination_type']);
    }

    /**
     * @param  array<int, int|string>  $medicineIds
     */
    public function syncMedicineStocks(array $medicineIds): void
    {
        foreach (collect($medicineIds)->map(fn ($id) => (int) $id)->filter()->unique()->all() as $medicineId) {
            $medicine = Medicine::query()->find($medicineId);

            if (! $medicine) {
                continue;
            }

            $totalIn = (int) DB::table('stock_mutation_items')
                ->join('stock_mutations', 'stock_mutations.id', '=', 'stock_mutation_items.stock_mutation_id')
                ->where('stock_mutation_items.medicine_id', $medicineId)
                ->where('stock_mutations.mutation_type', 'MASUK')
                ->sum('stock_mutation_items.quantity');

            $totalOut = (int) DB::table('stock_mutation_items')
                ->join('stock_mutations', 'stock_mutations.id', '=', 'stock_mutation_items.stock_mutation_id')
                ->where('stock_mutation_items.medicine_id', $medicineId)
                ->where('stock_mutations.mutation_type', 'KELUAR')
                ->sum('stock_mutation_items.quantity');

            $currentQuantity = max(0, $totalIn - $totalOut);
            $statusNote = match (true) {
                $currentQuantity === 0 => 'Kurang',
                $currentQuantity < (int) $medicine->minimum_stock => 'Kurang',
                $currentQuantity > ((int) $medicine->minimum_stock * 2) && (int) $medicine->minimum_stock > 0 => 'Berlebih',
                default => 'Aman',
            };

            MedicineStock::query()->updateOrCreate(
                [
                    'medicine_id' => $medicineId,
                    'period' => now()->format('Y-m'),
                ],
                [
                    'quantity' => $currentQuantity,
                    'input_date' => now()->toDateString(),
                    'status_note' => $statusNote,
                ]
            );
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function buildMutationPayload(array $validated, ?StockMutation $existing = null, ?int $createdBy = null): array
    {
        $firstMedicineId = (int) $validated['items'][0]['medicine_id'];
        $totalQuantity = (int) collect($validated['items'])->sum(fn (array $item) => (int) $item['quantity']);

        return [
            'mutation_number' => $validated['mutation_number'],
            'medicine_id' => $firstMedicineId,
            'mutation_date' => $validated['mutation_date'],
            'mutation_type' => $validated['mutation_type'],
            'quantity' => $totalQuantity,
            'distribution_destination_id' => $validated['distribution_destination_id'] ?? null,
            'created_by' => $existing?->created_by ?? $createdBy,
            'reference' => $validated['reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'rko_header_id' => $existing?->rko_header_id,
            'is_auto_generated' => $existing?->is_auto_generated ?? false,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function normalizeItems(array $items): array
    {
        return collect($items)
            ->map(fn (array $item) => [
                'medicine_id' => (int) $item['medicine_id'],
                'quantity' => (int) $item['quantity'],
                'notes' => $item['notes'] ?? null,
            ])
            ->values()
            ->all();
    }

    private function generateNextMutationNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "MTS-{$year}-";

        $lastNumber = StockMutation::query()
            ->where('mutation_number', 'like', "{$prefix}%")
            ->orderByDesc('mutation_number')
            ->value('mutation_number');

        if (! $lastNumber) {
            return "{$prefix}0001";
        }

        $sequence = (int) substr($lastNumber, -4) + 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    private function logActivity(int $userId, string $module, string $action, string $description, ?string $ipAddress): void
    {
        ActivityLog::query()->create([
            'user_id' => $userId,
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'ip_address' => $ipAddress,
        ]);
    }
}
