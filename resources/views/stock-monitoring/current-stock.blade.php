<x-app-layout>
    <x-slot name="header">Stok Terkini</x-slot>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total obat aktif</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($summary['total_medicines']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-sky-200 bg-sky-50 p-5 shadow-sm">
            <p class="text-sm text-sky-800">Total stok berjalan</p>
            <p class="mt-2 text-3xl font-semibold text-sky-900">{{ number_format($summary['total_current_stock']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <p class="text-sm text-amber-800">Stok menipis</p>
            <p class="mt-2 text-3xl font-semibold text-amber-900">{{ number_format($summary['low_stock_count']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <p class="text-sm text-rose-800">Stok habis</p>
            <p class="mt-2 text-3xl font-semibold text-rose-900">{{ number_format($summary['empty_stock_count']) }}</p>
        </article>
    </section>

    <section class="mt-6 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Monitoring Stok Obat</h2>
                <p class="mt-1 text-sm text-slate-500">Pantau stok terkini per obat untuk melihat kondisi aman, menipis, atau habis secara cepat.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('monitoring.stok.index') }}" class="mt-6 grid gap-3 xl:grid-cols-[minmax(0,2fr)_220px_180px_140px] xl:items-end">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode, nama, atau merek obat..." class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
            <select name="category_id" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($categoryId === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="status" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <option value="">Semua status</option>
                <option value="safe" @selected($status === 'safe')>Aman</option>
                <option value="low" @selected($status === 'low')>Menipis</option>
                <option value="empty" @selected($status === 'empty')>Habis</option>
            </select>
            <button type="submit" class="rounded-2xl border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Filter</button>
        </form>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-[980px] w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Kode</th>
                            <th class="px-4 py-3 font-semibold">Obat</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Kategori</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Satuan</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Stok Saat Ini</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Minimum</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($medicines as $medicine)
                            @php
                                $currentStock = (int) ($medicine->current_stock ?? 0);
                                $minimumStock = (int) $medicine->minimum_stock;
                                $statusLabel = $currentStock === 0
                                    ? 'Habis'
                                    : ($currentStock <= $minimumStock ? 'Menipis' : 'Aman');
                                $statusClasses = $currentStock === 0
                                    ? 'bg-rose-100 text-rose-800'
                                    : ($currentStock <= $minimumStock ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800');
                            @endphp
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{{ $medicine->code }}</td>
                                <td class="px-4 py-3 text-slate-700">
                                    <p class="font-medium text-slate-900">{{ $medicine->name }}</p>
                                    @if ($medicine->brand || $medicine->dosage)
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ collect([$medicine->brand, $medicine->dosage])->filter()->join(' · ') }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $medicine->category_name ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $medicine->unit_name ?? '-' }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-900 whitespace-nowrap">{{ number_format($currentStock) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($minimumStock) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $statusLabel }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada data stok terkini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $medicines->links() }}</div>
    </section>
</x-app-layout>
