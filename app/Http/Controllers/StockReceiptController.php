<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockReceiptRequest;
use App\Models\Medicine;
use App\Models\RkoHeader;
use App\Models\StockReceipt;
use App\Models\StockSource;
use App\Services\StockReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class StockReceiptController extends Controller
{
    public function __construct(
        private readonly StockReceiptService $stockReceiptService
    ) {
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $status = trim((string) $request->string('status'));
        $rkoHeaderId = trim((string) $request->string('rko_header_id'));

        $receipts = StockReceipt::query()
            ->with(['source', 'receiver', 'rkoHeader'])
            ->withCount('items')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('receipt_number', 'like', "%{$search}%")
                        ->orWhereHas('source', fn ($sourceQuery) => $sourceQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('rkoHeader', fn ($rkoQuery) => $rkoQuery->where('rko_number', 'like', "%{$search}%"));
                });
            })
            ->when(in_array($status, ['draft', 'posted', 'cancelled'], true), fn ($query) => $query->where('status', $status))
            ->when($rkoHeaderId !== '', fn ($query) => $query->where('rko_header_id', $rkoHeaderId))
            ->latest('received_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $rkoHeaders = RkoHeader::query()
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->get();

        return view('stock-receipts.index', compact('receipts', 'rkoHeaders', 'search', 'status', 'rkoHeaderId'));
    }

    public function create(Request $request): View
    {
        $receipt = new StockReceipt([
            'rko_header_id' => $request->integer('rko_header_id') ?: null,
            'received_date' => now()->toDateString(),
            'status' => 'draft',
        ]);

        return view('stock-receipts.create', [
            'receipt' => $receipt,
            'sources' => StockSource::orderBy('name')->get(),
            'rkoHeaders' => RkoHeader::query()->orderByDesc('period_year')->orderByDesc('period_month')->get(),
            'medicines' => Medicine::where('is_active', true)->orderBy('name')->get(),
            'nextReceiptNumber' => $this->generateNextReceiptNumber(),
        ]);
    }

    public function store(StockReceiptRequest $request): RedirectResponse
    {
        $receipt = $this->stockReceiptService->store(
            $request->validated(),
            (int) $request->user()->id,
            $request->ip()
        );

        return redirect()
            ->route('pengadaan.show', $receipt)
            ->with('success', 'Realisasi pengadaan berhasil disimpan.');
    }

    public function show(StockReceipt $stockReceipt): View
    {
        $stockReceipt->load(['source', 'receiver', 'rkoHeader', 'items.medicine', 'items.batch']);

        return view('stock-receipts.show', ['receipt' => $stockReceipt]);
    }

    public function edit(StockReceipt $stockReceipt): View
    {
        if ($stockReceipt->status !== 'draft') {
            abort(403, 'Hanya transaksi draft yang bisa diedit.');
        }

        $stockReceipt->load('items.medicine');

        return view('stock-receipts.edit', [
            'receipt' => $stockReceipt,
            'sources' => StockSource::orderBy('name')->get(),
            'rkoHeaders' => RkoHeader::query()->orderByDesc('period_year')->orderByDesc('period_month')->get(),
            'medicines' => Medicine::where('is_active', true)->orderBy('name')->get(),
            'nextReceiptNumber' => $stockReceipt->receipt_number,
        ]);
    }

    public function update(StockReceiptRequest $request, StockReceipt $stockReceipt): RedirectResponse
    {
        try {
            $receipt = $this->stockReceiptService->update(
                $stockReceipt,
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
            ->route('pengadaan.show', $receipt)
            ->with('success', 'Realisasi pengadaan berhasil diperbarui.');
    }

    public function destroy(Request $request, StockReceipt $stockReceipt): RedirectResponse
    {
        try {
            $this->stockReceiptService->deleteDraft(
                $stockReceipt,
                (int) $request->user()->id,
                $request->ip()
            );
        } catch (RuntimeException $exception) {
            return back()->withErrors([
                'delete' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('pengadaan.index')
            ->with('success', 'Draft realisasi pengadaan berhasil dihapus.');
    }

    private function generateNextReceiptNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "RCV-{$year}-";

        $lastNumber = StockReceipt::query()
            ->where('receipt_number', 'like', "{$prefix}%")
            ->orderByDesc('receipt_number')
            ->value('receipt_number');

        if (! $lastNumber) {
            return "{$prefix}0001";
        }

        $sequence = (int) substr($lastNumber, -4) + 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
