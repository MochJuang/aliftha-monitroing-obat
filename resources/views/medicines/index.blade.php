<x-app-layout>
    <x-slot name="header">Obat</x-slot>

    <section
        x-data="{
            details: @js($medicineDetails),
            open: false,
            selected: null,
            showDetail(id) {
                this.selected = this.details[id] ?? null;
                this.open = !!this.selected;
            },
            closeDetail() {
                this.open = false;
                this.selected = null;
            }
        }"
        @keydown.escape.window="closeDetail()"
        class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm"
    >
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <p class="text-sm text-slate-500">Kelola master obat kontrasepsi beserta kategori, satuan, dan stok minimum.</p>
            <a href="{{ route('master-obat.obat.create') }}" class="inline-flex rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Tambah Obat</a>
        </div>

        <form method="GET" action="{{ route('master-obat.obat.index') }}" class="mt-6 flex flex-col gap-3 xl:flex-row xl:items-center">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode, nama, atau merek..." class="w-full min-w-0 flex-1 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
            <select name="status" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500 xl:w-48 xl:shrink-0">
                <option value="">Semua status</option>
                <option value="active" @selected($status === 'active')>Aktif</option>
                <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
            </select>
            <button type="submit" class="shrink-0 rounded-2xl border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 xl:min-w-32">Filter</button>
        </form>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
            <table class="min-w-[980px] w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Kode</th>
                        <th class="px-4 py-3 font-semibold">Nama</th>
                        <th class="px-4 py-3 font-semibold">Kategori</th>
                        <th class="px-4 py-3 font-semibold">Satuan</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Stok minimum</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($medicines as $medicine)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{{ $medicine->code }}</td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-900">{{ $medicine->name }}</p>
                                <p class="text-xs text-slate-500">{{ $medicine->brand ?: 'Tanpa merek' }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $medicine->category->name }}</td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $medicine->unit->name }}</td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($medicine->minimum_stock) }}</td>
                            <td class="px-4 py-3">
                                <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold {{ $medicine->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $medicine->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2 whitespace-nowrap">
                                    <button
                                        type="button"
                                        @click="showDetail({{ $medicine->id }})"
                                        class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                    >
                                        Detail
                                    </button>
                                    <a href="{{ route('master-obat.obat.edit', $medicine) }}" class="rounded-xl border border-amber-300 px-3 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-50">Edit</a>
                                    <form method="POST" action="{{ route('master-obat.obat.destroy', $medicine) }}" onsubmit="return confirm('Hapus data obat ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-xl border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada data obat.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        <div class="mt-6">{{ $medicines->links() }}</div>

        <template x-teleport="body">
            <div x-cloak x-show="open">
                <div x-transition.opacity class="fixed inset-0 z-[90] bg-slate-950/60 backdrop-blur-sm" @click="closeDetail()"></div>

                <div x-transition class="fixed inset-0 z-[100] overflow-y-auto p-4 lg:p-8">
                    <div class="flex min-h-full items-start justify-center lg:items-center">
                    <div class="my-4 flex w-full max-w-7xl max-h-[92vh] flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-2xl" @click.stop>
                        <div class="flex items-start justify-between border-b border-slate-200 bg-slate-50 px-6 py-5">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Detail Obat</p>
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
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700" x-text="'Minimum: ' + new Intl.NumberFormat('id-ID').format(selected?.minimum_stock ?? 0)"></span>
                                            <template x-if="selected?.nearest_expired_at">
                                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800" x-text="'Expired terdekat: ' + selected.nearest_expired_at"></span>
                                            </template>
                                            <span
                                                class="rounded-full px-3 py-1 text-xs font-semibold"
                                                :class="selected?.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'"
                                                x-text="selected?.is_active ? 'Aktif' : 'Nonaktif'"
                                            ></span>
                                        </div>
                                        <p class="mt-4 text-sm leading-7 text-slate-600" x-text="selected?.description || 'Belum ada deskripsi obat.'"></p>
                                    </div>
                                </div>

                                <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white">
                                    <div class="border-b border-slate-200 px-5 py-4">
                                        <h4 class="font-semibold text-slate-900">Kartu Stok Ringkas</h4>
                                        <p class="mt-1 text-sm text-slate-500">Menampilkan 12 mutasi terakhir untuk obat ini.</p>
                                    </div>
                                    <div class="grid gap-3 border-b border-slate-200 px-5 py-4 md:grid-cols-3">
                                        <article class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4">
                                            <p class="text-sm text-emerald-800">Total realisasi pengadaan</p>
                                            <p class="mt-2 text-3xl font-semibold text-emerald-900" x-text="new Intl.NumberFormat('id-ID').format(selected?.movement_summary?.total_in ?? 0)"></p>
                                        </article>
                                        <article class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4">
                                            <p class="text-sm text-rose-800">Total distribusi obat</p>
                                            <p class="mt-2 text-3xl font-semibold text-rose-900" x-text="new Intl.NumberFormat('id-ID').format(selected?.movement_summary?.total_out ?? 0)"></p>
                                        </article>
                                        <article class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-4">
                                            <p class="text-sm text-sky-800">Total penyesuaian stok</p>
                                            <p class="mt-2 text-3xl font-semibold text-sky-900" x-text="new Intl.NumberFormat('id-ID').format(selected?.movement_summary?.total_adjustment ?? 0)"></p>
                                        </article>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-[1180px] w-full divide-y divide-slate-200 text-sm">
                                            <thead class="bg-slate-50 text-left text-slate-500">
                                                <tr>
                                                    <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal</th>
                                                    <th class="px-4 py-3 font-semibold whitespace-nowrap">Jenis</th>
                                                    <th class="px-4 py-3 font-semibold whitespace-nowrap">Referensi</th>
                                                    <th class="px-4 py-3 font-semibold whitespace-nowrap">Batch</th>
                                                    <th class="px-4 py-3 font-semibold whitespace-nowrap">Sumber / Tujuan</th>
                                                    <th class="px-4 py-3 font-semibold whitespace-nowrap text-right">Masuk</th>
                                                    <th class="px-4 py-3 font-semibold whitespace-nowrap text-right">Keluar</th>
                                                    <th class="px-4 py-3 font-semibold whitespace-nowrap text-right">Adj</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 bg-white">
                                                <template x-if="!selected?.movements?.length">
                                                    <tr>
                                                        <td colspan="8" class="px-4 py-8 text-center text-slate-500">Belum ada mutasi untuk obat ini.</td>
                                                    </tr>
                                                </template>
                                                <template x-for="movement in (selected?.movements || [])" :key="movement.reference_number + '-' + movement.movement_date + '-' + movement.batch_number">
                                                    <tr>
                                                        <td class="px-4 py-3 whitespace-nowrap text-slate-600" x-text="movement.movement_date"></td>
                                                        <td class="px-4 py-3">
                                                            <span
                                                                class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold"
                                                                :class="movement.type === 'realisasi_pengadaan'
                                                                    ? 'bg-emerald-100 text-emerald-800'
                                                                    : (movement.type === 'distribusi_obat'
                                                                        ? 'bg-rose-100 text-rose-800'
                                                                        : 'bg-sky-100 text-sky-800')"
                                                                x-text="movement.type_label"
                                                            ></span>
                                                        </td>
                                                        <td class="px-4 py-3 font-medium whitespace-nowrap text-slate-900" x-text="movement.reference_number"></td>
                                                        <td class="px-4 py-3 whitespace-nowrap text-slate-600" x-text="movement.batch_number || '-'"></td>
                                                        <td class="px-4 py-3 text-slate-600" x-text="movement.counterpart_name || movement.notes || '-'"></td>
                                                        <td class="px-4 py-3 text-right whitespace-nowrap text-emerald-700" x-text="movement.qty_in ? new Intl.NumberFormat('id-ID').format(movement.qty_in) : '-'"></td>
                                                        <td class="px-4 py-3 text-right whitespace-nowrap text-rose-700" x-text="movement.qty_out ? new Intl.NumberFormat('id-ID').format(movement.qty_out) : '-'"></td>
                                                        <td class="px-4 py-3 text-right whitespace-nowrap text-sky-700" x-text="movement.adjustment_qty ? new Intl.NumberFormat('id-ID').format(movement.adjustment_qty) : '-'"></td>
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
            </div>
        </template>
    </section>
</x-app-layout>
