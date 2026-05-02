<x-app-layout>
    <x-slot name="header">Detail Distribusi Obat</x-slot>

    <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm text-slate-500">Nomor distribusi</p>
                    <h3 class="mt-2 text-2xl font-semibold text-slate-900">{{ $distribution->distribution_number }}</h3>
                    <p class="mt-2 text-sm text-slate-500">{{ $distribution->destination->name }}</p>
                </div>

                @if ($distribution->status === 'draft')
                    <a href="{{ route('faskes.distribusi.edit', $distribution) }}" class="rounded-2xl border border-amber-300 px-4 py-2 text-sm font-medium text-amber-700 hover:bg-amber-50">
                        Edit Draft
                    </a>
                @endif
            </div>

            <dl class="mt-8 grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Tanggal distribusi</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $distribution->distributed_date->format('d M Y') }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Status</dt>
                    <dd class="mt-1 font-semibold {{ $distribution->status === 'posted' ? 'text-emerald-700' : 'text-amber-700' }}">{{ ucfirst($distribution->status) }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Tujuan distribusi</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $distribution->destination->name }}</dd>
                    <p class="mt-1 text-xs text-slate-500">{{ $distribution->destination->destination_type }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Petugas distribusi</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $distribution->distributor->name }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4 md:col-span-2">
                    <dt class="text-sm text-slate-500">Catatan</dt>
                    <dd class="mt-1 text-sm leading-7 text-slate-700">{{ $distribution->notes ?: 'Tidak ada catatan.' }}</dd>
                </div>
            </dl>
        </article>

        <aside class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">Ringkasan transaksi</p>

            <div class="mt-4 space-y-4">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Jumlah alokasi batch</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $distribution->items->count() }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Total quantity</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format((int) $distribution->items->sum('quantity')) }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Dibuat pada</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $distribution->created_at->format('d M Y H:i') }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Terakhir diperbarui</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $distribution->updated_at->format('d M Y H:i') }}</p>
                </div>
            </div>
        </aside>
    </section>

    <section class="mt-6 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Detail Alokasi Batch</h3>
                <p class="mt-1 text-sm text-slate-500">Satu obat bisa dipecah ke beberapa batch sesuai urutan FEFO.</p>
            </div>
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-[1100px] w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Obat</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Batch</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Expired</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Qty Keluar</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Sisa Batch</th>
                            <th class="px-4 py-3 font-semibold">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($distribution->items as $item)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-900">{{ $item->medicine->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $item->medicine->code }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $item->batch->batch_number }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $item->batch->expired_at->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($item->quantity) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($item->batch->qty_remaining) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $item->notes ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-app-layout>
