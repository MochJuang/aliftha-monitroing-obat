<?php

namespace App\Http\Controllers;

use App\Http\Requests\DistributionDestinationRequest;
use App\Models\DistributionDestination;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DistributionDestinationController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $type = trim((string) $request->string('type'));
        $status = trim((string) $request->string('status'));

        $destinations = DistributionDestination::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%");
                });
            })
            ->when($type !== '', fn ($query) => $query->where('destination_type', $type))
            ->when(in_array($status, ['active', 'inactive'], true), function ($query) use ($status) {
                $query->where('is_active', $status === 'active');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('distribution-destinations.index', compact('destinations', 'search', 'type', 'status'));
    }

    public function create(): View
    {
        $destination = new DistributionDestination(['is_active' => true]);

        return view('distribution-destinations.create', compact('destination'));
    }

    public function store(DistributionDestinationRequest $request): RedirectResponse
    {
        $destination = DistributionDestination::create($request->validated());

        return redirect()
            ->route('distribution-destinations.show', $destination)
            ->with('success', 'Data faskes berhasil ditambahkan.');
    }

    public function show(DistributionDestination $distributionDestination): View
    {
        $distributionDestination->loadCount('stockDistributions');

        return view('distribution-destinations.show', ['destination' => $distributionDestination]);
    }

    public function edit(DistributionDestination $distributionDestination): View
    {
        return view('distribution-destinations.edit', ['destination' => $distributionDestination]);
    }

    public function update(DistributionDestinationRequest $request, DistributionDestination $distributionDestination): RedirectResponse
    {
        $distributionDestination->update($request->validated());

        return redirect()
            ->route('distribution-destinations.show', $distributionDestination)
            ->with('success', 'Data faskes berhasil diperbarui.');
    }

    public function destroy(DistributionDestination $distributionDestination): RedirectResponse
    {
        try {
            $distributionDestination->delete();

            return redirect()
                ->route('distribution-destinations.index')
                ->with('success', 'Data faskes berhasil dihapus.');
        } catch (QueryException) {
            return back()->withErrors([
                'delete' => 'Data faskes tidak bisa dihapus karena masih dipakai pada distribusi obat.',
            ]);
        }
    }
}
