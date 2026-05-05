<x-app-layout>
    <x-slot name="header">Pengguna</x-slot>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <p class="text-sm text-slate-500">Kelola akun admin, petugas gudang, dan pimpinan yang menggunakan aplikasi internal ini.</p>
            <a href="{{ route('users.create') }}" class="inline-flex rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                Tambah Pengguna
            </a>
        </div>

	        <div class="mt-6 overflow-x-auto">
	            <form method="GET" action="{{ route('users.index') }}" class="flex flex-nowrap items-end gap-3 min-w-max">
	            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, username, atau email..." class="min-w-[260px] flex-1 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
	            <select name="role_id" class="w-52 shrink-0 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
	                <option value="">Semua role</option>
	                @foreach ($roles as $role)
	                    <option value="{{ $role->id }}" @selected($roleId === (string) $role->id)>{{ $role->name }}</option>
	                @endforeach
	            </select>
	            <select name="status" class="w-44 shrink-0 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
	                <option value="">Semua status</option>
	                <option value="active" @selected($status === 'active')>Aktif</option>
	                <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
	            </select>
	            <button type="submit" class="w-28 shrink-0 rounded-2xl border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Filter</button>
	            </form>
	        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-[1180px] w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Nama</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Username</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Email</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Role</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Transaksi</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Login terakhir</th>
                            <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-900">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $user->phone ?: '-' }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $user->username }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $user->email }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $user->role?->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold {{ $user->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                    {{ number_format($user->stock_mutations_count) }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ optional($user->last_login_at)->format('d M Y H:i') ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2 whitespace-nowrap">
                                        <a href="{{ route('users.show', $user) }}" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">Detail</a>
                                        <a href="{{ route('users.edit', $user) }}" class="rounded-xl border border-amber-300 px-3 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-50">Edit</a>
                                        <form method="POST" action="{{ route('users.toggle-status', $user) }}" onsubmit="return confirm('{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun ini?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-xl border {{ $user->is_active ? 'border-rose-300 text-rose-700 hover:bg-rose-50' : 'border-emerald-300 text-emerald-700 hover:bg-emerald-50' }} px-3 py-1.5 text-xs font-medium">
                                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-8 text-center text-slate-500">Belum ada pengguna.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $users->links() }}</div>
    </section>
</x-app-layout>
