<x-app-layout>
    <x-slot name="header">Realisasi Pengadaan</x-slot>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Baris realisasi</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($summary['total_rows']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Dokumen RKO terkait</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($summary['total_rko']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-sky-200 bg-sky-50 p-5 shadow-sm">
            <p class="text-sm text-sky-800">Total kuantitas</p>
            <p class="mt-2 text-3xl font-semibold text-sky-900">{{ number_format($summary['total_quantity']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
            <p class="text-sm text-emerald-800">Total nilai</p>
            <p class="mt-2 text-3xl font-semibold text-emerald-900">Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</p>
        </article>
    </section>

    <section class="mt-6 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-2">
            <h3 class="text-lg font-semibold text-slate-900">Daftar Realisasi Pengadaan</h3>
            <p class="text-sm text-slate-500">Data ini terbentuk otomatis dari hasil persetujuan dokumen RKO berdasarkan sumber dana, obat, dan jumlah yang disetujui.</p>
        </div>

        <form method="GET" action="{{ route('rko.realisasi.index') }}" class="mt-6 grid gap-3 xl:grid-cols-[minmax(0,2fr)_220px_180px_140px] xl:items-end">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor RKO, obat, atau sumber dana..." class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
            <select name="funding_source_id" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <option value="">Semua sumber dana</option>
                @foreach ($fundingSources as $fundingSource)
                    <option value="{{ $fundingSource->id }}" @selected($fundingSourceId === (string) $fundingSource->id)>{{ $fundingSource->name }}</option>
                @endforeach
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
                <table class="min-w-[1380px] w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Nomor RKO</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Periode</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Sumber Dana</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Obat</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Qty Realisasi</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Harga Satuan</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Total Nilai</th>
                            <th class="px-4 py-3 font-semibold">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($realizations as $realization)
                            <tr>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $realization->realization_date?->format('d M Y') ?? '-' }}</td>
                                <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{{ $realization->rkoHeader?->rko_number ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ sprintf('%02d', $realization->period_month) }}/{{ $realization->period_year }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $realization->fundingSource?->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-900">{{ $realization->medicine?->name ?? '-' }}</p>
                                    <p class="text-xs text-slate-500">{{ $realization->medicine?->code ?? '-' }}{{ $realization->medicine?->unit?->name ? ' | '.$realization->medicine->unit->name : '' }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($realization->realized_quantity) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">Rp {{ number_format((float) $realization->unit_price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">Rp {{ number_format((float) $realization->total_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $realization->notes ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-4 py-8 text-center text-slate-500">Belum ada data realisasi pengadaan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $realizations->links() }}</div>
    </section>
</x-app-layout>
