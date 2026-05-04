@php
    $initialItems = old('items');

    if (! is_array($initialItems)) {
        $initialItems = $receipt->exists
            ? $receipt->items->map(fn ($item) => [
                'medicine_id' => $item->medicine_id,
                'expired_at' => optional($item->expired_at)->format('Y-m-d'),
                'quantity' => $item->quantity,
                'unit_cost' => $item->unit_cost,
                'notes' => $item->notes,
            ])->values()->all()
            : [[
                'medicine_id' => '',
                'expired_at' => '',
                'quantity' => 1,
                'unit_cost' => 0,
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
                expired_at: "",
                quantity: 1,
                unit_cost: 0,
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
            <label for="receipt_number" class="block text-sm font-medium text-slate-700">Nomor penerimaan</label>
            <input
                id="receipt_number"
                name="receipt_number"
                type="text"
                value="{{ old('receipt_number', $nextReceiptNumber) }}"
                class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                required
            >
        </div>

        <div>
            <label for="received_date" class="block text-sm font-medium text-slate-700">Tanggal penerimaan</label>
            <input
                id="received_date"
                name="received_date"
                type="date"
                value="{{ old('received_date', optional($receipt->received_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                required
            >
        </div>

        <div>
            <label for="source_id" class="block text-sm font-medium text-slate-700">Sumber obat</label>
            <select
                id="source_id"
                name="source_id"
                class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                required
            >
                <option value="">Pilih sumber obat</option>
                @foreach ($sources as $source)
                    <option value="{{ $source->id }}" @selected((string) old('source_id', $receipt->source_id) === (string) $source->id)>
                        {{ $source->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="rko_header_id" class="block text-sm font-medium text-slate-700">Referensi RKO</label>
            <select
                id="rko_header_id"
                name="rko_header_id"
                class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
            >
                <option value="">Tanpa referensi RKO</option>
                @foreach ($rkoHeaders as $header)
                    <option value="{{ $header->id }}" @selected((string) old('rko_header_id', $receipt->rko_header_id) === (string) $header->id)>
                        {{ $header->rko_number }} - {{ sprintf('%02d', $header->period_month) }}/{{ $header->period_year }}
                    </option>
                @endforeach
            </select>
            <p class="mt-2 text-xs text-slate-500">Pilih dokumen RKO jika realisasi pengadaan ini merupakan tindak lanjut dari rencana kebutuhan obat tertentu.</p>
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
            <select
                id="status"
                name="status"
                class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                required
            >
                <option value="draft" @selected(old('status', $receipt->status) === 'draft')>Draft</option>
                <option value="posted" @selected(old('status', $receipt->status) === 'posted')>Posted</option>
            </select>
        </div>

        <div class="lg:col-span-2">
            <label for="notes" class="block text-sm font-medium text-slate-700">Catatan</label>
            <textarea
                id="notes"
                name="notes"
                rows="4"
                class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
            >{{ old('notes', $receipt->notes) }}</textarea>
        </div>
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-slate-50/70 p-5">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Item Obat Masuk</h3>
                <p class="mt-1 text-sm text-slate-500">Isi daftar obat yang diterima beserta jumlah dan informasi pendukung transaksi.</p>
            </div>
            <button
                type="button"
                class="rounded-2xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100"
                @click="addItem()"
            >
                Tambah Item
            </button>
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

                    <div class="mt-4 grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
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
                                    <option value="{{ $medicine->id }}">{{ $medicine->code }} - {{ $medicine->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Tanggal expired</label>
                            <input
                                type="date"
                                class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                :name="`items[${index}][expired_at]`"
                                x-model="item.expired_at"
                                required
                            >
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
                            <label class="block text-sm font-medium text-slate-700">Harga satuan</label>
                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                :name="`items[${index}][unit_cost]`"
                                x-model="item.unit_cost"
                            >
                        </div>

                        <div class="xl:col-span-3">
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
        <a href="{{ route('pengadaan.index') }}" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
            Batal
        </a>
        <button type="submit" class="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
            Simpan Transaksi
        </button>
    </div>
</div>
