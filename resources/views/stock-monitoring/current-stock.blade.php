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

    <section
        x-data="{
            open: false,
            selected: null,
            details: @js($medicines->getCollection()->mapWithKeys(fn ($medicine) => [
                $medicine->id => [
                    'id' => (int) $medicine->id,
                    'code' => $medicine->code,
                    'name' => $medicine->name,
                    'brand' => $medicine->brand,
                    'dosage' => $medicine->dosage,
                    'category_name' => $medicine->category_name,
                    'unit_name' => $medicine->unit_name,
                    'current_stock' => (int) ($medicine->current_stock ?? 0),
                    'minimum_stock' => (int) $medicine->minimum_stock,
                    'active_batch_count' => (int) ($medicine->active_batch_count ?? 0),
                    'almost_expired_batch_count' => (int) ($medicine->almost_expired_batch_count ?? 0),
                    'nearest_expired_at' => $medicine->nearest_expired_at ? \Illuminate\Support\Carbon::parse($medicine->nearest_expired_at)->format('d M Y') : null,
                    'batch_details' => $medicine->batch_details ?? [],
                ],
            ])),
            openDetail(id) {
                this.selected = this.details[id] ?? null;
                this.open = !!this.selected;
            },
            closeDetail() {
                this.open = false;
                this.selected = null;
            }
        }"
        @keydown.escape.window="closeDetail()"
        class="mt-6 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm"
    >
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Monitoring Stok Per Obat</h3>
                <p class="mt-1 text-sm text-slate-500">Lihat stok aktif non-expired, batas minimum, dan buka detail untuk melihat batch pembentuk stok obat.</p>
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
                        <th class="px-4 py-3 font-semibold text-right whitespace-nowrap">Aksi</th>
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
                                <td class="px-4 py-3">
                                    <div class="flex justify-end">
                                        <button
                                            type="button"
                                            @click="openDetail({{ $medicine->id }})"
                                            class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                        >
                                            Detail
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-8 text-center text-slate-500">Belum ada data stok untuk ditampilkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $medicines->links() }}
        </div>

        <template x-teleport="body">
            <div x-cloak x-show="open">
                <div x-transition.opacity class="fixed inset-0 z-[90] bg-slate-950/60 backdrop-blur-sm" @click="closeDetail()"></div>

                <div x-transition class="fixed inset-0 z-[100] overflow-y-auto p-4 lg:p-8">
                    <div class="flex min-h-full items-start justify-center lg:items-center">
                    <div class="my-4 flex w-full max-w-7xl max-h-[92vh] flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-2xl" @click.stop>
                    <div class="flex items-start justify-between border-b border-slate-200 bg-slate-50 px-6 py-5">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Detail Stok Terkini</p>
                            <h3 class="mt-2 text-xl font-semibold text-slate-900" x-text="selected?.name"></h3>
                            <p class="mt-1 text-sm text-slate-500">
                                <span x-text="selected?.code"></span>
                                <template x-if="selected?.brand">
                                    <span x-text="' | ' + selected.brand"></span>
                                </template>
                                <template x-if="selected?.dosage">
                                    <span x-text="' | ' + selected.dosage"></span>
                                </template>
                            </p>
                        </div>
                        <button type="button" @click="closeDetail()" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-slate-300 bg-white text-slate-700 shadow-sm transition hover:bg-slate-100" aria-label="Tutup modal">
                            <span class="text-xl leading-none">&times;</span>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto px-6 py-6">
                        <div class="grid gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">
                            <div class="space-y-4">
                                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 p-5">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Ringkasan</p>
                                    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                                        <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
                                            <p class="text-sm text-slate-500">Kategori</p>
                                            <p class="mt-1 font-semibold text-slate-900" x-text="selected?.category_name || '-'"></p>
                                        </article>
                                        <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
                                            <p class="text-sm text-slate-500">Satuan</p>
                                            <p class="mt-1 font-semibold text-slate-900" x-text="selected?.unit_name || '-'"></p>
                                        </article>
                                        <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
                                            <p class="text-sm text-slate-500">Stok saat ini</p>
                                            <p class="mt-1 text-2xl font-semibold text-slate-900" x-text="new Intl.NumberFormat('id-ID').format(selected?.current_stock ?? 0)"></p>
                                        </article>
                                        <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
                                            <p class="text-sm text-slate-500">Stok minimum</p>
                                            <p class="mt-1 text-2xl font-semibold text-slate-900" x-text="new Intl.NumberFormat('id-ID').format(selected?.minimum_stock ?? 0)"></p>
                                        </article>
                                        <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4">
                                            <p class="text-sm text-slate-500">Batch aktif</p>
                                            <p class="mt-1 text-2xl font-semibold text-slate-900" x-text="new Intl.NumberFormat('id-ID').format(selected?.active_batch_count ?? 0)"></p>
                                        </article>
                                    </div>
                                </div>

                                <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5">
                                    <div class="flex flex-wrap gap-2">
                                        <template x-if="selected?.nearest_expired_at">
                                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800" x-text="'Expired terdekat: ' + selected.nearest_expired_at"></span>
                                        </template>
                                        <template x-if="(selected?.almost_expired_batch_count ?? 0) > 0">
                                            <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-800" x-text="'Batch hampir expired: ' + new Intl.NumberFormat('id-ID').format(selected?.almost_expired_batch_count ?? 0)"></span>
                                        </template>
                                        <template x-if="(selected?.almost_expired_batch_count ?? 0) === 0">
                                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">Batch aktif dalam kondisi aman</span>
                                        </template>
                                    </div>
                                    <p class="mt-4 text-sm leading-7 text-slate-600">Detail ini menampilkan batch aktif yang saat ini membentuk stok tersedia pada obat terpilih.</p>
                                </div>
                            </div>

                            <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white">
                                <div class="border-b border-slate-200 px-5 py-4">
                                    <h4 class="font-semibold text-slate-900">Batch Monitoring Obat</h4>
                                    <p class="mt-1 text-sm text-slate-500">Daftar batch aktif yang membentuk stok terkini untuk obat ini.</p>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-[1080px] w-full divide-y divide-slate-200 text-sm">
                                        <thead class="bg-slate-50 text-left text-slate-500">
                                            <tr>
                                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Batch</th>
                                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Tgl Terima</th>
                                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Expired</th>
                                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Sumber</th>
                                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Qty Diterima</th>
                                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Qty Sisa</th>
                                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 bg-white">
                                            <template x-if="!selected?.batch_details?.length">
                                                <tr>
                                                    <td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada batch aktif untuk obat ini.</td>
                                                </tr>
                                            </template>
                                            <template x-for="batch in (selected?.batch_details || [])" :key="batch.batch_number + '-' + batch.expired_at">
                                                <tr>
                                                    <td class="px-4 py-3 font-medium whitespace-nowrap text-slate-900" x-text="batch.batch_number"></td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-slate-600" x-text="batch.received_date || '-'"></td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-slate-600" x-text="batch.expired_at"></td>
                                                    <td class="px-4 py-3 text-slate-600" x-text="batch.source_name || '-'"></td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-slate-600" x-text="new Intl.NumberFormat('id-ID').format(batch.qty_received)"></td>
                                                    <td class="px-4 py-3 whitespace-nowrap font-semibold text-slate-900" x-text="new Intl.NumberFormat('id-ID').format(batch.qty_remaining)"></td>
                                                    <td class="px-4 py-3">
                                                        <span
                                                            class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold"
                                                            :class="batch.status_color === 'sky'
                                                                ? 'bg-sky-100 text-sky-800'
                                                                : 'bg-emerald-100 text-emerald-800'"
                                                            x-text="batch.status_label"
                                                        ></span>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                    </div>
                </div>
            </div>
        </template>
    </section>
</x-app-layout>
