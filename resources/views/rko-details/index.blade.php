<x-app-layout>
    <x-slot name="header">RKO Detail</x-slot>

    <section class="grid gap-4 md:grid-cols-3">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total item RKO</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($summary['total_items']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-sky-200 bg-sky-50 p-5 shadow-sm">
            <p class="text-sm text-sky-800">Total rencana</p>
            <p class="mt-2 text-3xl font-semibold text-sky-900">{{ number_format($summary['total_planned_quantity']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
            <p class="text-sm text-emerald-800">Total disetujui</p>
            <p class="mt-2 text-3xl font-semibold text-emerald-900">{{ number_format($summary['total_approved_quantity']) }}</p>
        </article>
    </section>

    <section class="mt-6 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Daftar Item Kebutuhan Obat</h3>
            <p class="mt-1 text-sm text-slate-500">Lihat semua detail item RKO lintas dokumen, filter per header, obat, atau tahun periode.</p>
        </div>

        <form method="GET" action="{{ route('rko.detail.index') }}" class="mt-6 grid gap-3 xl:grid-cols-[minmax(0,2fr)_minmax(0,1.4fr)_minmax(0,1.6fr)_140px_140px] xl:items-end">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor RKO, kode obat, nama obat..." class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
            <select name="header_id" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <option value="">Semua RKO</option>
                @foreach ($headers as $header)
                    <option value="{{ $header->id }}" @selected($headerId === (string) $header->id)>{{ $header->rko_number }} - {{ sprintf('%02d', $header->period_month) }}/{{ $header->period_year }}</option>
                @endforeach
            </select>
            <select name="medicine_id" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <option value="">Semua obat</option>
                @foreach ($medicines as $medicine)
                    <option value="{{ $medicine->id }}" @selected($medicineId === (string) $medicine->id)>{{ $medicine->code }} - {{ $medicine->name }}</option>
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
                <table class="min-w-[1180px] w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Nomor RKO</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Periode</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Kode</th>
                            <th class="px-4 py-3 font-semibold">Obat</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Kategori</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Rencana</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Disetujui</th>
                            <th class="px-4 py-3 font-semibold">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($details as $detail)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">
                                    <a href="{{ route('rko.header.show', $detail->header) }}" class="hover:text-amber-700">
                                        {{ $detail->header->rko_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ sprintf('%02d', $detail->header->period_month) }}/{{ $detail->header->period_year }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $detail->medicine->code }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-900">{{ $detail->medicine->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $detail->medicine->unit?->name ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $detail->medicine->category?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($detail->planned_quantity) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $detail->approved_quantity !== null ? number_format($detail->approved_quantity) : '-' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $detail->notes ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-8 text-center text-slate-500">Belum ada detail RKO.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $details->links() }}</div>
    </section>
</x-app-layout>
