<x-app-layout>
    <x-slot name="header">Detail RKO Header</x-slot>

    <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm text-slate-500">Nomor RKO</p>
                    <h3 class="mt-2 text-2xl font-semibold text-slate-900">{{ $rkoHeader->rko_number }}</h3>
                    <p class="mt-2 text-sm text-slate-500">Periode {{ sprintf('%02d', $rkoHeader->period_month) }}/{{ $rkoHeader->period_year }}</p>
                </div>
                <div class="flex items-center gap-3">
                    @can('create-rko')
                        @if ($rkoHeader->status !== 'approved')
                            <a href="{{ route('rko.header.edit', $rkoHeader) }}" class="rounded-2xl border border-amber-300 px-4 py-2 text-sm font-medium text-amber-700 hover:bg-amber-50">Edit Pengajuan</a>
                        @endif
                    @endcan
                    @can('approve-rko')
                        <a href="{{ route('rko.header.approval.edit', $rkoHeader) }}" class="rounded-2xl border border-emerald-300 px-4 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-50">Form Persetujuan</a>
                    @endcan
                </div>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Status</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ match($rkoHeader->status) { 'draft' => 'Draft', 'submitted' => 'Diajukan', 'approved' => 'Disetujui', default => 'Ditolak' } }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Jumlah item</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ number_format($rkoHeader->items->count()) }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Sumber dana</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $rkoHeader->fundingSource?->name ?? '-' }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Penyusun</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $rkoHeader->submitter?->name ?? '-' }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Penyetuju</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $rkoHeader->approver?->name ?? '-' }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Total anggaran</p>
                    <p class="mt-1 font-semibold text-slate-900">Rp {{ number_format((float) $rkoHeader->total_budget, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Tanggal pengajuan</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $rkoHeader->submitted_at?->format('d M Y') ?? '-' }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Tanggal persetujuan</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $rkoHeader->approved_at?->format('d M Y') ?? '-' }}</p>
                </div>
            </div>

            <div class="mt-6 rounded-2xl bg-slate-50 px-4 py-4">
                <p class="text-sm text-slate-500">Catatan</p>
                <p class="mt-2 text-sm leading-7 text-slate-700">{{ $rkoHeader->notes ?: 'Belum ada catatan RKO.' }}</p>
            </div>

            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                Jika dokumen ini berstatus <span class="font-semibold text-slate-900">Disetujui</span>, sistem akan otomatis membentuk <span class="font-semibold text-slate-900">Realisasi Pengadaan</span> dan <span class="font-semibold text-slate-900">Mutasi Stok MASUK</span> berdasarkan jumlah yang disetujui pada setiap item.
            </div>
        </article>

        <div class="space-y-6">
	            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
	                <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
	                    <p class="text-sm text-slate-500">Total rencana</p>
	                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($mutationSummary['total_planned_qty']) }}</p>
	                </article>
	                <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
	                    <p class="text-sm text-slate-500">Total disetujui</p>
	                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($mutationSummary['total_approved_qty']) }}</p>
	                </article>
	                <article class="rounded-[2rem] border border-sky-200 bg-sky-50 p-5 shadow-sm sm:col-span-2 lg:col-span-1">
	                    <p class="text-sm text-sky-800">Total realisasi pengadaan</p>
	                    <p class="mt-2 text-3xl font-semibold text-sky-900">{{ number_format($realizationSummary['total_quantity']) }}</p>
	                </article>
	                <article class="rounded-[2rem] border border-emerald-200 bg-emerald-50 p-5 shadow-sm sm:col-span-2 lg:col-span-3">
	                    <div class="grid gap-4 sm:grid-cols-3">
	                        <div>
	                            <p class="text-sm text-emerald-800">Realisasi linked</p>
	                            <p class="mt-2 text-3xl font-semibold text-emerald-900">{{ number_format($realizationSummary['linked_count']) }}</p>
	                        </div>
	                        <div>
                            <p class="text-sm text-emerald-800">Nilai realisasi</p>
                            <p class="mt-2 text-3xl font-semibold text-emerald-900">Rp {{ number_format($realizationSummary['total_amount'], 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-emerald-800">Mutasi masuk linked</p>
                            <p class="mt-2 text-3xl font-semibold text-emerald-900">{{ number_format($mutationSummary['linked_count']) }}</p>
                        </div>
                    </div>
                </article>
            </section>

            <article class="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h3 class="text-lg font-semibold text-slate-900">Detail Kebutuhan Obat</h3>
                    <p class="mt-1 text-sm text-slate-500">Rincian item obat yang mencakup data usulan serta hasil persetujuan agar proses pengajuan dan approval lebih terpisah.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-[1440px] w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Kode</th>
                                <th class="px-4 py-3 font-semibold">Obat</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Jenis</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Kategori</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Satuan</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Rencana</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Disetujui</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Estimasi Harga</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Harga Disetujui</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Total Estimasi</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Total Disetujui</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Prioritas</th>
                                <th class="px-4 py-3 font-semibold">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($rkoHeader->items as $item)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{{ $item->medicine->code }}</td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-slate-900">{{ $item->medicine->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $item->medicine->brand ?: '-' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $item->medicine->medicine_type ?: '-' }}</td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $item->medicine->category?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $item->medicine->unit?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($item->planned_quantity) }}</td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $item->approved_quantity !== null ? number_format($item->approved_quantity) : '-' }}</td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">Rp {{ number_format((float) $item->estimated_unit_price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $item->approved_unit_price !== null ? 'Rp '.number_format((float) $item->approved_unit_price, 0, ',', '.') : '-' }}</td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">Rp {{ number_format((float) $item->total_estimate, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                        {{ $item->approved_quantity !== null && $item->approved_unit_price !== null ? 'Rp '.number_format((float) $item->approved_quantity * (float) $item->approved_unit_price, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span @class([
                                            'whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold',
                                            'bg-rose-100 text-rose-800' => $item->priority === 'tinggi',
                                            'bg-amber-100 text-amber-800' => $item->priority === 'sedang',
                                            'bg-sky-100 text-sky-800' => $item->priority === 'rendah',
                                        ])>
                                            {{ ucfirst($item->priority) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">{{ $item->notes ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="13" class="px-4 py-8 text-center text-slate-500">Belum ada item pada dokumen RKO ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h3 class="text-lg font-semibold text-slate-900">Realisasi Pengadaan</h3>
                    <p class="mt-1 text-sm text-slate-500">Data realisasi pengadaan yang terbentuk dari hasil persetujuan dokumen RKO ini.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-[1100px] w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Sumber Dana</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Obat</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Qty</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Harga Satuan</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap">Total</th>
                                <th class="px-4 py-3 font-semibold">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($linkedRealizations as $realization)
                                <tr>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $realization->realization_date?->format('d M Y') ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $realization->fundingSource?->name ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-slate-900">{{ $realization->medicine?->name ?? '-' }}</p>
                                        <p class="text-xs text-slate-500">{{ $realization->medicine?->code ?? '-' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($realization->realized_quantity) }}</td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">Rp {{ number_format((float) $realization->unit_price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">Rp {{ number_format((float) $realization->total_amount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $realization->notes ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada realisasi pengadaan yang terhubung ke RKO ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h3 class="text-lg font-semibold text-slate-900">Mutasi Stok Terkait</h3>
                    <p class="mt-1 text-sm text-slate-500">Daftar mutasi stok masuk yang dibentuk dari persetujuan dokumen RKO ini.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-[900px] w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-slate-500">
                            <tr>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Nomor Mutasi</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Jenis</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Item</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Qty</th>
                            <th class="px-4 py-3 font-semibold text-right whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($linkedMutations as $mutation)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{{ $mutation->mutation_number }}</td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $mutation->mutation_date->format('d M Y') }}</td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $mutation->mutation_type }}</td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($mutation->items_count) }}</td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format((int) ($mutation->items_sum_quantity ?? 0)) }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end">
                                            @can('manage-stock-mutations')
                                                <a href="{{ route('transaksi.mutasi.show', $mutation) }}" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">Detail</a>
                                            @else
                                                <span class="text-xs text-slate-400">Detail terbatas</span>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada mutasi stok yang terhubung ke RKO ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </div>
    </section>
</x-app-layout>
