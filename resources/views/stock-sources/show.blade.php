<x-app-layout>
    <x-slot name="header">Detail Sumber Pengadaan</x-slot>

    <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm text-slate-500">Nama sumber</p>
                    <h3 class="mt-2 text-2xl font-semibold text-slate-900">{{ $source->name }}</h3>
                </div>
                <a href="{{ route('pengadaan.sumber.edit', $source) }}" class="rounded-2xl border border-amber-300 px-4 py-2 text-sm font-medium text-amber-700 hover:bg-amber-50">Edit</a>
            </div>

            <dl class="mt-8 grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Jenis sumber</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ strtoupper($source->source_type) }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Contact person</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $source->contact_person ?: '-' }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Telepon</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $source->phone ?: '-' }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4 md:col-span-2">
                    <dt class="text-sm text-slate-500">Alamat</dt>
                    <dd class="mt-1 text-sm leading-7 text-slate-700">{{ $source->address ?: 'Belum ada alamat.' }}</dd>
                </div>
            </dl>
        </article>

        <aside class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="space-y-4">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Jumlah stok masuk terkait</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $source->stock_receipts_count }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Dibuat</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $source->created_at->format('d M Y H:i') }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Diperbarui</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $source->updated_at->format('d M Y H:i') }}</p>
                </div>
            </div>
        </aside>
    </section>
</x-app-layout>
