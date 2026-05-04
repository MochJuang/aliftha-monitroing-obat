<x-app-layout>
    <x-slot name="header">Sumber Pengadaan</x-slot>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <p class="text-sm text-slate-500">Kelola sumber penerimaan obat seperti BKKBN, Dinkes, atau supplier.</p>
            <a href="{{ route('pengadaan.sumber.create') }}" class="inline-flex rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Tambah Sumber</a>
        </div>

        <form method="GET" action="{{ route('pengadaan.sumber.index') }}" class="mt-6 flex flex-col gap-3 xl:flex-row xl:items-center">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau contact person..." class="w-full min-w-0 flex-1 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
            <select name="type" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500 xl:w-48 xl:shrink-0">
                <option value="">Semua jenis</option>
                <option value="dinkes" @selected($type === 'dinkes')>Dinkes</option>
                <option value="bkkbn" @selected($type === 'bkkbn')>BKKBN</option>
                <option value="supplier" @selected($type === 'supplier')>Supplier</option>
                <option value="lainnya" @selected($type === 'lainnya')>Lainnya</option>
            </select>
            <button type="submit" class="shrink-0 rounded-2xl border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 xl:min-w-32">Filter</button>
        </form>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
            <table class="min-w-[900px] w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Nama</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Jenis</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Contact person</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Telepon</th>
                        <th class="px-4 py-3 font-semibold">Keterangan</th>
                        <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($sources as $source)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $source->name }}</td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ strtoupper($source->source_type) }}</td>
                            <td class="px-4 py-3">
                                <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold {{ $source->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $source->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $source->contact_person ?: '-' }}</td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $source->phone ?: '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $source->notes ?: '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2 whitespace-nowrap">
                                    <a href="{{ route('pengadaan.sumber.show', $source) }}" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">Detail</a>
                                    <a href="{{ route('pengadaan.sumber.edit', $source) }}" class="rounded-xl border border-amber-300 px-3 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-50">Edit</a>
                                    <form method="POST" action="{{ route('pengadaan.sumber.destroy', $source) }}" onsubmit="return confirm('Hapus sumber obat ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-xl border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada data sumber obat.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        <div class="mt-6">{{ $sources->links() }}</div>
    </section>
</x-app-layout>
