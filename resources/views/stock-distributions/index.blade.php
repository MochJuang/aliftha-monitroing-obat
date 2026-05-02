<x-app-layout>
    <x-slot name="header">Distribusi Obat</x-slot>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <p class="text-sm text-slate-500">Catat distribusi obat ke puskesmas, klinik, atau bidan dengan alokasi batch FEFO yang otomatis.</p>
            <a href="{{ route('stock-distributions.create') }}" class="inline-flex rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                Tambah Distribusi Obat
            </a>
        </div>

        <form method="GET" action="{{ route('stock-distributions.index') }}" class="mt-6 flex flex-col gap-3 xl:flex-row xl:items-center">
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Cari nomor distribusi atau tujuan..."
                class="w-full min-w-0 flex-1 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
            >
            <select name="status" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500 xl:w-48 xl:shrink-0">
                <option value="">Semua status</option>
                <option value="draft" @selected($status === 'draft')>Draft</option>
                <option value="posted" @selected($status === 'posted')>Posted</option>
                <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
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
                            <th class="px-4 py-3 font-semibold">Tujuan</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Item Batch</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Total Qty</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Petugas</th>
                            <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($distributions as $distribution)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{{ $distribution->distribution_number }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $distribution->distributed_date->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-900">{{ $distribution->destination->name }}</p>
                                    <p class="text-xs text-slate-500">{{ strtoupper($distribution->destination->destination_type) }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $distribution->items_count }} alokasi</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format((int) ($distribution->items_sum_quantity ?? 0)) }}</td>
                                <td class="px-4 py-3">
                                    <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold {{ $distribution->status === 'posted' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ ucfirst($distribution->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $distribution->distributor->name }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2 whitespace-nowrap">
                                        <a href="{{ route('stock-distributions.show', $distribution) }}" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">Detail</a>

                                        @if ($distribution->status === 'draft')
                                            <a href="{{ route('stock-distributions.edit', $distribution) }}" class="rounded-xl border border-amber-300 px-3 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-50">Edit</a>
                                            <form method="POST" action="{{ route('stock-distributions.destroy', $distribution) }}" onsubmit="return confirm('Hapus transaksi draft ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-xl border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-slate-500">Belum ada transaksi stok keluar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $distributions->links() }}
        </div>
    </section>
</x-app-layout>
