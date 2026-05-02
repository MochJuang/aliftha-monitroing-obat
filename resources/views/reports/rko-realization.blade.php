<x-app-layout>
    <x-slot name="header">Laporan RKO vs Realisasi</x-slot>

    @include('reports._tabs')

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Dokumen RKO</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($summary['total_headers']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total rencana</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($summary['total_planned_qty']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <p class="text-sm text-amber-800">Total disetujui</p>
            <p class="mt-2 text-3xl font-semibold text-amber-900">{{ number_format($summary['total_approved_qty']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-sky-200 bg-sky-50 p-5 shadow-sm">
            <p class="text-sm text-sky-800">Total realisasi</p>
            <p class="mt-2 text-3xl font-semibold text-sky-900">{{ number_format($summary['total_realized_qty']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
            <p class="text-sm text-emerald-800">Cakupan realisasi</p>
            <p class="mt-2 text-3xl font-semibold text-emerald-900">{{ number_format($summary['coverage_percent'], 1) }}%</p>
        </article>
    </section>

    <section class="mt-6 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('laporan.rko') }}" class="grid gap-3 xl:grid-cols-[minmax(0,2fr)_220px_180px_140px] xl:items-end">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor RKO atau catatan..." class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
            <select name="status" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <option value="">Semua status</option>
                <option value="draft" @selected($status === 'draft')>Draft</option>
                <option value="submitted" @selected($status === 'submitted')>Diajukan</option>
                <option value="approved" @selected($status === 'approved')>Disetujui</option>
                <option value="rejected" @selected($status === 'rejected')>Ditolak</option>
            </select>
            <select name="period_year" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <option value="">Semua tahun</option>
                @foreach ($availableYears as $year)
                    <option value="{{ $year }}" @selected($periodYear === (string) $year)>{{ $year }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-2xl border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Filter</button>
        </form>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-[1280px] w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Nomor RKO</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Periode</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Item</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Rencana</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Disetujui</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Realisasi Posted</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Selisih ke Disetujui</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Cakupan</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Linked Pengadaan</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Penyusun</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($reports as $report)
                            @php
                                $approvedQty = (int) ($report->items_sum_approved_quantity ?? 0);
                                $realizedQty = (int) ($report->posted_realized_quantity ?? 0);
                                $difference = $realizedQty - $approvedQty;
                                $coverage = $approvedQty > 0 ? round(($realizedQty / $approvedQty) * 100, 1) : 0;
                            @endphp
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">
                                    <a href="{{ route('rko.header.show', $report) }}" class="hover:text-amber-700">
                                        {{ $report->rko_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ sprintf('%02d', $report->period_month) }}/{{ $report->period_year }}</td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold',
                                        'bg-slate-100 text-slate-700' => $report->status === 'draft',
                                        'bg-amber-100 text-amber-800' => $report->status === 'submitted',
                                        'bg-emerald-100 text-emerald-800' => $report->status === 'approved',
                                        'bg-rose-100 text-rose-800' => $report->status === 'rejected',
                                    ])>
                                        {{ match($report->status) { 'draft' => 'Draft', 'submitted' => 'Diajukan', 'approved' => 'Disetujui', default => 'Ditolak' } }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($report->items_count) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format((int) ($report->items_sum_planned_quantity ?? 0)) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($approvedQty) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($realizedQty) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap {{ $difference >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">{{ number_format($difference) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                        {{ number_format($coverage, 1) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format((int) ($report->linked_receipts_count ?? 0)) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $report->submitter?->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="px-4 py-8 text-center text-slate-500">Belum ada data perbandingan RKO dan realisasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $reports->links() }}</div>
    </section>
</x-app-layout>
