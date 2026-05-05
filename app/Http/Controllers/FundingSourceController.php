<?php

namespace App\Http\Controllers;

use App\Http\Requests\FundingSourceRequest;
use App\Models\FundingSource;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FundingSourceController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $status = trim((string) $request->string('status'));

        $fundingSources = FundingSource::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('source_type', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['active', 'inactive'], true), function ($query) use ($status) {
                $query->where('is_active', $status === 'active');
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('funding-sources.index', compact('fundingSources', 'search', 'status'));
    }

    public function create(): View
    {
        return view('funding-sources.create', [
            'fundingSource' => new FundingSource(['is_active' => true]),
        ]);
    }

    public function store(FundingSourceRequest $request): RedirectResponse
    {
        $fundingSource = FundingSource::create($request->validated());

        return redirect()
            ->route('rko.sumber-dana.show', $fundingSource)
            ->with('success', 'Sumber dana berhasil ditambahkan.');
    }

    public function show(FundingSource $fundingSource): View
    {
        $fundingSource->loadCount(['rkoHeaders', 'procurementRealizations']);

        return view('funding-sources.show', compact('fundingSource'));
    }

    public function edit(FundingSource $fundingSource): View
    {
        return view('funding-sources.edit', compact('fundingSource'));
    }

    public function update(FundingSourceRequest $request, FundingSource $fundingSource): RedirectResponse
    {
        $fundingSource->update($request->validated());

        return redirect()
            ->route('rko.sumber-dana.show', $fundingSource)
            ->with('success', 'Sumber dana berhasil diperbarui.');
    }

    public function destroy(FundingSource $fundingSource): RedirectResponse
    {
        try {
            $fundingSource->delete();

            return redirect()
                ->route('rko.sumber-dana.index')
                ->with('success', 'Sumber dana berhasil dihapus.');
        } catch (QueryException) {
            return back()->withErrors([
                'delete' => 'Sumber dana tidak bisa dihapus karena masih dipakai pada RKO atau realisasi pengadaan.',
            ]);
        }
    }
}
