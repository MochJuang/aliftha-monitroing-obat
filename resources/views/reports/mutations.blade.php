<x-app-layout>
    <x-slot name="header">Laporan Mutasi Stok</x-slot>

    @include('reports._tabs')

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Jumlah transaksi</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($summary['transaction_count']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
            <p class="text-sm text-emerald-800">Total masuk</p>
            <p class="mt-2 text-3xl font-semibold text-emerald-900">{{ number_format($summary['total_in']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <p class="text-sm text-rose-800">Total keluar</p>
            <p class="mt-2 text-3xl font-semibold text-rose-900">{{ number_format($summary['total_out']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-sky-200 bg-sky-50 p-5 shadow-sm">
            <p class="text-sm text-sky-800">Mutasi bersih</p>
            <p class="mt-2 text-3xl font-semibold text-sky-900">{{ number_format($summary['net_mutation']) }}</p>
        </article>
    </section>

    <section class="mt-6 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
	        <div class="overflow-x-auto">
	            <form method="GET" action="{{ route('laporan.mutasi') }}" class="flex flex-nowrap items-end gap-3 min-w-max">
	                <input type="text" name="search" value="{{ $search }}" placeholder="Cari obat, referensi, atau catatan..." class="min-w-[260px] flex-1 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
	                <select name="medicine_id" class="w-64 shrink-0 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
	                <option value="">Semua obat</option>
	                @foreach ($medicines as $medicine)
	                    <option value="{{ $medicine->id }}" @selected($medicineId === (string) $medicine->id)>{{ $medicine->code }} - {{ $medicine->name }}</option>
	                @endforeach
	                </select>
	                <select name="type" class="w-40 shrink-0 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
	                <option value="">Semua jenis</option>
	                <option value="MASUK" @selected($type === 'MASUK')>MASUK</option>
	                <option value="KELUAR" @selected($type === 'KELUAR')>KELUAR</option>
	                </select>
	                <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-40 shrink-0 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
	                <input type="date" name="date_to" value="{{ $dateTo }}" class="w-40 shrink-0 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
	                <button type="submit" class="w-28 shrink-0 rounded-2xl border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Filter</button>
	            </form>
	        </div>
            @include('reports._export_buttons', ['routeName' => 'laporan.mutasi.export'])

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-[1020px] w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Obat</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Jenis</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Jumlah</th>
                            <th class="px-4 py-3 font-semibold">Referensi</th>
                            <th class="px-4 py-3 font-semibold">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($reports as $report)
                            <tr>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $report->mutation_date->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-900">{{ $report->medicine->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $report->medicine->code }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $report->mutation_type === 'MASUK' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $report->mutation_type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap {{ $report->mutation_type === 'MASUK' ? 'text-emerald-700' : 'text-rose-700' }}">
                                    {{ number_format($report->quantity) }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $report->reference ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $report->notes ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada data mutasi stok.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $reports->links() }}</div>
    </section>
</x-app-layout>
