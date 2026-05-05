@php
    $initialItems = old('items', $formItems ?? [[
        'medicine_id' => '',
        'quantity' => 1,
        'notes' => '',
    ]]);

    $medicineOptions = $medicines->map(fn ($medicine) => [
        'id' => $medicine->id,
        'label' => sprintf(
            '%s - %s%s',
            $medicine->code,
            $medicine->name,
            $medicine->unit?->name ? ' ('.$medicine->unit->name.')' : ''
        ),
    ])->values();
@endphp

<div
    x-data='{
        items: @json($initialItems),
        medicines: @json($medicineOptions),
        addItem() {
            this.items.push({ medicine_id: "", quantity: 1, notes: "" });
        },
        removeItem(index) {
            if (this.items.length === 1) return;
            this.items.splice(index, 1);
        }
    }'
    class="space-y-6"
>
<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label for="mutation_number" class="block text-sm font-medium text-slate-700">Nomor mutasi</label>
        <input id="mutation_number" type="text" name="mutation_number" value="{{ old('mutation_number', $mutation->mutation_number) }}" class="mt-1 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
        @error('mutation_number') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="mutation_date" class="block text-sm font-medium text-slate-700">Tanggal mutasi</label>
        <input id="mutation_date" type="date" name="mutation_date" value="{{ old('mutation_date', optional($mutation->mutation_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="mt-1 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
        @error('mutation_date') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="mutation_type" class="block text-sm font-medium text-slate-700">Jenis mutasi</label>
        <select id="mutation_type" name="mutation_type" class="mt-1 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
            <option value="MASUK" @selected(old('mutation_type', $mutation->mutation_type) === 'MASUK')>MASUK</option>
            <option value="KELUAR" @selected(old('mutation_type', $mutation->mutation_type) === 'KELUAR')>KELUAR</option>
        </select>
        @error('mutation_type') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="distribution_destination_id" class="block text-sm font-medium text-slate-700">Tujuan distribusi (opsional)</label>
        <select id="distribution_destination_id" name="distribution_destination_id" class="mt-1 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
            <option value="">Tanpa tujuan distribusi</option>
            @foreach ($destinations as $destination)
                <option value="{{ $destination->id }}" @selected((string) old('distribution_destination_id', $mutation->distribution_destination_id) === (string) $destination->id)>
                    {{ $destination->name }}{{ $destination->destination_type ? ' ('.ucfirst($destination->destination_type).')' : '' }}
                </option>
            @endforeach
        </select>
        @error('distribution_destination_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label for="reference" class="block text-sm font-medium text-slate-700">Referensi</label>
        <input id="reference" type="text" name="reference" value="{{ old('reference', $mutation->reference) }}" placeholder="Contoh: Mutasi stok Mei 2026" class="mt-1 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
        @error('reference') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label for="notes" class="block text-sm font-medium text-slate-700">Keterangan</label>
        <textarea id="notes" name="notes" rows="4" class="mt-1 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" placeholder="Catatan tambahan...">{{ old('notes', $mutation->notes) }}</textarea>
        @error('notes') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
</div>

<section class="rounded-[1.75rem] border border-slate-200 bg-slate-50/70 p-5">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Detail Item Mutasi</h3>
            <p class="mt-1 text-sm text-slate-500">Tambahkan satu atau lebih item obat untuk transaksi mutasi ini.</p>
        </div>
        <button type="button" class="rounded-2xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100" @click="addItem()">Tambah Item</button>
    </div>

    <div class="mt-5 space-y-4">
        <template x-for="(item, index) in items" :key="index">
            <div class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <p class="text-sm font-semibold text-slate-900" x-text="`Item ${index + 1}`"></p>
                    <button type="button" class="rounded-xl border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50" @click="removeItem(index)" x-show="items.length > 1">Hapus</button>
                </div>

                <div class="mt-4 grid gap-4 lg:grid-cols-[minmax(0,2fr)_180px_minmax(0,1fr)]">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Obat</label>
                        <select class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" :name="`items[${index}][medicine_id]`" x-model="item.medicine_id" required>
                            <option value="">Pilih obat</option>
                            <template x-for="medicine in medicines" :key="medicine.id">
                                <option :value="medicine.id" x-text="medicine.label"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Jumlah</label>
                        <input type="number" min="1" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" :name="`items[${index}][quantity]`" x-model="item.quantity" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Catatan item</label>
                        <input type="text" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" :name="`items[${index}][notes]`" x-model="item.notes">
                    </div>
                </div>
            </div>
        </template>
    </div>
</section>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('transaksi.mutasi.index') }}" class="rounded-2xl border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</a>
    <button type="submit" class="rounded-2xl bg-slate-950 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-800">Simpan Mutasi</button>
</div>
</div>
