@php
    $initialItems = old('items', [[
        'batch_id' => '',
        'actual_qty' => 0,
        'reason' => '',
    ]]);

    $batchOptions = $batches->map(fn ($batch) => [
        'id' => $batch->id,
        'medicine' => $batch->medicine->name,
        'medicine_code' => $batch->medicine->code,
        'unit' => $batch->medicine->unit?->name,
        'batch_number' => $batch->batch_number,
        'expired_at' => optional($batch->expired_at)->format('Y-m-d'),
        'qty_remaining' => $batch->qty_remaining,
        'label' => sprintf(
            '%s - %s | Batch %s | Exp %s | Sistem %s %s',
            $batch->medicine->code,
            $batch->medicine->name,
            $batch->batch_number,
            optional($batch->expired_at)->format('d M Y'),
            number_format($batch->qty_remaining),
            $batch->medicine->unit?->name ?? ''
        ),
    ])->values();
@endphp

@csrf

<div
    x-data='{
        items: @json($initialItems),
        batches: @json($batchOptions),
        addItem() {
            this.items.push({
                batch_id: "",
                actual_qty: 0,
                reason: "",
            });
        },
        removeItem(index) {
            if (this.items.length === 1) {
                return;
            }

            this.items.splice(index, 1);
        },
        findBatch(batchId) {
            return this.batches.find((batch) => String(batch.id) === String(batchId));
        },
        systemQty(batchId) {
            const batch = this.findBatch(batchId);
            return batch ? batch.qty_remaining : 0;
        },
        difference(batchId, actualQty) {
            const batch = this.findBatch(batchId);
            if (! batch) {
                return 0;
            }

            return Number(actualQty || 0) - Number(batch.qty_remaining || 0);
        },
    }'
    class="space-y-8"
>
    <section class="grid gap-6 lg:grid-cols-2">
        <div>
            <label for="adjustment_number" class="block text-sm font-medium text-slate-700">Nomor adjustment</label>
            <input
                id="adjustment_number"
                name="adjustment_number"
                type="text"
                value="{{ old('adjustment_number', $nextAdjustmentNumber) }}"
                class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                required
            >
        </div>

        <div>
            <label for="adjustment_date" class="block text-sm font-medium text-slate-700">Tanggal adjustment</label>
            <input
                id="adjustment_date"
                name="adjustment_date"
                type="date"
                value="{{ old('adjustment_date', optional($adjustment->adjustment_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                required
            >
        </div>

        <div>
            <label for="adjustment_type" class="block text-sm font-medium text-slate-700">Jenis adjustment</label>
            <select
                id="adjustment_type"
                name="adjustment_type"
                class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                required
            >
                <option value="opname" @selected(old('adjustment_type', $adjustment->adjustment_type) === 'opname')>Opname</option>
                <option value="koreksi" @selected(old('adjustment_type', $adjustment->adjustment_type) === 'koreksi')>Koreksi</option>
                <option value="expired" @selected(old('adjustment_type', $adjustment->adjustment_type) === 'expired')>Expired</option>
                <option value="rusak" @selected(old('adjustment_type', $adjustment->adjustment_type) === 'rusak')>Rusak</option>
            </select>
        </div>

        <div class="lg:col-span-2">
            <label for="notes" class="block text-sm font-medium text-slate-700">Catatan</label>
            <textarea
                id="notes"
                name="notes"
                rows="4"
                class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
            >{{ old('notes', $adjustment->notes) }}</textarea>
        </div>
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-slate-50/70 p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Batch yang Disesuaikan</h3>
                <p class="mt-1 text-sm text-slate-500">Pilih batch, isi stok fisik aktual, lalu sistem akan mencatat selisih dan memperbarui stok batch tersebut.</p>
            </div>
            <button
                type="button"
                class="rounded-2xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100"
                @click="addItem()"
            >
                Tambah Batch
            </button>
        </div>

        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Adjustment langsung memperbarui <strong>qty_remaining</strong> sesuai stok fisik yang kamu input. Nilai selisih akan tersimpan sebagai jejak audit.
        </div>

        <div class="mt-6 space-y-4">
            <template x-for="(item, index) in items" :key="index">
                <div class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <p class="text-sm font-semibold text-slate-900" x-text="`Batch ${index + 1}`"></p>
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
                            <label class="block text-sm font-medium text-slate-700">Batch</label>
                            <select
                                class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                :name="`items[${index}][batch_id]`"
                                x-model="item.batch_id"
                                required
                            >
                                <option value="">Pilih batch</option>
                                <template x-for="batch in batches" :key="batch.id">
                                    <option :value="batch.id" x-text="batch.label"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Stok sistem</label>
                            <div class="mt-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700">
                                <span x-text="systemQty(item.batch_id)"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Stok aktual</label>
                            <input
                                type="number"
                                min="0"
                                class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                :name="`items[${index}][actual_qty]`"
                                x-model="item.actual_qty"
                                required
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Selisih</label>
                            <div class="mt-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-medium"
                                :class="difference(item.batch_id, item.actual_qty) > 0 ? 'text-emerald-700' : (difference(item.batch_id, item.actual_qty) < 0 ? 'text-rose-700' : 'text-slate-700')"
                            >
                                <span x-text="difference(item.batch_id, item.actual_qty)"></span>
                            </div>
                        </div>

                        <div class="xl:col-span-3">
                            <label class="block text-sm font-medium text-slate-700">Alasan / catatan item</label>
                            <input
                                type="text"
                                class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                :name="`items[${index}][reason]`"
                                x-model="item.reason"
                            >
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </section>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('stock-adjustments.index') }}" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
            Batal
        </a>
        <button type="submit" class="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
            Simpan Adjustment
        </button>
    </div>
</div>
