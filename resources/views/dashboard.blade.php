<x-app-layout>
    <x-slot name="header">
        Dashboard
    </x-slot>

    <section class="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
        <div class="overflow-hidden rounded-[2rem] bg-slate-950 px-6 py-7 text-white shadow-xl shadow-slate-300/40">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-300">Ringkasan Hari Ini</p>
            <h3 class="mt-3 max-w-2xl text-3xl font-semibold leading-tight">
                Sistem gudang obat KB siap dipakai untuk mencatat stok masuk, distribusi, dan monitoring batch obat.
            </h3>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300">
                Fondasi aplikasi sudah siap sampai tahap autentikasi, database, seeder awal, model Eloquent, dan middleware hak akses.
                Langkah berikutnya tinggal melanjutkan modul master data dan transaksi stok.
            </p>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm text-slate-400">Role awal</p>
                    <p class="mt-2 text-2xl font-semibold">3</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm text-slate-400">Data obat contoh</p>
                    <p class="mt-2 text-2xl font-semibold">4</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm text-slate-400">Status panel</p>
                    <p class="mt-2 text-2xl font-semibold text-emerald-300">Siap lanjut</p>
                </div>
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Akun Aktif</p>
            <h3 class="mt-3 text-xl font-semibold text-slate-900">{{ Auth::user()->name }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ Auth::user()->email }}</p>

            <dl class="mt-6 space-y-4">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Role</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ Auth::user()->role?->name ?? 'Belum diatur' }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Status akun</dt>
                    <dd class="mt-1 font-semibold text-emerald-700">{{ Auth::user()->is_active ? 'Aktif' : 'Nonaktif' }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Login terakhir</dt>
                    <dd class="mt-1 font-semibold text-slate-900">
                        {{ optional(Auth::user()->last_login_at)->translatedFormat('d M Y H:i') ?? 'Belum tercatat' }}
                    </dd>
                </div>
            </dl>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-3">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Roadmap Aktif</p>
                    <h3 class="mt-2 text-xl font-semibold text-slate-900">Tahap implementasi yang sudah berjalan</h3>
                </div>
                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-800">
                    Step 9
                </span>
            </div>

            <div class="mt-6 space-y-4">
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4">
                    <p class="font-semibold text-emerald-900">Selesai</p>
                    <p class="mt-1 text-sm leading-6 text-emerald-800">
                        Analisis kebutuhan, setup Laravel, autentikasi, migration, seeder, model Eloquent, dan middleware role.
                    </p>
                </div>
                <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-4">
                    <p class="font-semibold text-sky-900">Sedang dikerjakan</p>
                    <p class="mt-1 text-sm leading-6 text-sky-800">
                        Penyusunan layout admin panel sebagai fondasi halaman dashboard, master data, transaksi, dan laporan.
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="font-semibold text-slate-900">Berikutnya</p>
                    <p class="mt-1 text-sm leading-6 text-slate-700">
                        Modul master data, form request validation, dan controller CRUD untuk obat, kategori, satuan, sumber, serta tujuan distribusi.
                    </p>
                </div>
            </div>
        </article>

        <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Quick Note</p>
            <h3 class="mt-2 text-xl font-semibold text-slate-900">Prinsip stok</h3>
            <ul class="mt-5 space-y-3 text-sm leading-6 text-slate-600">
                <li class="rounded-2xl bg-slate-50 px-4 py-3">Setiap penerimaan akan membentuk batch stok baru.</li>
                <li class="rounded-2xl bg-slate-50 px-4 py-3">Pengeluaran stok sebaiknya memakai FEFO, ambil batch terdekat expired lebih dulu.</li>
                <li class="rounded-2xl bg-slate-50 px-4 py-3">Adjustment hanya mengubah stok berjalan di batch, bukan menghapus histori transaksi.</li>
            </ul>
        </article>
    </section>
</x-app-layout>
