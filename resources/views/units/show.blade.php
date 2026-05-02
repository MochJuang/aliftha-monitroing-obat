<x-app-layout>
    <x-slot name="header">Detail Satuan</x-slot>

    <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm text-slate-500">Nama satuan</p>
                    <h3 class="mt-2 text-2xl font-semibold text-slate-900">{{ $unit->name }}</h3>
                </div>
                <a href="{{ route('master-obat.satuan.edit', $unit) }}" class="rounded-2xl border border-amber-300 px-4 py-2 text-sm font-medium text-amber-700 hover:bg-amber-50">Edit</a>
            </div>

            <div class="mt-8 rounded-2xl bg-slate-50 px-4 py-4">
                <p class="text-sm text-slate-500">Simbol satuan</p>
                <p class="mt-1 text-lg font-semibold text-slate-900">{{ $unit->symbol }}</p>
            </div>
        </article>

        <aside class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="space-y-4">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Jumlah obat terkait</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $unit->medicines_count }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Dibuat</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $unit->created_at->format('d M Y H:i') }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Diperbarui</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $unit->updated_at->format('d M Y H:i') }}</p>
                </div>
            </div>
        </aside>
    </section>
</x-app-layout>
