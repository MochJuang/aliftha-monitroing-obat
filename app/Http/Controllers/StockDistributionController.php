<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockDistributionRequest;
use App\Models\DistributionDestination;
use App\Models\Medicine;
use App\Models\StockDistribution;
use App\Services\StockDistributionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class StockDistributionController extends Controller
{
    public function __construct(
        private readonly StockDistributionService $stockDistributionService
    ) {
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $status = trim((string) $request->string('status'));

        $distributions = StockDistribution::query()
            ->with(['destination', 'distributor'])
            ->withCount('items')
            ->withSum('items', 'quantity')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('distribution_number', 'like', "%{$search}%")
                        ->orWhereHas('destination', fn (Builder $destinationQuery) => $destinationQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when(in_array($status, ['draft', 'posted', 'cancelled'], true), fn (Builder $query) => $query->where('status', $status))
            ->latest('distributed_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('stock-distributions.index', compact('distributions', 'search', 'status'));
    }

    public function create(): View
    {
        $distribution = new StockDistribution([
            'distributed_date' => now()->toDateString(),
            'status' => 'draft',
        ]);

        return view('stock-distributions.create', [
            'distribution' => $distribution,
            'destinations' => DistributionDestination::where('is_active', true)->orderBy('name')->get(),
            'medicines' => $this->getAvailableMedicines(),
            'formItems' => null,
            'nextDistributionNumber' => $this->generateNextDistributionNumber(),
        ]);
    }

    public function store(StockDistributionRequest $request): RedirectResponse
    {
        try {
            $distribution = $this->stockDistributionService->store(
                $request->validated(),
                (int) $request->user()->id,
                $request->ip()
            );
        } catch (RuntimeException $exception) {
            return back()->withErrors([
                'stock' => $exception->getMessage(),
            ])->withInput();
        }

        return redirect()
            ->route('faskes.distribusi.show', $distribution)
            ->with('success', 'Distribusi obat berhasil disimpan.');
    }

    public function show(StockDistribution $stockDistribution): View
    {
        $stockDistribution->load(['destination', 'distributor', 'items.medicine', 'items.batch']);

        return view('stock-distributions.show', ['distribution' => $stockDistribution]);
    }

    public function edit(StockDistribution $stockDistribution): View
    {
        if ($stockDistribution->status !== 'draft') {
            abort(403, 'Hanya transaksi draft yang bisa diedit.');
        }

        $stockDistribution->load('items.medicine');

        $formItems = $stockDistribution->items
            ->groupBy('medicine_id')
            ->map(fn ($group) => [
                'medicine_id' => $group->first()->medicine_id,
                'quantity' => $group->sum('quantity'),
                'notes' => $group->pluck('notes')->filter()->first() ?? '',
            ])
            ->values()
            ->all();

        return view('stock-distributions.edit', [
            'distribution' => $stockDistribution,
            'destinations' => DistributionDestination::where('is_active', true)->orderBy('name')->get(),
            'medicines' => $this->getAvailableMedicines(),
            'formItems' => $formItems,
            'nextDistributionNumber' => $stockDistribution->distribution_number,
        ]);
    }

    public function update(StockDistributionRequest $request, StockDistribution $stockDistribution): RedirectResponse
    {
        try {
            $distribution = $this->stockDistributionService->update(
                $stockDistribution,
                $request->validated(),
                (int) $request->user()->id,
                $request->ip()
            );
        } catch (RuntimeException $exception) {
            return back()->withErrors([
                'update' => $exception->getMessage(),
            ])->withInput();
        }

        return redirect()
            ->route('faskes.distribusi.show', $distribution)
            ->with('success', 'Distribusi obat berhasil diperbarui.');
    }

    public function destroy(Request $request, StockDistribution $stockDistribution): RedirectResponse
    {
        try {
            $this->stockDistributionService->deleteDraft(
                $stockDistribution,
                (int) $request->user()->id,
                $request->ip()
            );
        } catch (RuntimeException $exception) {
            return back()->withErrors([
                'delete' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('faskes.distribusi.index')
            ->with('success', 'Draft distribusi obat berhasil dihapus.');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Medicine>
     */
    private function getAvailableMedicines()
    {
        return Medicine::query()
            ->with('unit')
            ->where('is_active', true)
            ->withSum([
                'batches as available_stock' => fn (Builder $query) => $query
                    ->available()
                    ->whereDate('expired_at', '>=', now()->toDateString()),
            ], 'qty_remaining')
            ->having('available_stock', '>', 0)
            ->orderBy('name')
            ->get();
    }

    private function generateNextDistributionNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "DST-{$year}-";

        $lastNumber = StockDistribution::query()
            ->where('distribution_number', 'like', "{$prefix}%")
            ->orderByDesc('distribution_number')
            ->value('distribution_number');

        if (! $lastNumber) {
            return "{$prefix}0001";
        }

        $sequence = (int) substr($lastNumber, -4) + 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
