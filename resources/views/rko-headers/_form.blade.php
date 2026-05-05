@php
    $months = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    $initialItems = old('items', isset($rkoHeader) && $rkoHeader->relationLoaded('items')
        ? $rkoHeader->items->map(fn ($item) => [
            'medicine_id' => (string) $item->medicine_id,
            'planned_quantity' => $item->planned_quantity,
            'estimated_unit_price' => $item->estimated_unit_price,
            'priority' => $item->priority,
            'notes' => $item->notes,
        ])->values()->all()
        : [[
            'medicine_id' => '',
            'planned_quantity' => 1,
            'estimated_unit_price' => 0,
            'priority' => 'sedang',
            'notes' => '',
        ]]);

    $medicineOptions = $medicines->map(fn ($medicine) => [
        'id' => $medicine->id,
        'name' => $medicine->name,
        'code' => $medicine->code,
        'category' => $medicine->category?->name,
        'unit' => $medicine->unit?->name,
        'label' => sprintf(
            '%s - %s | %s | %s',
            $medicine->code,
            $medicine->name,
            $medicine->category?->name ?? '-',
            $medicine->unit?->name ?? '-'
        ),
    ])->values();
@endphp

@csrf

<div
    x-data='{
        items: @json($initialItems),
        medicines: @json($medicineOptions),
        addItem() {
            this.items.push({
                medicine_id: "",
                planned_quantity: 1,
                estimated_unit_price: 0,
                priority: "sedang",
                notes: "",
            });
        },
        removeItem(index) {
            if (this.items.length === 1) {
                return;
            }

            this.items.splice(index, 1);
        },
        selectedMedicine(medicineId) {
            return this.medicines.find((medicine) => String(medicine.id) === String(medicineId));
        },
    }'
    class="space-y-8"
>
    <input type="hidden" id="rko_status" name="status" value="{{ old('status', $rkoHeader->status === 'draft' ? 'draft' : 'submitted') }}">

    <section class="grid gap-6 lg:grid-cols-2">
        <div>
            <label for="rko_number" class="block text-sm font-medium text-slate-700">Nomor RKO</label>
            <input id="rko_number" name="rko_number" type="text" value="{{ old('rko_number', $nextRkoNumber) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
        </div>

        <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 px-4 py-4">
            <label class="block text-sm font-medium text-slate-700">Alur dokumen</label>
            <p class="mt-2 text-sm leading-6 text-slate-600">Form ini hanya untuk pengajuan RKO. Jumlah dan harga yang disetujui akan diisi pada form persetujuan terpisah oleh pihak penyetuju.</p>
        </div>

        <div>
            <label for="period_month" class="block text-sm font-medium text-slate-700">Periode Bulan</label>
            <select id="period_month" name="period_month" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
                @foreach ($months as $value => $label)
                    <option value="{{ $value }}" @selected((int) old('period_month', $rkoHeader->period_month) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="period_year" class="block text-sm font-medium text-slate-700">Periode Tahun</label>
            <input id="period_year" name="period_year" type="number" min="2020" max="2100" value="{{ old('period_year', $rkoHeader->period_year) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
        </div>

        <div>
            <label for="funding_source_id" class="block text-sm font-medium text-slate-700">Sumber dana</label>
            <select id="funding_source_id" name="funding_source_id" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
                <option value="">Pilih sumber dana</option>
                @foreach ($fundingSources as $fundingSource)
                    <option value="{{ $fundingSource->id }}" @selected((string) old('funding_source_id', $rkoHeader->funding_source_id) === (string) $fundingSource->id)>
                        {{ $fundingSource->code }} - {{ $fundingSource->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="total_budget" class="block text-sm font-medium text-slate-700">Total anggaran</label>
            <input id="total_budget" name="total_budget" type="number" min="0" step="0.01" value="{{ old('total_budget', $rkoHeader->total_budget ?? 0) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
        </div>

        <div class="lg:col-span-2">
            <label for="notes" class="block text-sm font-medium text-slate-700">Catatan</label>
            <textarea id="notes" name="notes" rows="4" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('notes', $rkoHeader->notes) }}</textarea>
        </div>
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-slate-50/70 p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Detail Kebutuhan Obat</h3>
                <p class="mt-1 text-sm text-slate-500">Tambahkan item obat yang dibutuhkan untuk periode RKO ini beserta jumlah rencana dan estimasi harga satuan.</p>
            </div>
            <button type="button" class="rounded-2xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100" @click="addItem()">
                Tambah Item
            </button>
        </div>

        <div class="mt-6 space-y-4">
            <template x-for="(item, index) in items" :key="index">
                <div class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <p class="text-sm font-semibold text-slate-900" x-text="`Item ${index + 1}`"></p>
                        <button type="button" class="rounded-xl border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50" @click="removeItem(index)" x-show="items.length > 1">
                            Hapus
                        </button>
                    </div>

	                    <div class="mt-4 space-y-4">
		                        <div class="overflow-x-auto">
		                        <div class="grid grid-cols-6 gap-4 min-w-[980px]">
		                            <div class="col-span-4">
		                                <label class="block text-sm font-medium text-slate-700">Obat</label>
		                                <select class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" :name="`items[${index}][medicine_id]`" x-model="item.medicine_id" required>
		                                    <option value="">Pilih obat</option>
		                                    <template x-for="medicine in medicines" :key="medicine.id">
		                                        <option :value="medicine.id" x-text="medicine.label"></option>
		                                    </template>
		                                </select>
		                            </div>
		
		                            <div class="col-span-1">
		                                <label class="block text-sm font-medium text-slate-700">Jumlah rencana</label>
		                                <input type="number" min="1" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" :name="`items[${index}][planned_quantity]`" x-model="item.planned_quantity" required>
		                            </div>
		
		                            <div class="col-span-1">
		                                <label class="block text-sm font-medium text-slate-700">Estimasi harga satuan</label>
		                                <input type="number" min="0" step="0.01" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" :name="`items[${index}][estimated_unit_price]`" x-model="item.estimated_unit_price" required>
		                            </div>
		                        </div>
		                        </div>
	
		                        <div class="overflow-x-auto">
		                        <div class="grid grid-cols-6 gap-4 min-w-[980px]">
		                            <div class="col-span-2">
		                                <label class="block text-sm font-medium text-slate-700">Prioritas</label>
		                                <select class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" :name="`items[${index}][priority]`" x-model="item.priority" required>
		                                    <option value="tinggi">Tinggi</option>
		                                    <option value="sedang">Sedang</option>
		                                    <option value="rendah">Rendah</option>
		                                </select>
		                            </div>
		
		                            <div class="col-span-4">
		                                <label class="block text-sm font-medium text-slate-700">Catatan item</label>
		                                <input type="text" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" :name="`items[${index}][notes]`" x-model="item.notes">
		                            </div>
		                        </div>
		                        </div>
	
	                        <div>
	                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600" x-show="selectedMedicine(item.medicine_id)">
	                                <span class="font-medium text-slate-900" x-text="selectedMedicine(item.medicine_id)?.code"></span>
	                                <span x-text="' | ' + (selectedMedicine(item.medicine_id)?.category || '-')"></span>
	                                <span x-text="' | ' + (selectedMedicine(item.medicine_id)?.unit || '-')"></span>
	                                <span class="block mt-2 text-slate-700" x-text="'Total estimasi: ' + new Intl.NumberFormat('id-ID').format(Number(item.planned_quantity || 0) * Number(item.estimated_unit_price || 0))"></span>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	            </template>
	        </div>
	    </section>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('rko.header.index') }}" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</a>
        <button type="submit" onclick="document.getElementById('rko_status').value='draft'" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
            Simpan Draft
        </button>
        <button type="submit" onclick="document.getElementById('rko_status').value='submitted'" class="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
            Simpan & Ajukan
        </button>
    </div>
</div>
