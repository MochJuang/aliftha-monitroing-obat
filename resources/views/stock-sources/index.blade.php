<x-app-layout>
    <x-slot name="header">Sumber Obat</x-slot>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <p class="text-sm text-slate-500">Kelola sumber penerimaan obat seperti BKKBN, Dinkes, atau supplier.</p>
            <a href="{{ route('stock-sources.create') }}" class="inline-flex rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Tambah Sumber</a>
        </div>

        <form method="GET" action="{{ route('stock-sources.index') }}" class="mt-6 grid gap-3 lg:grid-cols-[1fr_180px_auto]">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau contact person..." class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
            <select name="type" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <option value="">Semua jenis</option>
                <option value="dinkes" @selected($type === 'dinkes')>Dinkes</option>
                <option value="bkkbn" @selected($type === 'bkkbn')>BKKBN</option>
                <option value="supplier" @selected($type === 'supplier')>Supplier</option>
                <option value="lainnya" @selected($type === 'lainnya')>Lainnya</option>
            </select>
            <button type="submit" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Filter</button>
        </form>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Nama</th>
                        <th class="px-4 py-3 font-semibold">Jenis</th>
                        <th class="px-4 py-3 font-semibold">Contact person</th>
                        <th class="px-4 py-3 font-semibold">Telepon</th>
                        <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($sources as $source)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $source->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ strtoupper($source->source_type) }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $source->contact_person ?: '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $source->phone ?: '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('stock-sources.show', $source) }}" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">Detail</a>
                                    <a href="{{ route('stock-sources.edit', $source) }}" class="rounded-xl border border-amber-300 px-3 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-50">Edit</a>
                                    <form method="POST" action="{{ route('stock-sources.destroy', $source) }}" onsubmit="return confirm('Hapus sumber obat ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-xl border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada data sumber obat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $sources->links() }}</div>
    </section>
</x-app-layout>
