<x-app-layout>
    <x-slot name="header">Detail Penyesuaian Stok</x-slot>

    <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div>
                <p class="text-sm text-slate-500">Nomor adjustment</p>
                <h3 class="mt-2 text-2xl font-semibold text-slate-900">{{ $adjustment->adjustment_number }}</h3>
                <p class="mt-2 text-sm text-slate-500">Jenis {{ ucfirst($adjustment->adjustment_type) }}</p>
            </div>

            <dl class="mt-8 grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Tanggal adjustment</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $adjustment->adjustment_date->format('d M Y') }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Petugas</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $adjustment->creator->name }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4 md:col-span-2">
                    <dt class="text-sm text-slate-500">Catatan</dt>
                    <dd class="mt-1 text-sm leading-7 text-slate-700">{{ $adjustment->notes ?: 'Tidak ada catatan.' }}</dd>
                </div>
            </dl>
        </article>

        <aside class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">Ringkasan adjustment</p>

            <div class="mt-4 space-y-4">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Jumlah batch</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $adjustment->items->count() }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Total selisih</p>
                    @php
                        $totalDifference = (int) $adjustment->items->sum('difference_qty');
                    @endphp
                    <p class="mt-1 text-2xl font-semibold {{ $totalDifference > 0 ? 'text-emerald-700' : ($totalDifference < 0 ? 'text-rose-700' : 'text-slate-900') }}">
                        {{ number_format($totalDifference) }}
                    </p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Dibuat pada</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $adjustment->created_at->format('d M Y H:i') }}</p>
                </div>
            </div>
        </aside>
    </section>

    <section class="mt-6 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Detail Penyesuaian per Batch</h3>
            <p class="mt-1 text-sm text-slate-500">Tabel ini menunjukkan stok sistem, stok fisik aktual, dan selisih yang disimpan ke histori.</p>
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-[1180px] w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Obat</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Batch</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Expired</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Qty Sistem</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Qty Aktual</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Selisih</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Qty Batch Kini</th>
                            <th class="px-4 py-3 font-semibold">Alasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($adjustment->items as $item)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-900">{{ $item->medicine->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $item->medicine->code }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $item->batch->batch_number }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $item->batch->expired_at->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($item->system_qty) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($item->actual_qty) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap {{ $item->difference_qty > 0 ? 'text-emerald-700' : ($item->difference_qty < 0 ? 'text-rose-700' : 'text-slate-600') }}">
                                    {{ number_format($item->difference_qty) }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($item->batch->qty_remaining) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $item->reason ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-app-layout>
