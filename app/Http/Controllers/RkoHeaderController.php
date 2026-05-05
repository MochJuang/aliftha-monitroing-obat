<?php

namespace App\Http\Controllers;

use App\Http\Requests\RkoApprovalRequest;
use App\Http\Requests\RkoHeaderRequest;
use App\Models\ActivityLog;
use App\Models\FundingSource;
use App\Models\Medicine;
use App\Models\RkoHeader;
use App\Models\StockMutation;
use App\Http\Controllers\StockMutationController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RkoHeaderController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $status = trim((string) $request->string('status'));
        $periodYear = trim((string) $request->string('period_year'));
        $fundingSourceId = trim((string) $request->string('funding_source_id'));

        $baseQuery = RkoHeader::query()
            ->with(['submitter', 'approver', 'fundingSource'])
            ->withCount('items')
            ->withSum('items', 'planned_quantity')
            ->withSum('items', 'approved_quantity')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('rko_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['draft', 'submitted', 'approved', 'rejected'], true), fn (Builder $query) => $query->where('status', $status))
            ->when($periodYear !== '', fn (Builder $query) => $query->where('period_year', (int) $periodYear))
            ->when($fundingSourceId !== '', fn (Builder $query) => $query->where('funding_source_id', $fundingSourceId));

        $headers = (clone $baseQuery)
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $summary = [
            'total_headers' => (clone $baseQuery)->count(),
            'draft_count' => RkoHeader::where('status', 'draft')->count(),
            'submitted_count' => RkoHeader::where('status', 'submitted')->count(),
            'approved_count' => RkoHeader::where('status', 'approved')->count(),
        ];

        $availableYears = RkoHeader::query()
            ->select('period_year')
            ->distinct()
            ->orderByDesc('period_year')
            ->pluck('period_year');

        $fundingSources = FundingSource::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('rko-headers.index', compact('headers', 'summary', 'search', 'status', 'periodYear', 'fundingSourceId', 'availableYears', 'fundingSources'));
    }

    public function create(): View
    {
        $rkoHeader = new RkoHeader([
            'period_month' => (int) now()->format('m'),
            'period_year' => (int) now()->format('Y'),
            'status' => 'draft',
            'submitted_at' => now()->toDateString(),
            ]);

        return view('rko-headers.create', [
            'rkoHeader' => $rkoHeader,
            'medicines' => Medicine::with(['category', 'unit'])->where('is_active', true)->orderBy('name')->get(),
            'fundingSources' => FundingSource::query()->where('is_active', true)->orderBy('name')->get(),
            'nextRkoNumber' => $this->generateNextRkoNumber(),
        ]);
    }

    public function store(RkoHeaderRequest $request): RedirectResponse
    {
        $rkoHeader = DB::transaction(function () use ($request) {
            $validated = $request->validated();
            $submittedAt = $validated['status'] === 'submitted' ? now()->toDateString() : null;

            $header = RkoHeader::create([
                'rko_number' => $validated['rko_number'],
                'period_month' => $validated['period_month'],
                'period_year' => $validated['period_year'],
                'funding_source_id' => $validated['funding_source_id'],
                'total_budget' => $validated['total_budget'],
                'status' => $validated['status'],
                'submitted_at' => $submittedAt,
                'approved_at' => null,
                'submitted_by' => $request->user()?->id,
                'approved_by' => null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $header->items()->createMany($this->normalizeItems($validated['items']));
            $header->load('items');
            $this->syncApprovedOutputs($header);

            ActivityLog::create([
                'user_id' => $request->user()?->id,
                'module' => 'rko',
                'action' => 'create',
                'description' => "Membuat RKO {$header->rko_number}.",
                'ip_address' => $request->ip(),
            ]);

            return $header;
        });

        return redirect()
            ->route('rko.header.show', $rkoHeader)
            ->with('success', 'RKO berhasil disimpan.');
    }

    public function show(RkoHeader $rkoHeader): View
    {
        $rkoHeader->load([
            'submitter',
            'approver',
            'items.medicine.category',
            'items.medicine.unit',
            'fundingSource',
            'stockMutations.items.medicine.unit',
            'procurementRealizations.medicine.unit',
            'procurementRealizations.fundingSource',
        ]);

        $linkedMutations = $rkoHeader->stockMutations()
            ->where('mutation_type', 'MASUK')
            ->with(['items.medicine.unit'])
            ->withCount('items')
            ->withSum('items', 'quantity')
            ->latest('mutation_date')
            ->latest('id')
            ->get();

        $mutationSummary = [
            'linked_count' => $linkedMutations->count(),
            'total_realized_qty' => (int) $linkedMutations->sum(fn ($mutation) => (int) ($mutation->items_sum_quantity ?? 0)),
            'total_planned_qty' => (int) $rkoHeader->items->sum('planned_quantity'),
            'total_approved_qty' => (int) $rkoHeader->items->sum(fn ($item) => (int) ($item->approved_quantity ?? 0)),
        ];

        $linkedRealizations = $rkoHeader->procurementRealizations()
            ->with(['medicine.unit', 'fundingSource'])
            ->orderBy('medicine_id')
            ->get();

        $realizationSummary = [
            'linked_count' => $linkedRealizations->count(),
            'total_quantity' => (int) $linkedRealizations->sum('realized_quantity'),
            'total_amount' => (float) $linkedRealizations->sum('total_amount'),
        ];

        return view('rko-headers.show', compact('rkoHeader', 'linkedMutations', 'mutationSummary', 'linkedRealizations', 'realizationSummary'));
    }

    public function edit(RkoHeader $rkoHeader): View|RedirectResponse
    {
        if ($rkoHeader->status === 'approved') {
            return redirect()
                ->route('rko.header.approval.edit', $rkoHeader)
                ->with('warning', 'Dokumen yang sudah disetujui dikelola melalui form persetujuan.');
        }

        $rkoHeader->load('items.medicine');

        return view('rko-headers.edit', [
            'rkoHeader' => $rkoHeader,
            'medicines' => Medicine::with(['category', 'unit'])->where('is_active', true)->orderBy('name')->get(),
            'fundingSources' => FundingSource::query()->where('is_active', true)->orderBy('name')->get(),
            'nextRkoNumber' => $rkoHeader->rko_number,
        ]);
    }

    public function update(RkoHeaderRequest $request, RkoHeader $rkoHeader): RedirectResponse
    {
        DB::transaction(function () use ($request, $rkoHeader) {
            $validated = $request->validated();
            $submittedAt = $validated['status'] === 'submitted'
                ? ($rkoHeader->submitted_at?->toDateString() ?? now()->toDateString())
                : null;

            $rkoHeader->update([
                'rko_number' => $validated['rko_number'],
                'period_month' => $validated['period_month'],
                'period_year' => $validated['period_year'],
                'funding_source_id' => $validated['funding_source_id'],
                'total_budget' => $validated['total_budget'],
                'status' => $validated['status'],
                'submitted_at' => $submittedAt,
                'approved_at' => null,
                'submitted_by' => $rkoHeader->submitted_by ?? $request->user()?->id,
                'approved_by' => null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $rkoHeader->items()->delete();
            $rkoHeader->items()->createMany($this->normalizeItems($validated['items']));
            $rkoHeader->load('items');
            $this->syncApprovedOutputs($rkoHeader);

            ActivityLog::create([
                'user_id' => $request->user()?->id,
                'module' => 'rko',
                'action' => 'update',
                'description' => "Memperbarui RKO {$rkoHeader->rko_number}.",
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()
            ->route('rko.header.show', $rkoHeader)
            ->with('success', 'RKO berhasil diperbarui.');
    }

    public function editApproval(RkoHeader $rkoHeader): View
    {
        $rkoHeader->load('items.medicine.category', 'items.medicine.unit', 'fundingSource');

        return view('rko-headers.approval', [
            'rkoHeader' => $rkoHeader,
        ]);
    }

    public function updateApproval(RkoApprovalRequest $request, RkoHeader $rkoHeader): RedirectResponse
    {
        DB::transaction(function () use ($request, $rkoHeader) {
            $validated = $request->validated();
            $itemsById = collect($validated['items'])->keyBy(fn (array $item) => (int) $item['id']);

            $rkoHeader->update([
                'status' => $validated['status'],
                'approved_at' => $validated['status'] === 'approved' ? $validated['approved_at'] : null,
                'approved_by' => in_array($validated['status'], ['approved', 'rejected'], true) ? $request->user()?->id : null,
            ]);

            foreach ($rkoHeader->items as $detail) {
                $payload = $itemsById->get($detail->id, []);

                $detail->update([
                    'approved_quantity' => $validated['status'] === 'approved'
                        ? (int) ($payload['approved_quantity'] ?? 0)
                        : null,
                    'approved_unit_price' => $validated['status'] === 'approved'
                        ? (float) ($payload['approved_unit_price'] ?? 0)
                        : null,
                ]);
            }

            $rkoHeader->refresh()->load('items');
            $this->syncApprovedOutputs($rkoHeader);

            ActivityLog::create([
                'user_id' => $request->user()?->id,
                'module' => 'rko',
                'action' => 'approve',
                'description' => "Memperbarui persetujuan RKO {$rkoHeader->rko_number} menjadi {$validated['status']}.",
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()
            ->route('rko.header.show', $rkoHeader)
            ->with('success', 'Persetujuan RKO berhasil diperbarui.');
    }

    public function destroy(Request $request, RkoHeader $rkoHeader): RedirectResponse
    {
        $rkoNumber = $rkoHeader->rko_number;

        DB::transaction(function () use ($request, $rkoHeader, $rkoNumber) {
            $rkoHeader->delete();

            ActivityLog::create([
                'user_id' => $request->user()?->id,
                'module' => 'rko',
                'action' => 'delete',
                'description' => "Menghapus RKO {$rkoNumber}.",
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()
            ->route('rko.header.index')
            ->with('success', 'RKO berhasil dihapus.');
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
                'planned_quantity' => (int) $item['planned_quantity'],
                'approved_quantity' => null,
                'estimated_unit_price' => (float) ($item['estimated_unit_price'] ?? 0),
                'approved_unit_price' => null,
                'total_estimate' => (int) $item['planned_quantity'] * (float) ($item['estimated_unit_price'] ?? 0),
                'priority' => $item['priority'] ?? 'sedang',
                'notes' => $item['notes'] ?? null,
            ])
            ->values()
            ->all();
    }

    private function generateNextRkoNumber(): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $prefix = "RKO-{$year}{$month}-";

        $lastNumber = RkoHeader::query()
            ->where('rko_number', 'like', "{$prefix}%")
            ->orderByDesc('rko_number')
            ->value('rko_number');

        if (! $lastNumber) {
            return "{$prefix}0001";
        }

        $sequence = (int) substr($lastNumber, -4) + 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    private function syncApprovedOutputs(RkoHeader $header): void
    {
        $this->syncApprovedMutation($header);
        $this->syncApprovedProcurementRealizations($header);
    }

    private function syncApprovedMutation(RkoHeader $header): void
    {
        $autoMutation = $header->stockMutations()
            ->where('mutation_type', 'MASUK')
            ->where('is_auto_generated', true)
            ->first();

        $oldMedicineIds = $autoMutation?->items()->pluck('medicine_id')->all() ?? [];

        if ($header->status !== 'approved') {
            $autoMutation?->delete();
            if ($oldMedicineIds !== []) {
                app(StockMutationController::class)->syncMedicineStocks($oldMedicineIds);
            }

            return;
        }

        $items = $header->items
            ->map(fn ($item) => [
                'medicine_id' => (int) $item->medicine_id,
                'quantity' => (int) ($item->approved_quantity ?? $item->planned_quantity),
                'notes' => $item->notes,
            ])
            ->filter(fn (array $item) => $item['quantity'] > 0)
            ->values();

        if ($items->isEmpty()) {
            $autoMutation?->delete();
            if ($oldMedicineIds !== []) {
                app(StockMutationController::class)->syncMedicineStocks($oldMedicineIds);
            }

            return;
        }

        $firstMedicineId = (int) $items->first()['medicine_id'];
        $totalQuantity = (int) $items->sum('quantity');

        $payload = [
            'mutation_number' => 'AUTO-'.$header->rko_number,
            'medicine_id' => $firstMedicineId,
            'rko_header_id' => $header->id,
            'is_auto_generated' => true,
            'created_by' => $header->approved_by ?? $header->submitted_by,
            'mutation_date' => $header->approved_at?->toDateString() ?? now()->toDateString(),
            'mutation_type' => 'MASUK',
            'quantity' => $totalQuantity,
            'reference' => 'RKO Disetujui / '.$header->rko_number,
            'notes' => 'Mutasi masuk otomatis dari persetujuan RKO.',
        ];

        if ($autoMutation) {
            $autoMutation->update($payload);
            $autoMutation->items()->delete();
        } else {
            $autoMutation = StockMutation::create($payload);
        }

        $autoMutation->items()->createMany($items->all());

        app(StockMutationController::class)->syncMedicineStocks(
            array_unique([...$oldMedicineIds, ...$items->pluck('medicine_id')->all()])
        );
    }

    private function syncApprovedProcurementRealizations(RkoHeader $header): void
    {
        if ($header->status !== 'approved') {
            $header->procurementRealizations()->delete();

            return;
        }

        $items = $header->items
            ->map(fn ($item) => [
                'medicine_id' => (int) $item->medicine_id,
                'realized_quantity' => (int) ($item->approved_quantity ?? $item->planned_quantity),
                'unit_price' => (float) ($item->approved_unit_price ?? $item->estimated_unit_price ?? 0),
                'notes' => $item->notes,
            ])
            ->filter(fn (array $item) => $item['realized_quantity'] > 0)
            ->values();

        $header->procurementRealizations()->delete();

        if ($items->isEmpty() || ! $header->funding_source_id) {
            return;
        }

        $header->procurementRealizations()->createMany(
            $items->map(fn (array $item) => [
                'funding_source_id' => $header->funding_source_id,
                'medicine_id' => $item['medicine_id'],
                'period_month' => (int) $header->period_month,
                'period_year' => (int) $header->period_year,
                'realization_date' => $header->approved_at?->toDateString() ?? now()->toDateString(),
                'realized_quantity' => $item['realized_quantity'],
                'unit_price' => $item['unit_price'],
                'total_amount' => $item['realized_quantity'] * $item['unit_price'],
                'notes' => $item['notes'],
            ])->all()
        );
    }
}
