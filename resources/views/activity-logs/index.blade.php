<x-app-layout>
    <x-slot name="header">Log Aktivitas</x-slot>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total log</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($summary['total_logs']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Log hari ini</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($summary['today_logs']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-sky-200 bg-sky-50 p-5 shadow-sm">
            <p class="text-sm text-sky-800">Pengguna tercatat</p>
            <p class="mt-2 text-3xl font-semibold text-sky-900">{{ number_format($summary['unique_users']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <p class="text-sm text-amber-800">Modul tercatat</p>
            <p class="mt-2 text-3xl font-semibold text-amber-900">{{ number_format($summary['unique_modules']) }}</p>
        </article>
    </section>

    <section class="mt-6 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Audit Aktivitas Sistem</h3>
            <p class="mt-1 text-sm text-slate-500">Jejak aksi pengguna pada modul aplikasi, lengkap dengan waktu dan alamat IP yang tercatat.</p>
        </div>

        <form method="GET" action="{{ route('activity-logs.index') }}" class="mt-6 grid gap-3 xl:grid-cols-[minmax(0,2fr)_180px_180px_minmax(0,1.2fr)_180px_180px_140px] xl:items-end">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari deskripsi, user, atau IP..." class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
            <select name="module" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <option value="">Semua modul</option>
                @foreach ($modules as $moduleOption)
                    <option value="{{ $moduleOption }}" @selected($module === $moduleOption)>{{ $moduleOption }}</option>
                @endforeach
            </select>
            <select name="action" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <option value="">Semua aksi</option>
                @foreach ($actions as $actionOption)
                    <option value="{{ $actionOption }}" @selected($action === $actionOption)>{{ $actionOption }}</option>
                @endforeach
            </select>
            <select name="user_id" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <option value="">Semua pengguna</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected($userId === (string) $user->id)>{{ $user->name }} ({{ $user->username }})</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
            <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
            <button type="submit" class="rounded-2xl border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Filter</button>
        </form>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-[1320px] w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Waktu</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Pengguna</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Modul</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Aksi</th>
                            <th class="px-4 py-3 font-semibold">Deskripsi</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($logs as $log)
                            <tr>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-900">{{ $log->user?->name ?? 'Sistem' }}</p>
                                    <p class="text-xs text-slate-500">{{ $log->user?->username ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $log->module }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $log->action }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $log->description ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $log->ip_address ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada log aktivitas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $logs->links() }}
        </div>
    </section>
</x-app-layout>
