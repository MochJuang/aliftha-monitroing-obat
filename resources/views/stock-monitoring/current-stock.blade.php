<x-app-layout>
    <x-slot name="header">Stok Terkini</x-slot>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Obat aktif</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($summary['total_medicines']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total stok berjalan</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($summary['total_current_stock']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <p class="text-sm text-amber-800">Stok menipis</p>
            <p class="mt-2 text-3xl font-semibold text-amber-900">{{ number_format($summary['low_stock_count']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <p class="text-sm text-rose-800">Stok habis</p>
            <p class="mt-2 text-3xl font-semibold text-rose-900">{{ number_format($summary['empty_stock_count']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-sky-200 bg-sky-50 p-5 shadow-sm">
            <p class="text-sm text-sky-800">Batch hampir expired</p>
            <p class="mt-2 text-3xl font-semibold text-sky-900">{{ number_format($summary['almost_expired_batch_count']) }}</p>
        </article>
    </section>

    <section class="mt-6 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Monitoring Stok Per Obat</h3>
                <p class="mt-1 text-sm text-slate-500">Lihat stok aktif non-expired, batch berjalan, batas minimum, dan indikasi risiko stok.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('stock-monitoring.current-stock') }}" class="mt-6 flex flex-col gap-3 xl:flex-row xl:items-center">
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Cari kode, nama, atau brand obat..."
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
                <option value="safe" @selected($status === 'safe')>Aman</option>
                <option value="low" @selected($status === 'low')>Stok menipis</option>
                <option value="empty" @selected($status === 'empty')>Stok habis</option>
                <option value="almost_expired" @selected($status === 'almost_expired')>Hampir expired</option>
            </select>
            <button type="submit" class="shrink-0 rounded-2xl border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 xl:min-w-32">
                Filter
            </button>
        </form>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-[1280px] w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Kode</th>
                            <th class="px-4 py-3 font-semibold">Obat</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Kategori</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Satuan</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Stok Saat Ini</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Minimum</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Batch Aktif</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Expired Terdekat</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($medicines as $medicine)
                            @php
                                $currentStock = (int) ($medicine->current_stock ?? 0);
                                $minimumStock = (int) $medicine->minimum_stock;
                                $isEmpty = $currentStock === 0;
                                $isLow = $currentStock > 0 && $currentStock <= $minimumStock;
                                $hasAlmostExpired = (int) ($medicine->almost_expired_batch_count ?? 0) > 0;
                            @endphp
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{{ $medicine->code }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-900">{{ $medicine->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $medicine->brand ?: '-' }}{{ $medicine->dosage ? ' | '.$medicine->dosage : '' }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $medicine->category_name ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $medicine->unit_name ?? '-' }}</td>
                                <td class="px-4 py-3 font-semibold whitespace-nowrap {{ $isEmpty ? 'text-rose-700' : ($isLow ? 'text-amber-700' : 'text-slate-900') }}">
                                    {{ number_format($currentStock) }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($minimumStock) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format((int) ($medicine->active_batch_count ?? 0)) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                    {{ $medicine->nearest_expired_at ? \Illuminate\Support\Carbon::parse($medicine->nearest_expired_at)->format('d M Y') : '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        @if ($isEmpty)
                                            <span class="whitespace-nowrap rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800">Stok Habis</span>
                                        @elseif ($isLow)
                                            <span class="whitespace-nowrap rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Menipis</span>
                                        @else
                                            <span class="whitespace-nowrap rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Aman</span>
                                        @endif

                                        @if ($hasAlmostExpired)
                                            <span class="whitespace-nowrap rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-800">Hampir Expired</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-slate-500">Belum ada data stok untuk ditampilkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $medicines->links() }}
        </div>
    </section>
</x-app-layout>
