<x-app-layout>
    <x-slot name="header">Mutasi Stok</x-slot>

    <section class="grid gap-4 md:grid-cols-3">
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
    </section>

    <section class="mt-6 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <p class="text-sm text-slate-500">Transaksi manual pada halaman ini hanya untuk mutasi keluar. Mutasi masuk dibentuk otomatis dari persetujuan RKO.</p>
            @can('manage-stock-mutations')
                <a href="{{ route('transaksi.mutasi.create') }}" class="inline-flex rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    Tambah Mutasi Keluar
                </a>
            @endcan
        </div>

	        <div class="mt-6 overflow-x-auto">
	            <form method="GET" action="{{ route('transaksi.mutasi.index') }}" class="flex flex-nowrap items-end gap-3 min-w-max">
	                <input type="text" name="search" value="{{ $search }}" placeholder="Cari obat, referensi, atau catatan..." class="min-w-[260px] flex-1 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
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

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-[980px] w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Nomor</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Jenis</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Jumlah Item</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Total Qty</th>
                            <th class="px-4 py-3 font-semibold">Tujuan Distribusi</th>
                            <th class="px-4 py-3 font-semibold">Referensi</th>
                            <th class="px-4 py-3 font-semibold">Keterangan</th>
                            <th class="px-4 py-3 font-semibold text-right whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($mutations as $mutation)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{{ $mutation->mutation_number }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $mutation->mutation_date->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $mutation->mutation_type === 'MASUK' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $mutation->mutation_type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($mutation->items_count) }} item</td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap {{ $mutation->mutation_type === 'MASUK' ? 'text-emerald-700' : 'text-rose-700' }}">{{ number_format((int) ($mutation->items_sum_quantity ?? 0)) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $mutation->destination?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $mutation->reference ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $mutation->notes ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2 whitespace-nowrap">
                                        <a href="{{ route('transaksi.mutasi.show', $mutation) }}" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">Detail</a>
                                        @can('manage-stock-mutations')
                                            @if (! $mutation->is_auto_generated && $mutation->mutation_type === 'KELUAR')
                                                <a href="{{ route('transaksi.mutasi.edit', $mutation) }}" class="rounded-xl border border-amber-300 px-3 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-50">Edit</a>
                                                <form method="POST" action="{{ route('transaksi.mutasi.destroy', $mutation) }}" onsubmit="return confirm('Hapus mutasi ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-xl border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50">Hapus</button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-4 py-8 text-center text-slate-500">Belum ada data mutasi stok.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $mutations->links() }}</div>
    </section>
</x-app-layout>
