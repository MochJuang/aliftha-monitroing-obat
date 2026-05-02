<x-app-layout>
    <x-slot name="header">Batch & Kedaluwarsa</x-slot>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Batch aktif</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($summary['total_batches']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <p class="text-sm text-rose-800">Batch expired</p>
            <p class="mt-2 text-3xl font-semibold text-rose-900">{{ number_format($summary['expired_batches']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <p class="text-sm text-amber-800">Hampir expired</p>
            <p class="mt-2 text-3xl font-semibold text-amber-900">{{ number_format($summary['almost_expired_batches']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
            <p class="text-sm text-emerald-800">Batch aman</p>
            <p class="mt-2 text-3xl font-semibold text-emerald-900">{{ number_format($summary['safe_batches']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Qty expired</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($summary['expired_stock_qty']) }}</p>
        </article>
    </section>

    <section class="mt-6 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Monitoring Batch Obat</h3>
                <p class="mt-1 text-sm text-slate-500">Pantau batch aktif per obat, lihat sumber penerimaan, sisa stok, dan prioritas distribusi berdasarkan tanggal expired.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('monitoring.batch.index') }}" class="mt-6 flex flex-col gap-3 xl:flex-row xl:items-center">
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Cari batch, obat, atau sumber obat..."
                class="w-full min-w-0 flex-1 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
            >
            <select name="category_id" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500 xl:w-56 xl:shrink-0">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($categoryId === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="status" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500 xl:w-56 xl:shrink-0">
                <option value="">Semua status</option>
                <option value="expired" @selected($status === 'expired')>Expired</option>
                <option value="almost_expired" @selected($status === 'almost_expired')>Hampir expired</option>
                <option value="safe" @selected($status === 'safe')>Aman</option>
            </select>
            <button type="submit" class="shrink-0 rounded-2xl border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 xl:min-w-32">
                Filter
            </button>
        </form>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-[1380px] w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Batch</th>
                            <th class="px-4 py-3 font-semibold">Obat</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Kategori</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Sumber</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Tgl Terima</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Expired</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Qty Diterima</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Qty Sisa</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($batches as $batch)
                            @php
                                $isExpired = $batch->expired_at->isPast() && ! $batch->expired_at->isToday();
                                $isAlmostExpired = ! $isExpired && $batch->expired_at->between(now()->startOfDay(), now()->addDays(30)->endOfDay());
                            @endphp
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{{ $batch->batch_number }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-900">{{ $batch->medicine->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $batch->medicine->code }}{{ $batch->medicine->brand ? ' | '.$batch->medicine->brand : '' }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $batch->medicine->category?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $batch->receiptItem?->stockReceipt?->source?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                    {{ $batch->receiptItem?->stockReceipt?->received_date?->format('d M Y') ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $batch->expired_at->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($batch->qty_received) }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-900 whitespace-nowrap">
                                    {{ number_format($batch->qty_remaining) }} {{ $batch->medicine->unit?->name ?? '' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($isExpired)
                                        <span class="whitespace-nowrap rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800">Expired</span>
                                    @elseif ($isAlmostExpired)
                                        <span class="whitespace-nowrap rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Hampir Expired</span>
                                    @else
                                        <span class="whitespace-nowrap rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Aman</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-slate-500">Belum ada batch aktif untuk ditampilkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $batches->links() }}
        </div>
    </section>
</x-app-layout>
