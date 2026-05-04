@php
    $initialItems = old('items');

    if (! is_array($initialItems)) {
        $initialItems = is_array($formItems) && $formItems !== []
            ? $formItems
            : [[
                'medicine_id' => '',
                'quantity' => 1,
                'notes' => '',
            ]];
    }
@endphp

@csrf

<div
    x-data='{
        items: @json($initialItems),
        addItem() {
            this.items.push({
                medicine_id: "",
                quantity: 1,
                notes: "",
            });
        },
        removeItem(index) {
            if (this.items.length === 1) {
                return;
            }

            this.items.splice(index, 1);
        },
    }'
    class="space-y-8"
>
    <section class="grid gap-6 lg:grid-cols-2">
        <div>
            <label for="distribution_number" class="block text-sm font-medium text-slate-700">Nomor distribusi</label>
            <input
                id="distribution_number"
                name="distribution_number"
                type="text"
                value="{{ old('distribution_number', $nextDistributionNumber) }}"
                class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                required
            >
        </div>

        <div>
            <label for="distributed_date" class="block text-sm font-medium text-slate-700">Tanggal distribusi</label>
            <input
                id="distributed_date"
                name="distributed_date"
                type="date"
                value="{{ old('distributed_date', optional($distribution->distributed_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                required
            >
        </div>

        <div>
            <label for="destination_id" class="block text-sm font-medium text-slate-700">Tujuan distribusi</label>
            <select
                id="destination_id"
                name="destination_id"
                class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                required
            >
                <option value="">Pilih tujuan distribusi</option>
                @foreach ($destinations as $destination)
                    <option value="{{ $destination->id }}" @selected((string) old('destination_id', $distribution->destination_id) === (string) $destination->id)>
                        {{ $destination->name }} ({{ $destination->destination_type }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
            <select
                id="status"
                name="status"
                class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                required
            >
                <option value="draft" @selected(old('status', $distribution->status) === 'draft')>Draft</option>
                <option value="posted" @selected(old('status', $distribution->status) === 'posted')>Posted</option>
            </select>
        </div>

        <div class="lg:col-span-2">
            <label for="notes" class="block text-sm font-medium text-slate-700">Catatan</label>
            <textarea
                id="notes"
                name="notes"
                rows="4"
                class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
            >{{ old('notes', $distribution->notes) }}</textarea>
        </div>
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-slate-50/70 p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Item Obat Keluar</h3>
                <p class="mt-1 text-sm text-slate-500">Isi data distribusi obat sesuai tujuan penyaluran dan jumlah yang akan dikirim.</p>
            </div>
            <button
                type="button"
                class="rounded-2xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100"
                @click="addItem()"
            >
                Tambah Item
            </button>
        </div>

        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Distribusi dengan status <strong>posted</strong> akan langsung tercatat sebagai realisasi penyaluran. Draft dapat dipakai untuk menyiapkan data sebelum diposting.
        </div>

        <div class="mt-6 space-y-4">
            <template x-for="(item, index) in items" :key="index">
                <div class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <p class="text-sm font-semibold text-slate-900" x-text="`Item ${index + 1}`"></p>
                        <button
                            type="button"
                            class="rounded-xl border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50"
                            @click="removeItem(index)"
                            x-show="items.length > 1"
                        >
                            Hapus
                        </button>
                    </div>

                    <div class="mt-4 grid gap-4 lg:grid-cols-2 xl:grid-cols-4">
                        <div class="xl:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Obat</label>
                            <select
                                class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                :name="`items[${index}][medicine_id]`"
                                x-model="item.medicine_id"
                                required
                            >
                                <option value="">Pilih obat</option>
                                @foreach ($medicines as $medicine)
                                    <option value="{{ $medicine->id }}">
                                        {{ $medicine->code }} - {{ $medicine->name }} | Stok: {{ number_format((int) ($medicine->available_stock ?? 0)) }} {{ $medicine->unit?->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Jumlah</label>
                            <input
                                type="number"
                                min="1"
                                class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                :name="`items[${index}][quantity]`"
                                x-model="item.quantity"
                                required
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Catatan item</label>
                            <input
                                type="text"
                                class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                :name="`items[${index}][notes]`"
                                x-model="item.notes"
                            >
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </section>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('faskes.distribusi.index') }}" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
            Batal
        </a>
        <button type="submit" class="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
            Simpan Transaksi
        </button>
    </div>
</div>
