<x-app-layout>
    <x-slot name="header">Penyesuaian Stok</x-slot>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <p class="text-sm text-slate-500">Catat selisih stok fisik, batch expired, atau obat rusak agar stok sistem tetap akurat.</p>
            <a href="{{ route('stock-adjustments.create') }}" class="inline-flex rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                Tambah Penyesuaian Stok
            </a>
        </div>

        <form method="GET" action="{{ route('stock-adjustments.index') }}" class="mt-6 flex flex-col gap-3 xl:flex-row xl:items-center">
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Cari nomor adjustment atau catatan..."
                class="w-full min-w-0 flex-1 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
            >
            <select name="type" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500 xl:w-48 xl:shrink-0">
                <option value="">Semua jenis</option>
                <option value="opname" @selected($type === 'opname')>Opname</option>
                <option value="koreksi" @selected($type === 'koreksi')>Koreksi</option>
                <option value="expired" @selected($type === 'expired')>Expired</option>
                <option value="rusak" @selected($type === 'rusak')>Rusak</option>
            </select>
            <button type="submit" class="shrink-0 rounded-2xl border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 xl:min-w-32">
                Filter
            </button>
        </form>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-[1080px] w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Nomor</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Jenis</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Batch</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Total Selisih</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Petugas</th>
                            <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($adjustments as $adjustment)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{{ $adjustment->adjustment_number }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $adjustment->adjustment_date->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    <span class="whitespace-nowrap rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                        {{ ucfirst($adjustment->adjustment_type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $adjustment->items_count }} batch</td>
                                <td class="px-4 py-3 whitespace-nowrap {{ (int) ($adjustment->items_sum_difference_qty ?? 0) > 0 ? 'text-emerald-700' : ((int) ($adjustment->items_sum_difference_qty ?? 0) < 0 ? 'text-rose-700' : 'text-slate-600') }}">
                                    {{ number_format((int) ($adjustment->items_sum_difference_qty ?? 0)) }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $adjustment->creator->name }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2 whitespace-nowrap">
                                        <a href="{{ route('stock-adjustments.show', $adjustment) }}" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">Detail</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada penyesuaian stok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $adjustments->links() }}
        </div>
    </section>
</x-app-layout>
