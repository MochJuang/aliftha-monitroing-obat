<?php

namespace App\Http\Controllers;

use App\Http\Requests\RkoHeaderRequest;
use App\Models\ActivityLog;
use App\Models\Medicine;
use App\Models\RkoHeader;
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

        $baseQuery = RkoHeader::query()
            ->with(['submitter', 'approver'])
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
            ->when($periodYear !== '', fn (Builder $query) => $query->where('period_year', (int) $periodYear));

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

        return view('rko-headers.index', compact('headers', 'summary', 'search', 'status', 'periodYear', 'availableYears'));
    }

    public function create(): View
    {
        $rkoHeader = new RkoHeader([
            'period_month' => (int) now()->format('m'),
            'period_year' => (int) now()->format('Y'),
            'status' => 'draft',
        ]);

        return view('rko-headers.create', [
            'rkoHeader' => $rkoHeader,
            'medicines' => Medicine::with(['category', 'unit'])->where('is_active', true)->orderBy('name')->get(),
            'nextRkoNumber' => $this->generateNextRkoNumber(),
        ]);
    }

    public function store(RkoHeaderRequest $request): RedirectResponse
    {
        $rkoHeader = DB::transaction(function () use ($request) {
            $validated = $request->validated();

            $header = RkoHeader::create([
                'rko_number' => $validated['rko_number'],
                'period_month' => $validated['period_month'],
                'period_year' => $validated['period_year'],
                'status' => $validated['status'],
                'submitted_by' => $request->user()?->id,
                'approved_by' => $validated['status'] === 'approved' ? $request->user()?->id : null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $header->items()->createMany($this->normalizeItems($validated['items']));

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
            'stockReceipts.source',
            'stockReceipts.receiver',
        ]);

        $linkedReceipts = $rkoHeader->stockReceipts()
            ->with(['source', 'receiver'])
            ->withCount('items')
            ->withSum('items', 'quantity')
            ->latest('received_date')
            ->latest('id')
            ->get();

        $receiptSummary = [
            'linked_count' => $linkedReceipts->count(),
            'posted_count' => $linkedReceipts->where('status', 'posted')->count(),
            'total_realized_qty' => (int) $linkedReceipts->sum(fn ($receipt) => (int) ($receipt->items_sum_quantity ?? 0)),
            'total_planned_qty' => (int) $rkoHeader->items->sum('planned_quantity'),
            'total_approved_qty' => (int) $rkoHeader->items->sum(fn ($item) => (int) ($item->approved_quantity ?? 0)),
        ];

        return view('rko-headers.show', compact('rkoHeader', 'linkedReceipts', 'receiptSummary'));
    }

    public function edit(RkoHeader $rkoHeader): View
    {
        $rkoHeader->load('items.medicine');

        return view('rko-headers.edit', [
            'rkoHeader' => $rkoHeader,
            'medicines' => Medicine::with(['category', 'unit'])->where('is_active', true)->orderBy('name')->get(),
            'nextRkoNumber' => $rkoHeader->rko_number,
        ]);
    }

    public function update(RkoHeaderRequest $request, RkoHeader $rkoHeader): RedirectResponse
    {
        DB::transaction(function () use ($request, $rkoHeader) {
            $validated = $request->validated();

            $rkoHeader->update([
                'rko_number' => $validated['rko_number'],
                'period_month' => $validated['period_month'],
                'period_year' => $validated['period_year'],
                'status' => $validated['status'],
                'submitted_by' => $rkoHeader->submitted_by ?? $request->user()?->id,
                'approved_by' => $validated['status'] === 'approved' ? $request->user()?->id : null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $rkoHeader->items()->delete();
            $rkoHeader->items()->createMany($this->normalizeItems($validated['items']));

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
                'approved_quantity' => $item['approved_quantity'] !== null && $item['approved_quantity'] !== '' ? (int) $item['approved_quantity'] : null,
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
}
