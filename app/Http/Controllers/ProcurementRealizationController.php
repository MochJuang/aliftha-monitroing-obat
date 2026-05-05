<?php

namespace App\Http\Controllers;

use App\Models\FundingSource;
use App\Models\ProcurementRealization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProcurementRealizationController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $fundingSourceId = trim((string) $request->string('funding_source_id'));
        $periodYear = trim((string) $request->string('period_year'));

        $query = ProcurementRealization::query()
            ->with(['rkoHeader', 'fundingSource', 'medicine.unit'])
            ->when($search !== '', function (Builder $builder) use ($search) {
                $builder->where(function (Builder $inner) use ($search) {
                    $inner->whereHas('rkoHeader', fn (Builder $rko) => $rko->where('rko_number', 'like', "%{$search}%"))
                        ->orWhereHas('medicine', function (Builder $medicine) use ($search) {
                            $medicine->where('code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('fundingSource', fn (Builder $source) => $source->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($fundingSourceId !== '', fn (Builder $builder) => $builder->where('funding_source_id', $fundingSourceId))
            ->when($periodYear !== '', fn (Builder $builder) => $builder->where('period_year', (int) $periodYear));

        $realizations = (clone $query)
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->orderByDesc('realization_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $summary = [
            'total_rows' => (clone $query)->count(),
            'total_quantity' => (int) (clone $query)->sum('realized_quantity'),
            'total_amount' => (float) (clone $query)->sum('total_amount'),
            'total_rko' => (clone $query)->distinct('rko_header_id')->count('rko_header_id'),
        ];

        $fundingSources = FundingSource::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $availableYears = DB::table('procurement_realizations')
            ->select('period_year')
            ->distinct()
            ->orderByDesc('period_year')
            ->pluck('period_year');

        return view('procurement-realizations.index', compact(
            'realizations',
            'summary',
            'fundingSources',
            'availableYears',
            'search',
            'fundingSourceId',
            'periodYear'
        ));
    }
}
