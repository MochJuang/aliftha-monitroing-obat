<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockSourceRequest;
use App\Models\StockSource;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockSourceController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $type = trim((string) $request->string('type'));

        $sources = StockSource::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%");
                });
            })
            ->when($type !== '', fn ($query) => $query->where('source_type', $type))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('stock-sources.index', compact('sources', 'search', 'type'));
    }

    public function create(): View
    {
        $source = new StockSource();

        return view('stock-sources.create', compact('source'));
    }

    public function store(StockSourceRequest $request): RedirectResponse
    {
        $source = StockSource::create($request->validated());

        return redirect()
            ->route('pengadaan.sumber.show', $source)
            ->with('success', 'Sumber pengadaan berhasil ditambahkan.');
    }

    public function show(StockSource $stockSource): View
    {
        $stockSource->loadCount('stockReceipts');

        return view('stock-sources.show', ['source' => $stockSource]);
    }

    public function edit(StockSource $stockSource): View
    {
        return view('stock-sources.edit', ['source' => $stockSource]);
    }

    public function update(StockSourceRequest $request, StockSource $stockSource): RedirectResponse
    {
        $stockSource->update($request->validated());

        return redirect()
            ->route('pengadaan.sumber.show', $stockSource)
            ->with('success', 'Sumber pengadaan berhasil diperbarui.');
    }

    public function destroy(StockSource $stockSource): RedirectResponse
    {
        try {
            $stockSource->delete();

            return redirect()
                ->route('pengadaan.sumber.index')
                ->with('success', 'Sumber pengadaan berhasil dihapus.');
        } catch (QueryException) {
            return back()->withErrors([
                'delete' => 'Sumber pengadaan tidak bisa dihapus karena masih dipakai pada realisasi pengadaan.',
            ]);
        }
    }
}
