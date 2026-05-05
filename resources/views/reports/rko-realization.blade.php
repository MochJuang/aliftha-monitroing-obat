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
	        <div class="overflow-x-auto">
	            <form method="GET" action="{{ route('laporan.rko') }}" class="flex flex-col gap-3 md:flex-row md:flex-nowrap md:items-end min-w-max">
	                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor RKO atau catatan..." class="w-full min-w-0 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500 md:flex-1 md:min-w-[280px]">
	                <select name="status" class="w-full min-w-0 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500 md:w-44 md:shrink-0">
	                <option value="">Semua status</option>
	                <option value="draft" @selected($status === 'draft')>Draft</option>
	                <option value="submitted" @selected($status === 'submitted')>Diajukan</option>
	                <option value="approved" @selected($status === 'approved')>Disetujui</option>
	                <option value="rejected" @selected($status === 'rejected')>Ditolak</option>
	                </select>
	                <select name="period_year" class="w-full min-w-0 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500 md:w-40 md:shrink-0">
	                <option value="">Semua tahun</option>
	                @foreach ($availableYears as $year)
	                    <option value="{{ $year }}" @selected($periodYear === (string) $year)>{{ $year }}</option>
	                @endforeach
	                </select>
	                <button type="submit" class="rounded-2xl border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 md:w-28 md:shrink-0">Filter</button>
	            </form>
	        </div>

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
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Baris Realisasi</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Penyusun</th>
                        </tr>
                    </thead>
                    @forelse ($reports as $report)
                        <tbody x-data="{ open: false }" class="divide-y divide-slate-100 bg-white">
                            @php
                                $approvedQty = (int) ($report->items_sum_approved_quantity ?? 0);
                                $realizedQty = (int) ($report->posted_realized_quantity ?? 0);
                                $difference = $realizedQty - $approvedQty;
                                $coverage = $approvedQty > 0 ? round(($realizedQty / $approvedQty) * 100, 1) : 0;
                            @endphp
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <button
                                            type="button"
                                            @click="open = !open"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-slate-300 text-slate-600 hover:bg-slate-50"
                                            :aria-expanded="open.toString()"
                                            aria-label="Toggle detail item"
                                        >
                                            <span x-show="!open">+</span>
                                            <span x-show="open">−</span>
                                        </button>
                                        <a href="{{ route('rko.header.show', $report) }}" class="hover:text-amber-700">
                                            {{ $report->rko_number }}
                                        </a>
                                    </div>
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
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format((int) ($report->realization_rows_count ?? 0)) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $report->submitter?->name ?? '-' }}</td>
                            </tr>
                            <tr x-cloak x-show="open" x-transition>
                                <td colspan="11" class="bg-slate-50 px-4 py-5">
                                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                                        <div class="border-b border-slate-200 px-4 py-3">
                                            <h4 class="font-semibold text-slate-900">Detail Item RKO</h4>
                                            <p class="mt-1 text-sm text-slate-500">Perbandingan rencana, jumlah disetujui, dan realisasi per obat.</p>
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-[980px] w-full divide-y divide-slate-200 text-sm">
                                                <thead class="bg-slate-50 text-left text-slate-500">
                                                    <tr>
                                                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Kode</th>
                                                        <th class="px-4 py-3 font-semibold">Obat</th>
                                                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Rencana</th>
                                                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Disetujui</th>
                                                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Realisasi</th>
                                                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Selisih</th>
                                                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Cakupan</th>
                                                        <th class="px-4 py-3 font-semibold">Catatan</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100 bg-white">
                                                    @forelse ($report->item_rows as $item)
                                                        <tr>
                                                            <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{{ $item['medicine_code'] }}</td>
                                                            <td class="px-4 py-3">
                                                                <p class="font-medium text-slate-900">{{ $item['medicine_name'] }}</p>
                                                                <p class="text-xs text-slate-500">{{ $item['unit_name'] ?? '-' }}</p>
                                                            </td>
                                                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($item['planned_quantity']) }}</td>
                                                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($item['approved_quantity']) }}</td>
                                                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($item['realized_quantity']) }}</td>
                                                            <td class="px-4 py-3 whitespace-nowrap {{ $item['difference_quantity'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                                                {{ number_format($item['difference_quantity']) }}
                                                            </td>
                                                            <td class="px-4 py-3 whitespace-nowrap">
                                                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                                                    {{ number_format($item['coverage_percent'], 1) }}%
                                                                </span>
                                                            </td>
                                                            <td class="px-4 py-3 text-slate-600">{{ $item['notes'] ?: '-' }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">Belum ada detail item untuk dokumen RKO ini.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @empty
                        <tbody class="bg-white">
                            <tr><td colspan="11" class="px-4 py-8 text-center text-slate-500">Belum ada data perbandingan RKO dan realisasi.</td></tr>
                        </tbody>
                    @endforelse
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $reports->links() }}</div>
    </section>
</x-app-layout>
