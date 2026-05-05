<x-app-layout>
    <x-slot name="header">
        Dashboard
    </x-slot>

    <section class="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
        <div class="overflow-hidden rounded-[2rem] bg-slate-950 px-6 py-7 text-white shadow-xl shadow-slate-300/40">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-300">Ringkasan Hari Ini</p>
            <h3 class="mt-3 max-w-2xl text-3xl font-semibold leading-tight">
                Dashboard ini merangkum posisi stok dan mutasi obat langsung dari data aplikasi.
            </h3>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300">
                Petugas bisa memantau stok berjalan, kebutuhan yang menipis, dan aktivitas mutasi masuk maupun keluar dari satu halaman utama.
            </p>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm text-slate-400">Mutasi masuk hari ini</p>
                    <p class="mt-2 text-2xl font-semibold">{{ number_format($todayMovements['receipts_qty']) }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ number_format($todayMovements['receipts_count']) }} transaksi</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm text-slate-400">Mutasi keluar hari ini</p>
                    <p class="mt-2 text-2xl font-semibold">{{ number_format($todayMovements['distributions_qty']) }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ number_format($todayMovements['distributions_count']) }} transaksi</p>
                </div>
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Akun Aktif</p>
            <h3 class="mt-3 text-xl font-semibold text-slate-900">{{ $activeUser->name }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ $activeUser->email }}</p>

            <dl class="mt-6 space-y-4">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Role</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $activeUser->role?->name ?? 'Belum diatur' }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Status akun</dt>
                    <dd class="mt-1 font-semibold text-emerald-700">{{ $activeUser->is_active ? 'Aktif' : 'Nonaktif' }}</dd>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <dt class="text-sm text-slate-500">Login terakhir</dt>
                    <dd class="mt-1 font-semibold text-slate-900">
                        {{ optional($activeUser->last_login_at)->translatedFormat('d M Y H:i') ?? 'Belum tercatat' }}
                    </dd>
                </div>
            </dl>
        </div>
    </section>

    <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Obat aktif</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($summary['active_medicines']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Stok berjalan</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($summary['total_current_stock']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <p class="text-sm text-amber-800">Stok menipis</p>
            <p class="mt-2 text-3xl font-semibold text-amber-900">{{ number_format($summary['low_stock_medicines']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <p class="text-sm text-rose-800">Stok habis</p>
            <p class="mt-2 text-3xl font-semibold text-rose-900">{{ number_format($summary['empty_stock_medicines']) }}</p>
        </article>
    </section>

    <section class="mt-6 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">RKO Snapshot</p>
                <h3 class="mt-2 text-xl font-semibold text-slate-900">Perbandingan rencana dan realisasi pengadaan</h3>
            </div>
            <a href="{{ route('laporan.rko') }}" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-700">
                Buka Laporan RKO
            </a>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm text-slate-500">Dokumen RKO</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($rkoSummary['total_headers']) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ number_format($rkoSummary['approved_headers']) }} dokumen disetujui</p>
            </article>
            <article class="rounded-[1.5rem] border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm text-amber-800">Total disetujui</p>
                <p class="mt-2 text-3xl font-semibold text-amber-900">{{ number_format($rkoSummary['total_approved_qty']) }}</p>
            </article>
            <article class="rounded-[1.5rem] border border-sky-200 bg-sky-50 p-4">
                <p class="text-sm text-sky-800">Total realisasi</p>
                <p class="mt-2 text-3xl font-semibold text-sky-900">{{ number_format($rkoSummary['total_realized_qty']) }}</p>
            </article>
            <article class="rounded-[1.5rem] border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-sm text-emerald-800">Cakupan realisasi</p>
                <p class="mt-2 text-3xl font-semibold text-emerald-900">{{ number_format($rkoSummary['coverage_percent'], 1) }}%</p>
            </article>
        </div>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-3">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Peringatan Operasional</p>
                    <h3 class="mt-2 text-xl font-semibold text-slate-900">Obat yang butuh perhatian cepat</h3>
                </div>
                <a href="{{ route('monitoring.stok.index') }}" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-700">
                    Lihat Monitoring
                </a>
            </div>

            <div class="mt-6 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                <p class="font-semibold text-slate-900">Stok Menipis</p>
                <div class="mt-3 space-y-3">
                    @forelse ($lowStockMedicines as $medicine)
                        <div class="rounded-2xl bg-white px-4 py-3 shadow-sm">
                            <p class="font-medium text-slate-900">{{ $medicine->name }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $medicine->code }} | Minimum {{ number_format($medicine->minimum_stock) }}</p>
                            <p class="mt-2 text-sm font-semibold {{ (int) ($medicine->current_stock ?? 0) === 0 ? 'text-rose-700' : 'text-amber-700' }}">
                                Stok saat ini: {{ number_format((int) ($medicine->current_stock ?? 0)) }} {{ $medicine->unit_name ?? '' }}
                            </p>
                        </div>
                    @empty
                        <p class="rounded-2xl bg-white px-4 py-4 text-sm text-slate-500 shadow-sm">Belum ada obat dengan stok menipis.</p>
                    @endforelse
                </div>
            </div>
        </article>

        <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Aktivitas Terbaru</p>
            <h3 class="mt-2 text-xl font-semibold text-slate-900">Log pengguna</h3>
            <div class="mt-5 space-y-3 text-sm leading-6 text-slate-600">
                @forelse ($recentActivities as $activity)
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <p class="font-medium text-slate-900">{{ $activity->description }}</p>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $activity->user?->name ?? 'Sistem' }} | {{ $activity->created_at->format('d M Y H:i') }}
                        </p>
                    </div>
                @empty
                    <div class="rounded-2xl bg-slate-50 px-4 py-4 text-slate-500">Belum ada log aktivitas.</div>
                @endforelse
            </div>
        </article>
    </section>

    <section class="mt-6 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Mutasi Terbaru</p>
                <h3 class="mt-2 text-xl font-semibold text-slate-900">Transaksi stok paling baru</h3>
            </div>
            <a href="{{ route('transaksi.mutasi.index') }}" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-700">
                Buka Mutasi
            </a>
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-[980px] w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Jenis</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Referensi</th>
                            <th class="px-4 py-3 font-semibold">Sumber / Tujuan</th>
                            <th class="px-4 py-3 font-semibold">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($recentTransactions as $transaction)
                            <tr>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($transaction['movement_date'])->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    @if ($transaction['type'] === 'stok_masuk')
                                        <span class="whitespace-nowrap rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Mutasi Masuk</span>
                                    @elseif ($transaction['type'] === 'stok_keluar')
                                        <span class="whitespace-nowrap rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800">Mutasi Keluar</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{{ $transaction['reference_number'] }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $transaction['counterpart_name'] ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $transaction['notes'] ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada transaksi stok terbaru.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-app-layout>
