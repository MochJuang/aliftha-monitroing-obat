<x-app-layout>
    <x-slot name="header">Laporan Realisasi Pengadaan</x-slot>

    @include('reports._tabs')

    <section class="grid gap-4 md:grid-cols-3">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Jumlah transaksi</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($summary['transaction_count']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total quantity</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($summary['total_qty']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
            <p class="text-sm text-emerald-800">Transaksi posted</p>
            <p class="mt-2 text-3xl font-semibold text-emerald-900">{{ number_format($summary['posted_count']) }}</p>
        </article>
    </section>

    <section class="mt-6 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('laporan.pengadaan') }}" class="grid gap-3 xl:grid-cols-[minmax(0,2fr)_minmax(0,1.2fr)_minmax(0,1.3fr)_160px_160px_180px_140px] xl:items-end">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor transaksi, sumber, atau RKO..." class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
            <select name="source_id" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <option value="">Semua sumber</option>
                @foreach ($sources as $source)
                    <option value="{{ $source->id }}" @selected($sourceId === (string) $source->id)>{{ $source->name }}</option>
                @endforeach
            </select>
            <select name="rko_header_id" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <option value="">Semua RKO</option>
                @foreach ($rkoHeaders as $header)
                    <option value="{{ $header->id }}" @selected($rkoHeaderId === (string) $header->id)>{{ $header->rko_number }} - {{ sprintf('%02d', $header->period_month) }}/{{ $header->period_year }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
            <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
            <select name="status" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <option value="">Semua status</option>
                <option value="draft" @selected($status === 'draft')>Draft</option>
                <option value="posted" @selected($status === 'posted')>Posted</option>
                <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
            </select>
            <button type="submit" class="rounded-2xl border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Filter</button>
        </form>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-[1100px] w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Nomor</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal</th>
                            <th class="px-4 py-3 font-semibold">Sumber</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">RKO</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Item</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Qty</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Petugas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($reports as $report)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{{ $report->receipt_number }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $report->received_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $report->source->name }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $report->rkoHeader?->rko_number ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($report->items_count) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format((int) ($report->items_sum_quantity ?? 0)) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ ucfirst($report->status) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $report->receiver->name }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-8 text-center text-slate-500">Belum ada data laporan stok masuk.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $reports->links() }}</div>
    </section>
</x-app-layout>
