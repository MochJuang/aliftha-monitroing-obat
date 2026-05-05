<x-app-layout>
    <x-slot name="header">Satuan</x-slot>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <p class="text-sm text-slate-500">Satuan dipakai sebagai acuan jumlah obat seperti strip, vial, atau set.</p>
            <a href="{{ route('master-obat.satuan.create') }}" class="inline-flex rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Tambah Satuan</a>
        </div>

	        <form method="GET" action="{{ route('master-obat.satuan.index') }}" class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
	            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau simbol satuan..." class="w-full min-w-0 flex-1 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
	            <button type="submit" class="shrink-0 rounded-2xl border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 sm:min-w-28">Cari</button>
	        </form>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
            <table class="min-w-[720px] w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Nama</th>
                        <th class="px-4 py-3 font-semibold">Simbol</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Dibuat</th>
                        <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($units as $unit)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{{ $unit->name }}</td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $unit->symbol }}</td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $unit->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2 whitespace-nowrap">
                                    <a href="{{ route('master-obat.satuan.show', $unit) }}" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">Detail</a>
                                    <a href="{{ route('master-obat.satuan.edit', $unit) }}" class="rounded-xl border border-amber-300 px-3 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-50">Edit</a>
                                    <form method="POST" action="{{ route('master-obat.satuan.destroy', $unit) }}" onsubmit="return confirm('Hapus satuan ini?')">
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
        </div>

        <div class="mt-6">{{ $units->links() }}</div>
    </section>
</x-app-layout>
