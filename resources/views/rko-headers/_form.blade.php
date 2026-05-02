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
            'approved_quantity' => $item->approved_quantity,
            'notes' => $item->notes,
        ])->values()->all()
        : [[
            'medicine_id' => '',
            'planned_quantity' => 1,
            'approved_quantity' => '',
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
                approved_quantity: "",
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
    <section class="grid gap-6 lg:grid-cols-2">
        <div>
            <label for="rko_number" class="block text-sm font-medium text-slate-700">Nomor RKO</label>
            <input id="rko_number" name="rko_number" type="text" value="{{ old('rko_number', $nextRkoNumber) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
            <select id="status" name="status" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
                <option value="draft" @selected(old('status', $rkoHeader->status) === 'draft')>Draft</option>
                <option value="submitted" @selected(old('status', $rkoHeader->status) === 'submitted')>Diajukan</option>
                <option value="approved" @selected(old('status', $rkoHeader->status) === 'approved')>Disetujui</option>
                <option value="rejected" @selected(old('status', $rkoHeader->status) === 'rejected')>Ditolak</option>
            </select>
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

        <div class="lg:col-span-2">
            <label for="notes" class="block text-sm font-medium text-slate-700">Catatan</label>
            <textarea id="notes" name="notes" rows="4" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('notes', $rkoHeader->notes) }}</textarea>
        </div>
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-slate-50/70 p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Detail Kebutuhan Obat</h3>
                <p class="mt-1 text-sm text-slate-500">Tambahkan item obat yang dibutuhkan untuk periode RKO ini, beserta jumlah rencana dan jumlah yang disetujui jika sudah ada.</p>
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

                    <div class="mt-4 grid gap-4 lg:grid-cols-2 xl:grid-cols-4">
                        <div class="xl:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Obat</label>
                            <select class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" :name="`items[${index}][medicine_id]`" x-model="item.medicine_id" required>
                                <option value="">Pilih obat</option>
                                <template x-for="medicine in medicines" :key="medicine.id">
                                    <option :value="medicine.id" x-text="medicine.label"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Jumlah rencana</label>
                            <input type="number" min="1" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" :name="`items[${index}][planned_quantity]`" x-model="item.planned_quantity" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Jumlah disetujui</label>
                            <input type="number" min="0" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" :name="`items[${index}][approved_quantity]`" x-model="item.approved_quantity">
                        </div>

                        <div class="xl:col-span-4">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600" x-show="selectedMedicine(item.medicine_id)">
                                <span class="font-medium text-slate-900" x-text="selectedMedicine(item.medicine_id)?.code"></span>
                                <span x-text="' | ' + (selectedMedicine(item.medicine_id)?.category || '-')"></span>
                                <span x-text="' | ' + (selectedMedicine(item.medicine_id)?.unit || '-')"></span>
                            </div>
                        </div>

                        <div class="xl:col-span-4">
                            <label class="block text-sm font-medium text-slate-700">Catatan item</label>
                            <input type="text" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" :name="`items[${index}][notes]`" x-model="item.notes">
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </section>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('rko.header.index') }}" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</a>
        <button type="submit" class="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
            Simpan RKO
        </button>
    </div>
</div>
