<x-app-layout>
    <x-slot name="header">Detail Obat</x-slot>

    <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm text-slate-500">Kode obat</p>
                    <h3 class="mt-2 text-2xl font-semibold text-slate-900">{{ $medicine->code }}</h3>
                    <p class="mt-2 text-sm text-slate-500">{{ $medicine->brand ?: 'Tanpa merek' }}</p>
                </div>
                <a href="{{ route('master-obat.obat.edit', $medicine) }}" class="rounded-2xl border border-amber-300 px-4 py-2 text-sm font-medium text-amber-700 hover:bg-amber-50">Edit</a>
            </div>

            <dl class="mt-8 grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Nama obat</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $medicine->name }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Jenis obat</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $medicine->medicine_type ?: '-' }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Kategori</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $medicine->category->name }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Satuan</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $medicine->unit->name }} ({{ $medicine->unit->symbol }})</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Dosis / kemasan</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $medicine->dosage ?: '-' }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Stok minimum</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ number_format($medicine->minimum_stock) }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Harga standar</dt>
                    <dd class="mt-1 font-semibold text-slate-900">Rp {{ number_format((float) $medicine->standard_price, 0, ',', '.') }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Status</dt>
                    <dd class="mt-1 font-semibold {{ $medicine->is_active ? 'text-emerald-700' : 'text-slate-600' }}">{{ $medicine->is_active ? 'Aktif' : 'Nonaktif' }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4 md:col-span-2">
                    <dt class="text-sm text-slate-500">Deskripsi</dt>
                    <dd class="mt-1 text-sm leading-7 text-slate-700">{{ $medicine->description ?: 'Belum ada deskripsi.' }}</dd>
                </div>
            </dl>
        </article>

        <aside class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="space-y-4">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Dibuat</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $medicine->created_at->format('d M Y H:i') }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Diperbarui</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $medicine->updated_at->format('d M Y H:i') }}</p>
                </div>
            </div>
        </aside>
    </section>
</x-app-layout>
