<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicineRequest;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\Unit;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicineController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $status = (string) $request->string('status');

        $medicines = Medicine::query()
            ->with(['category', 'unit'])
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

        return view('medicines.index', compact('medicines', 'search', 'status'));
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
        $medicine = Medicine::create($request->validated());

        return redirect()
            ->route('medicines.show', $medicine)
            ->with('success', 'Data obat berhasil ditambahkan.');
    }

    public function show(Medicine $medicine): View
    {
        $medicine->load(['category', 'unit'])->loadCount('batches');

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
            ->route('medicines.show', $medicine)
            ->with('success', 'Data obat berhasil diperbarui.');
    }

    public function destroy(Medicine $medicine): RedirectResponse
    {
        try {
            $medicine->delete();

            return redirect()
                ->route('medicines.index')
                ->with('success', 'Data obat berhasil dihapus.');
        } catch (QueryException) {
            return back()->withErrors([
                'delete' => 'Data obat tidak bisa dihapus karena masih dipakai transaksi atau batch.',
            ]);
        }
    }
}
