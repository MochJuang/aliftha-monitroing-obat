<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnitRequest;
use App\Models\Unit;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));

        $units = Unit::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('symbol', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('units.index', compact('units', 'search'));
    }

    public function create(): View
    {
        $unit = new Unit();

        return view('units.create', compact('unit'));
    }

    public function store(UnitRequest $request): RedirectResponse
    {
        $unit = Unit::create($request->validated());

        return redirect()
            ->route('units.show', $unit)
            ->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function show(Unit $unit): View
    {
        $unit->loadCount('medicines');

        return view('units.show', compact('unit'));
    }

    public function edit(Unit $unit): View
    {
        return view('units.edit', compact('unit'));
    }

    public function update(UnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit->update($request->validated());

        return redirect()
            ->route('units.show', $unit)
            ->with('success', 'Satuan berhasil diperbarui.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        try {
            $unit->delete();

            return redirect()
                ->route('units.index')
                ->with('success', 'Satuan berhasil dihapus.');
        } catch (QueryException) {
            return back()->withErrors([
                'delete' => 'Satuan tidak bisa dihapus karena masih dipakai data lain.',
            ]);
        }
    }
}
