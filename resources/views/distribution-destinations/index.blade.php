<x-app-layout>
    <x-slot name="header">Faskes</x-slot>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <p class="text-sm text-slate-500">Kelola daftar fasilitas kesehatan atau pihak tujuan distribusi obat.</p>
            <a href="{{ route('faskes.create') }}" class="inline-flex rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Tambah Tujuan</a>
        </div>

        <form method="GET" action="{{ route('faskes.index') }}" class="mt-6 flex flex-col gap-3 xl:flex-row xl:items-center">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode, nama, atau contact person..." class="w-full min-w-0 flex-1 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
            <select name="type" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500 xl:w-48 xl:shrink-0">
                <option value="">Semua jenis</option>
                <option value="puskesmas" @selected($type === 'puskesmas')>Puskesmas</option>
                <option value="klinik" @selected($type === 'klinik')>Klinik</option>
                <option value="bidan" @selected($type === 'bidan')>Bidan</option>
                <option value="lainnya" @selected($type === 'lainnya')>Lainnya</option>
            </select>
            <select name="status" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500 xl:w-48 xl:shrink-0">
                <option value="">Semua status</option>
                <option value="active" @selected($status === 'active')>Aktif</option>
                <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
            </select>
            <button type="submit" class="shrink-0 rounded-2xl border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 xl:min-w-32">Filter</button>
        </form>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
            <table class="min-w-[920px] w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Kode</th>
                        <th class="px-4 py-3 font-semibold">Nama</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Jenis</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($destinations as $destination)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{{ $destination->code }}</td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-900">{{ $destination->name }}</p>
                                <p class="text-xs text-slate-500">{{ $destination->contact_person ?: '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ ucfirst($destination->destination_type) }}</td>
                            <td class="px-4 py-3">
                                <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold {{ $destination->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $destination->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2 whitespace-nowrap">
                                    <a href="{{ route('faskes.show', $destination) }}" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">Detail</a>
                                    <a href="{{ route('faskes.edit', $destination) }}" class="rounded-xl border border-amber-300 px-3 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-50">Edit</a>
                                    <form method="POST" action="{{ route('faskes.destroy', $destination) }}" onsubmit="return confirm('Hapus tujuan distribusi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-xl border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada data tujuan distribusi.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        <div class="mt-6">{{ $destinations->links() }}</div>
    </section>
</x-app-layout>
