<x-app-layout>
    <x-slot name="header">Detail Sumber Dana</x-slot>

    <section class="grid gap-6 xl:grid-cols-[1fr_0.9fr]">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm text-slate-500">Kode sumber dana</p>
                    <h3 class="mt-2 text-2xl font-semibold text-slate-900">{{ $fundingSource->code }}</h3>
                    <p class="mt-2 text-sm text-slate-500">{{ $fundingSource->name }}</p>
                </div>
                <a href="{{ route('rko.sumber-dana.edit', $fundingSource) }}" class="rounded-2xl border border-amber-300 px-4 py-2 text-sm font-medium text-amber-700 hover:bg-amber-50">Edit</a>
            </div>

            <dl class="mt-8 grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Nama</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $fundingSource->name }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Jenis sumber</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $fundingSource->source_type ?: '-' }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Status</dt>
                    <dd class="mt-1 font-semibold {{ $fundingSource->is_active ? 'text-emerald-700' : 'text-slate-700' }}">{{ $fundingSource->is_active ? 'Aktif' : 'Nonaktif' }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4 md:col-span-2">
                    <dt class="text-sm text-slate-500">Catatan</dt>
                    <dd class="mt-1 text-sm leading-7 text-slate-700">{{ $fundingSource->notes ?: 'Belum ada catatan sumber dana.' }}</dd>
                </div>
            </dl>
        </article>

        <aside class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">Ringkasan penggunaan</p>

            <div class="mt-4 space-y-4">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Dokumen RKO</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($fundingSource->rko_headers_count) }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Realisasi pengadaan</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($fundingSource->procurement_realizations_count) }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Dibuat</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $fundingSource->created_at->format('d M Y H:i') }}</p>
                </div>
            </div>
        </aside>
    </section>
</x-app-layout>
