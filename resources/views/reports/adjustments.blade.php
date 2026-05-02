<x-app-layout>
    <x-slot name="header">Laporan Adjustment</x-slot>

    @include('reports._tabs')

    <section class="grid gap-4 md:grid-cols-3">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Jumlah transaksi</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($summary['transaction_count']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-sky-200 bg-sky-50 p-5 shadow-sm">
            <p class="text-sm text-sky-800">Total selisih</p>
            <p class="mt-2 text-3xl font-semibold {{ $summary['total_difference'] >= 0 ? 'text-sky-900' : 'text-rose-900' }}">{{ number_format($summary['total_difference']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <p class="text-sm text-rose-800">Adjustment expired</p>
            <p class="mt-2 text-3xl font-semibold text-rose-900">{{ number_format($summary['expired_count']) }}</p>
        </article>
    </section>

    <section class="mt-6 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('reports.adjustments') }}" class="grid gap-3 xl:grid-cols-[minmax(0,2fr)_180px_180px_180px_140px] xl:items-end">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor adjustment atau catatan..." class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
            <select name="type" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <option value="">Semua jenis</option>
                <option value="opname" @selected($type === 'opname')>Opname</option>
                <option value="koreksi" @selected($type === 'koreksi')>Koreksi</option>
                <option value="expired" @selected($type === 'expired')>Expired</option>
                <option value="rusak" @selected($type === 'rusak')>Rusak</option>
            </select>
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
            <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
            <button type="submit" class="rounded-2xl border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Filter</button>
        </form>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-[1000px] w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Nomor</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Jenis</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Batch</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Selisih</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Petugas</th>
                            <th class="px-4 py-3 font-semibold">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($reports as $report)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{{ $report->adjustment_number }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $report->adjustment_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ ucfirst($report->adjustment_type) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($report->items_count) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap {{ (int) ($report->items_sum_difference_qty ?? 0) >= 0 ? 'text-sky-700' : 'text-rose-700' }}">{{ number_format((int) ($report->items_sum_difference_qty ?? 0)) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $report->creator->name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $report->notes ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada data laporan adjustment.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $reports->links() }}</div>
    </section>
</x-app-layout>
