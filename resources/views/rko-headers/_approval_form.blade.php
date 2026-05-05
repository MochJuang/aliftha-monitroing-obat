@csrf

<div class="space-y-8">
    <section class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 px-4 py-4">
            <p class="text-sm text-slate-500">Nomor RKO</p>
            <p class="mt-1 font-semibold text-slate-900">{{ $rkoHeader->rko_number }}</p>
        </div>
        <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 px-4 py-4">
            <p class="text-sm text-slate-500">Periode</p>
            <p class="mt-1 font-semibold text-slate-900">{{ sprintf('%02d', $rkoHeader->period_month) }}/{{ $rkoHeader->period_year }}</p>
        </div>
        <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 px-4 py-4">
            <p class="text-sm text-slate-500">Sumber Dana</p>
            <p class="mt-1 font-semibold text-slate-900">{{ $rkoHeader->fundingSource?->name ?? '-' }}</p>
        </div>
        <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 px-4 py-4">
            <p class="text-sm text-slate-500">Total Anggaran Usulan</p>
            <p class="mt-1 font-semibold text-slate-900">Rp {{ number_format((float) $rkoHeader->total_budget, 0, ',', '.') }}</p>
        </div>
        <div>
            <label for="status" class="block text-sm font-medium text-slate-700">Keputusan</label>
            <select id="status" name="status" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
                <option value="approved" @selected(old('status', $rkoHeader->status) === 'approved')>Setujui</option>
                <option value="rejected" @selected(old('status', $rkoHeader->status) === 'rejected')>Tolak</option>
            </select>
        </div>
        <div>
            <label for="approved_at" class="block text-sm font-medium text-slate-700">Tanggal persetujuan</label>
            <input id="approved_at" name="approved_at" type="date" value="{{ old('approved_at', optional($rkoHeader->approved_at)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
        </div>
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-slate-50/70 p-5">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Persetujuan Detail Obat</h3>
            <p class="mt-1 text-sm text-slate-500">Isi jumlah dan harga satuan yang benar-benar disetujui. Data ini akan membentuk realisasi pengadaan dan mutasi masuk otomatis.</p>
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-[1280px] w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Kode</th>
                            <th class="px-4 py-3 font-semibold">Obat</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Kategori</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Satuan</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Rencana</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Estimasi Harga</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Qty Disetujui</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Harga Disetujui</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($rkoHeader->items as $index => $item)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{{ $item->medicine->code }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-900">{{ $item->medicine->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $item->medicine->medicine_type ?: '-' }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $item->medicine->category?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $item->medicine->unit?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($item->planned_quantity) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">Rp {{ number_format((float) $item->estimated_unit_price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                    <input type="number" min="0" name="items[{{ $index }}][approved_quantity]" value="{{ old("items.$index.approved_quantity", $item->approved_quantity ?? $item->planned_quantity) }}" class="w-32 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <input type="number" min="0" step="0.01" name="items[{{ $index }}][approved_unit_price]" value="{{ old("items.$index.approved_unit_price", $item->approved_unit_price ?? $item->estimated_unit_price) }}" class="w-40 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('rko.header.show', $rkoHeader) }}" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</a>
        <button type="submit" class="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
            Simpan Persetujuan
        </button>
    </div>
</div>
