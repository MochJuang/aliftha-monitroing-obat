<x-app-layout>
    <x-slot name="header">Laporan Stok</x-slot>

    @include('reports._tabs')

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total stok berjalan</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($summary['total_stock_qty']) }}</p>
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
        <form method="GET" action="{{ route('laporan.stok') }}" class="flex flex-col gap-3 xl:flex-row xl:items-center">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode atau nama obat..." class="w-full min-w-0 flex-1 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
            <select name="category_id" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500 xl:w-56 xl:shrink-0">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($categoryId === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="status" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500 xl:w-48 xl:shrink-0">
                <option value="">Semua status</option>
                <option value="safe" @selected($status === 'safe')>Aman</option>
                <option value="low" @selected($status === 'low')>Menipis</option>
                <option value="empty" @selected($status === 'empty')>Habis</option>
            </select>
            <button type="submit" class="shrink-0 rounded-2xl border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 xl:min-w-32">Filter</button>
        </form>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-[920px] w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Kode</th>
                            <th class="px-4 py-3 font-semibold">Obat</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Kategori</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Satuan</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Stok</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Minimum</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($reports as $report)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{{ $report->code }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $report->name }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $report->category_name ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $report->unit_name ?? '-' }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-900 whitespace-nowrap">{{ number_format((int) ($report->current_stock ?? 0)) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format((int) $report->minimum_stock) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada data laporan stok.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $reports->links() }}</div>
    </section>
</x-app-layout>
