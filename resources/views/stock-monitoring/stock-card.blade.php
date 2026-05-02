<x-app-layout>
    <x-slot name="header">Kartu Stok</x-slot>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Histori Pergerakan Stok</h3>
            <p class="mt-1 text-sm text-slate-500">Pilih obat untuk melihat mutasi stok masuk, stok keluar, dan adjustment dalam urutan kronologis beserta saldo berjalan.</p>
        </div>

        <form method="GET" action="{{ route('stock-monitoring.stock-card') }}" class="mt-6 grid gap-3 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_180px_180px_140px] xl:items-end">
            <div>
                <label for="medicine_id" class="block text-sm font-medium text-slate-700">Obat</label>
                <select
                    id="medicine_id"
                    name="medicine_id"
                    class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                >
                    <option value="">Pilih obat</option>
                    @foreach ($medicines as $medicine)
                        <option value="{{ $medicine->id }}" @selected($medicineId === (string) $medicine->id)>{{ $medicine->code }} - {{ $medicine->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="batch_number" class="block text-sm font-medium text-slate-700">Batch</label>
                <input
                    id="batch_number"
                    name="batch_number"
                    type="text"
                    value="{{ $batchNumber }}"
                    placeholder="Opsional"
                    class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                >
            </div>

            <div>
                <label for="date_from" class="block text-sm font-medium text-slate-700">Dari tanggal</label>
                <input
                    id="date_from"
                    name="date_from"
                    type="date"
                    value="{{ $dateFrom }}"
                    class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                >
            </div>

            <div>
                <label for="date_to" class="block text-sm font-medium text-slate-700">Sampai tanggal</label>
                <input
                    id="date_to"
                    name="date_to"
                    type="date"
                    value="{{ $dateTo }}"
                    class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                >
            </div>

            <button type="submit" class="rounded-2xl border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 xl:min-w-32">
                Tampilkan
            </button>
        </form>
    </section>

    @if ($selectedMedicine)
        <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Saldo awal</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($summary['opening_balance']) }}</p>
            </article>
            <article class="rounded-[2rem] border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                <p class="text-sm text-emerald-800">Stok masuk</p>
                <p class="mt-2 text-3xl font-semibold text-emerald-900">{{ number_format($summary['total_in']) }}</p>
            </article>
            <article class="rounded-[2rem] border border-rose-200 bg-rose-50 p-5 shadow-sm">
                <p class="text-sm text-rose-800">Stok keluar</p>
                <p class="mt-2 text-3xl font-semibold text-rose-900">{{ number_format($summary['total_out']) }}</p>
            </article>
            <article class="rounded-[2rem] border border-sky-200 bg-sky-50 p-5 shadow-sm">
                <p class="text-sm text-sky-800">Penyesuaian Stok</p>
                <p class="mt-2 text-3xl font-semibold {{ $summary['total_adjustment'] >= 0 ? 'text-sky-900' : 'text-rose-900' }}">{{ number_format($summary['total_adjustment']) }}</p>
            </article>
            <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Saldo akhir</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($summary['closing_balance']) }}</p>
            </article>
        </section>

        <section class="mt-6 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">{{ $selectedMedicine->name }}</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $selectedMedicine->code }}
                        @if ($selectedMedicine->category?->name)
                            | {{ $selectedMedicine->category->name }}
                        @endif
                        @if ($selectedMedicine->unit?->name)
                            | Satuan: {{ $selectedMedicine->unit->name }}
                        @endif
                    </p>
                </div>
                @if ($batchNumber !== '')
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-800">
                        Filter Batch: {{ $batchNumber }}
                    </span>
                @endif
            </div>

            <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-[1320px] w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Jenis</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Referensi</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Batch</th>
                                <th class="px-4 py-3 font-semibold">Sumber / Tujuan</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Masuk</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Keluar</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Adj</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Saldo</th>
                                <th class="px-4 py-3 font-semibold">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($paginatedMovements as $movement)
                                <tr>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($movement['movement_date'])->format('d M Y') }}</td>
                                    <td class="px-4 py-3">
                                        @if ($movement['type'] === 'stok_masuk')
                                            <span class="whitespace-nowrap rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Realisasi Pengadaan</span>
                                        @elseif ($movement['type'] === 'stok_keluar')
                                            <span class="whitespace-nowrap rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800">Distribusi Obat</span>
                                        @else
                                            <span class="whitespace-nowrap rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-800">Penyesuaian Stok</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{{ $movement['reference_number'] }}</td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $movement['batch_number'] ?: '-' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $movement['counterpart_name'] ?: '-' }}</td>
                                    <td class="px-4 py-3 text-emerald-700 whitespace-nowrap">{{ $movement['qty_in'] ? number_format($movement['qty_in']) : '-' }}</td>
                                    <td class="px-4 py-3 text-rose-700 whitespace-nowrap">{{ $movement['qty_out'] ? number_format($movement['qty_out']) : '-' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap {{ $movement['adjustment_qty'] > 0 ? 'text-sky-700' : ($movement['adjustment_qty'] < 0 ? 'text-rose-700' : 'text-slate-600') }}">
                                        {{ $movement['adjustment_qty'] ? number_format($movement['adjustment_qty']) : '-' }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-slate-900 whitespace-nowrap">{{ number_format($movement['running_balance']) }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $movement['notes'] ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-8 text-center text-slate-500">Belum ada mutasi untuk filter yang dipilih.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">
                {{ $paginatedMovements->links() }}
            </div>
        </section>
    @else
        <section class="mt-6 rounded-[2rem] border border-dashed border-slate-300 bg-white p-8 text-center shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Pilih obat untuk melihat kartu stok</h3>
            <p class="mt-2 text-sm text-slate-500">Setelah obat dipilih, sistem akan menampilkan histori stok masuk, distribusi, adjustment, dan saldo berjalan.</p>
        </section>
    @endif
</x-app-layout>
