<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockAdjustmentRequest;
use App\Models\MedicineBatch;
use App\Models\StockAdjustment;
use App\Services\StockAdjustmentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockAdjustmentController extends Controller
{
    public function __construct(
        private readonly StockAdjustmentService $stockAdjustmentService
    ) {
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $type = trim((string) $request->string('type'));

        $adjustments = StockAdjustment::query()
            ->with(['creator'])
            ->withCount('items')
            ->withSum('items', 'difference_qty')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('adjustment_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->when(in_array($type, ['opname', 'koreksi', 'expired', 'rusak'], true), fn (Builder $query) => $query->where('adjustment_type', $type))
            ->latest('adjustment_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('stock-adjustments.index', compact('adjustments', 'search', 'type'));
    }

    public function create(): View
    {
        $adjustment = new StockAdjustment([
            'adjustment_date' => now()->toDateString(),
            'adjustment_type' => 'opname',
        ]);

        return view('stock-adjustments.create', [
            'adjustment' => $adjustment,
            'batches' => $this->getAdjustableBatches(),
            'nextAdjustmentNumber' => $this->generateNextAdjustmentNumber(),
        ]);
    }

    public function store(StockAdjustmentRequest $request): RedirectResponse
    {
        $adjustment = $this->stockAdjustmentService->store(
            $request->validated(),
            (int) $request->user()->id,
            $request->ip()
        );

        return redirect()
            ->route('monitoring.penyesuaian.show', $adjustment)
            ->with('success', 'Penyesuaian stok berhasil disimpan.');
    }

    public function show(StockAdjustment $stockAdjustment): View
    {
        $stockAdjustment->load(['creator', 'items.medicine.unit', 'items.batch']);

        return view('stock-adjustments.show', ['adjustment' => $stockAdjustment]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\MedicineBatch>
     */
    private function getAdjustableBatches()
    {
        return MedicineBatch::query()
            ->with('medicine.unit')
            ->where('qty_remaining', '>', 0)
            ->orderBy('expired_at')
            ->orderBy('id')
            ->get();
    }

    private function generateNextAdjustmentNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "ADJ-{$year}-";

        $lastNumber = StockAdjustment::query()
            ->where('adjustment_number', 'like', "{$prefix}%")
            ->orderByDesc('adjustment_number')
            ->value('adjustment_number');

        if (! $lastNumber) {
            return "{$prefix}0001";
        }

        $sequence = (int) substr($lastNumber, -4) + 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
