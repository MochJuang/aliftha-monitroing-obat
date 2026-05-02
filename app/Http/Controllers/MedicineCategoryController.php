<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicineCategoryRequest;
use App\Models\MedicineCategory;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicineCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));

        $categories = MedicineCategory::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('medicine-categories.index', compact('categories', 'search'));
    }

    public function create(): View
    {
        $category = new MedicineCategory();

        return view('medicine-categories.create', compact('category'));
    }

    public function store(MedicineCategoryRequest $request): RedirectResponse
    {
        $category = MedicineCategory::create($request->validated());

        return redirect()
            ->route('master-obat.kategori.show', $category)
            ->with('success', 'Kategori obat berhasil ditambahkan.');
    }

    public function show(MedicineCategory $medicineCategory): View
    {
        $medicineCategory->loadCount('medicines');

        return view('medicine-categories.show', ['category' => $medicineCategory]);
    }

    public function edit(MedicineCategory $medicineCategory): View
    {
        return view('medicine-categories.edit', ['category' => $medicineCategory]);
    }

    public function update(MedicineCategoryRequest $request, MedicineCategory $medicineCategory): RedirectResponse
    {
        $medicineCategory->update($request->validated());

        return redirect()
            ->route('master-obat.kategori.show', $medicineCategory)
            ->with('success', 'Kategori obat berhasil diperbarui.');
    }

    public function destroy(MedicineCategory $medicineCategory): RedirectResponse
    {
        try {
            $medicineCategory->delete();

            return redirect()
                ->route('master-obat.kategori.index')
                ->with('success', 'Kategori obat berhasil dihapus.');
        } catch (QueryException) {
            return back()->withErrors([
                'delete' => 'Kategori obat tidak bisa dihapus karena masih dipakai data lain.',
            ]);
        }
    }
}
