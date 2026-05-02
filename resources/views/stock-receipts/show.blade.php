<x-app-layout>
    <x-slot name="header">Detail Realisasi Pengadaan</x-slot>

    <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm text-slate-500">Nomor penerimaan</p>
                    <h3 class="mt-2 text-2xl font-semibold text-slate-900">{{ $receipt->receipt_number }}</h3>
                    <p class="mt-2 text-sm text-slate-500">{{ $receipt->source->name }}</p>
                </div>

                @if ($receipt->status === 'draft')
                    <a href="{{ route('pengadaan.edit', $receipt) }}" class="rounded-2xl border border-amber-300 px-4 py-2 text-sm font-medium text-amber-700 hover:bg-amber-50">
                        Edit Draft
                    </a>
                @endif
            </div>

            <dl class="mt-8 grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Tanggal penerimaan</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $receipt->received_date->format('d M Y') }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Status</dt>
                    <dd class="mt-1 font-semibold {{ $receipt->status === 'posted' ? 'text-emerald-700' : 'text-amber-700' }}">{{ ucfirst($receipt->status) }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Petugas penerima</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $receipt->receiver->name }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Sumber</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $receipt->source->name }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4 md:col-span-2">
                    <dt class="text-sm text-slate-500">Catatan</dt>
                    <dd class="mt-1 text-sm leading-7 text-slate-700">{{ $receipt->notes ?: 'Tidak ada catatan.' }}</dd>
                </div>
            </dl>
        </article>

        <aside class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">Ringkasan transaksi</p>

            <div class="mt-4 space-y-4">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Jumlah item</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $receipt->items->count() }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Dibuat pada</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $receipt->created_at->format('d M Y H:i') }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Terakhir diperbarui</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $receipt->updated_at->format('d M Y H:i') }}</p>
                </div>
            </div>
        </aside>
    </section>

    <section class="mt-6 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Daftar Item</h3>
                <p class="mt-1 text-sm text-slate-500">Batch akan terbentuk otomatis saat transaksi berstatus posted.</p>
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
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Jumlah</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Harga Satuan</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Qty Batch</th>
                            <th class="px-4 py-3 font-semibold">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($receipt->items as $item)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-900">{{ $item->medicine->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $item->medicine->code }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $item->batch_number }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $item->expired_at->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($item->quantity) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format((float) $item->unit_cost, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                    {{ $item->batch ? number_format($item->batch->qty_remaining) : '-' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $item->notes ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-app-layout>
