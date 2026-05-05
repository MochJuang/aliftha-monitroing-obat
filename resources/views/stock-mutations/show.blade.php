<x-app-layout>
    <x-slot name="header">Detail Mutasi Stok</x-slot>

    <section class="grid gap-6 xl:grid-cols-[360px_minmax(0,1fr)]">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Transaksi</p>
            <h3 class="mt-3 text-2xl font-semibold text-slate-900">{{ $mutation->mutation_number }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ $mutation->mutation_date->format('d F Y') }}</p>

            <div class="mt-6 space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Jumlah</p>
                    <p class="mt-1 text-2xl font-semibold {{ $mutation->mutation_type === 'MASUK' ? 'text-emerald-700' : 'text-rose-700' }}">{{ number_format((int) $mutation->items->sum('quantity')) }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Referensi</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $mutation->reference ?: '-' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Relasi RKO</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $mutation->rkoHeader?->rko_number ?? '-' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Tujuan distribusi</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $mutation->destination?->name ?? '-' }}</p>
                </div>
            </div>
        </article>

        <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <h4 class="text-lg font-semibold text-slate-900">Detail Item Mutasi</h4>

            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-[760px] w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Kode</th>
                                <th class="px-4 py-3 font-semibold">Obat</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Kategori</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Satuan</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Jumlah</th>
                                <th class="px-4 py-3 font-semibold">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($mutation->items as $item)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{{ $item->medicine->code }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $item->medicine->name }}</td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $item->medicine->category?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $item->medicine->unit?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 font-medium whitespace-nowrap {{ $mutation->mutation_type === 'MASUK' ? 'text-emerald-700' : 'text-rose-700' }}">{{ number_format($item->quantity) }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $item->notes ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                <p class="text-sm text-slate-500">Keterangan</p>
                <p class="mt-1 leading-7 text-slate-700">{{ $mutation->notes ?: 'Tidak ada keterangan tambahan.' }}</p>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('transaksi.mutasi.edit', $mutation) }}" class="rounded-2xl border border-amber-300 px-5 py-2 text-sm font-medium text-amber-700 hover:bg-amber-50">Edit</a>
                <a href="{{ route('transaksi.mutasi.index') }}" class="rounded-2xl border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Kembali</a>
            </div>
        </article>
    </section>
</x-app-layout>
