<x-app-layout>
    <x-slot name="header">Stok Terkini</x-slot>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
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
                <p class="mt-1 text-sm text-slate-500">Lihat posisi stok per obat, batas minimum, dan ringkasan monitoring untuk membantu pengambilan keputusan.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('monitoring.stok.index') }}" class="mt-6 flex flex-col gap-3 xl:flex-row xl:items-center">
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
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Kode</th>
                            <th class="px-4 py-3 font-semibold">Obat</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Kategori</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Satuan</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Stok Saat Ini</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Minimum</th>
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
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        @if ($isEmpty)
                                            <span class="whitespace-nowrap rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800">Stok Habis</span>
                                        @elseif ($isLow)
                                            <span class="whitespace-nowrap rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Menipis</span>
                                        @else
                                            <span class="whitespace-nowrap rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Aman</span>
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
                                <td colspan="8" class="px-4 py-8 text-center text-slate-500">Belum ada data stok untuk ditampilkan.</td>
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
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Detail Monitoring Stok</p>
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
                                    </div>
                                </div>

                                <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5">
                                    <div class="flex flex-wrap gap-2">
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700" x-text="'Minimum: ' + new Intl.NumberFormat('id-ID').format(selected?.minimum_stock ?? 0)"></span>
                                        <span
                                            class="rounded-full px-3 py-1 text-xs font-semibold"
                                            :class="(selected?.current_stock ?? 0) === 0
                                                ? 'bg-rose-100 text-rose-800'
                                                : ((selected?.current_stock ?? 0) <= (selected?.minimum_stock ?? 0)
                                                    ? 'bg-amber-100 text-amber-800'
                                                    : 'bg-emerald-100 text-emerald-800')"
                                            x-text="(selected?.current_stock ?? 0) === 0
                                                ? 'Stok Habis'
                                                : ((selected?.current_stock ?? 0) <= (selected?.minimum_stock ?? 0)
                                                    ? 'Stok Menipis'
                                                    : 'Stok Aman')"
                                        ></span>
                                    </div>
                                    <p class="mt-4 text-sm leading-7 text-slate-600">Detail ini merangkum posisi stok obat terpilih terhadap batas minimum yang ditetapkan pada master obat.</p>
                                </div>
                            </div>

                            <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white">
                                <div class="border-b border-slate-200 px-5 py-4">
                                    <h4 class="font-semibold text-slate-900">Ringkasan Monitoring</h4>
                                    <p class="mt-1 text-sm text-slate-500">Interpretasi cepat untuk membantu menentukan tindak lanjut obat ini.</p>
                                </div>
                                <div class="grid gap-4 px-5 py-5 md:grid-cols-2">
                                    <article class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                        <p class="text-sm text-slate-500">Status monitoring</p>
                                        <p
                                            class="mt-2 text-2xl font-semibold"
                                            :class="(selected?.current_stock ?? 0) === 0
                                                ? 'text-rose-700'
                                                : ((selected?.current_stock ?? 0) <= (selected?.minimum_stock ?? 0)
                                                    ? 'text-amber-700'
                                                    : 'text-emerald-700')"
                                            x-text="(selected?.current_stock ?? 0) === 0
                                                ? 'Stok Habis'
                                                : ((selected?.current_stock ?? 0) <= (selected?.minimum_stock ?? 0)
                                                    ? 'Stok Menipis'
                                                    : 'Stok Aman')"
                                        ></p>
                                    </article>
                                    <article class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                        <p class="text-sm text-slate-500">Selisih terhadap minimum</p>
                                        <p
                                            class="mt-2 text-2xl font-semibold"
                                            :class="((selected?.current_stock ?? 0) - (selected?.minimum_stock ?? 0)) >= 0 ? 'text-emerald-700' : 'text-rose-700'"
                                            x-text="new Intl.NumberFormat('id-ID').format((selected?.current_stock ?? 0) - (selected?.minimum_stock ?? 0))"
                                        ></p>
                                    </article>
                                    <article class="rounded-2xl border border-slate-200 bg-white px-4 py-4 md:col-span-2">
                                        <p class="text-sm text-slate-500">Kesimpulan</p>
                                        <p class="mt-2 text-sm leading-7 text-slate-700">
                                            Obat ini memiliki stok
                                            <span class="font-semibold" x-text="new Intl.NumberFormat('id-ID').format(selected?.current_stock ?? 0)"></span>
                                            <span x-text="selected?.unit_name ? ' ' + selected.unit_name : ''"></span>
                                            dengan batas minimum
                                            <span class="font-semibold" x-text="new Intl.NumberFormat('id-ID').format(selected?.minimum_stock ?? 0)"></span>.
                                            Gunakan informasi ini untuk menentukan prioritas pengadaan berikutnya.
                                        </p>
                                    </article>
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
