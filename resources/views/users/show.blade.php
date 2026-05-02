<x-app-layout>
    <x-slot name="header">Detail Pengguna</x-slot>

    <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm text-slate-500">Profil pengguna</p>
                    <h3 class="mt-2 text-2xl font-semibold text-slate-900">{{ $user->name }}</h3>
                    <p class="mt-2 text-sm text-slate-500">{{ $user->email }}</p>
                </div>
                <a href="{{ route('users.edit', $user) }}" class="rounded-2xl border border-amber-300 px-4 py-2 text-sm font-medium text-amber-700 hover:bg-amber-50">
                    Edit Pengguna
                </a>
            </div>

            <dl class="mt-8 grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Username</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $user->username }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Role</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $user->role?->name ?? '-' }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Telepon</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $user->phone ?: '-' }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Status akun</dt>
                    <dd class="mt-1 font-semibold {{ $user->is_active ? 'text-emerald-700' : 'text-slate-700' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Login terakhir</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ optional($user->last_login_at)->format('d M Y H:i') ?? 'Belum tercatat' }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Dibuat pada</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $user->created_at->format('d M Y H:i') }}</dd>
                </div>
            </dl>
        </article>

        <aside class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-500">Ringkasan aktivitas</p>

            <div class="mt-4 space-y-4">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Stok masuk dibuat</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($user->stock_receipts_count) }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Stok keluar dibuat</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($user->stock_distributions_count) }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Penyesuaian stok dibuat</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($user->stock_adjustments_count) }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-sm text-slate-500">Log aktivitas</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format($user->activity_logs_count) }}</p>
                </div>
            </div>
        </aside>
    </section>
</x-app-layout>
