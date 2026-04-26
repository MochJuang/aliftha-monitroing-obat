<x-app-layout>
    <x-slot name="header">Satuan</x-slot>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <p class="text-sm text-slate-500">Satuan dipakai sebagai acuan jumlah obat seperti strip, vial, atau set.</p>
            <a href="{{ route('units.create') }}" class="inline-flex rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Tambah Satuan</a>
        </div>

        <form method="GET" action="{{ route('units.index') }}" class="mt-6 grid gap-3 md:grid-cols-[1fr_auto]">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau simbol satuan..." class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
            <button type="submit" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cari</button>
        </form>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Nama</th>
                        <th class="px-4 py-3 font-semibold">Simbol</th>
                        <th class="px-4 py-3 font-semibold">Dibuat</th>
                        <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($units as $unit)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $unit->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $unit->symbol }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $unit->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('units.show', $unit) }}" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">Detail</a>
                                    <a href="{{ route('units.edit', $unit) }}" class="rounded-xl border border-amber-300 px-3 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-50">Edit</a>
                                    <form method="POST" action="{{ route('units.destroy', $unit) }}" onsubmit="return confirm('Hapus satuan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-xl border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada data satuan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $units->links() }}</div>
    </section>
</x-app-layout>
