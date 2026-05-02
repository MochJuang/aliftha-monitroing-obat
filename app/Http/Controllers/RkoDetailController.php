<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\RkoDetail;
use App\Models\RkoHeader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RkoDetailController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $headerId = trim((string) $request->string('header_id'));
        $medicineId = trim((string) $request->string('medicine_id'));
        $periodYear = trim((string) $request->string('period_year'));

        $details = RkoDetail::query()
            ->with(['header', 'medicine.category', 'medicine.unit'])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->whereHas('header', fn (Builder $headerQuery) => $headerQuery->where('rko_number', 'like', "%{$search}%"))
                        ->orWhereHas('medicine', function (Builder $medicineQuery) use ($search) {
                            $medicineQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        })
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->when($headerId !== '', fn (Builder $query) => $query->where('rko_header_id', (int) $headerId))
            ->when($medicineId !== '', fn (Builder $query) => $query->where('medicine_id', (int) $medicineId))
            ->when($periodYear !== '', fn (Builder $query) => $query->whereHas('header', fn (Builder $headerQuery) => $headerQuery->where('period_year', (int) $periodYear)))
            ->whereHas('header')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $summary = [
            'total_items' => RkoDetail::count(),
            'total_planned_quantity' => (int) RkoDetail::sum('planned_quantity'),
            'total_approved_quantity' => (int) RkoDetail::sum('approved_quantity'),
        ];

        $headers = RkoHeader::query()
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->get();

        $medicines = Medicine::where('is_active', true)->orderBy('name')->get();

        $availableYears = RkoHeader::query()
            ->select('period_year')
            ->distinct()
            ->orderByDesc('period_year')
            ->pluck('period_year');

        return view('rko-details.index', compact(
            'details',
            'summary',
            'headers',
            'medicines',
            'availableYears',
            'search',
            'headerId',
            'medicineId',
            'periodYear',
        ));
    }
}
