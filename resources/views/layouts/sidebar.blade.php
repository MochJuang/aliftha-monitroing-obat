@php
    $menuGroups = [
        [
            'title' => 'Utama',
            'items' => [
                ['label' => 'Dashboard', 'href' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
                ['label' => 'Profil', 'href' => route('profile.edit'), 'active' => request()->routeIs('profile.*')],
            ],
        ],
        [
            'title' => 'Master Data',
            'items' => [
                ['label' => 'Kategori Obat', 'href' => route('medicine-categories.index'), 'active' => request()->routeIs('medicine-categories.*')],
                ['label' => 'Satuan', 'href' => route('units.index'), 'active' => request()->routeIs('units.*')],
                ['label' => 'Obat', 'href' => route('medicines.index'), 'active' => request()->routeIs('medicines.*')],
                ['label' => 'Sumber Obat', 'href' => route('stock-sources.index'), 'active' => request()->routeIs('stock-sources.*')],
                ['label' => 'Tujuan Distribusi', 'href' => route('distribution-destinations.index'), 'active' => request()->routeIs('distribution-destinations.*')],
            ],
        ],
        [
            'title' => 'Transaksi',
            'items' => [
                ['label' => 'Stok Masuk', 'href' => route('stock-receipts.index'), 'active' => request()->routeIs('stock-receipts.*')],
                ['label' => 'Stok Keluar', 'href' => route('stock-distributions.index'), 'active' => request()->routeIs('stock-distributions.*')],
                ['label' => 'Penyesuaian Stok', 'href' => route('stock-adjustments.index'), 'active' => request()->routeIs('stock-adjustments.*')],
            ],
        ],
        [
            'title' => 'Monitoring',
            'items' => [
                ['label' => 'Stok Terkini', 'href' => route('stock-monitoring.current-stock'), 'active' => request()->routeIs('stock-monitoring.current-stock')],
                ['label' => 'Batch & Kedaluwarsa', 'href' => route('stock-monitoring.batches'), 'active' => request()->routeIs('stock-monitoring.batches')],
                ['label' => 'Kartu Stok', 'href' => '#'],
            ],
        ],
        [
            'title' => 'Lainnya',
            'items' => [
                ['label' => 'Laporan', 'href' => '#'],
                ['label' => 'Pengguna', 'href' => '#'],
                ['label' => 'Log Aktivitas', 'href' => '#'],
            ],
        ],
    ];
@endphp

<aside class="flex h-full w-72 flex-col border-r border-slate-200 bg-slate-950 text-slate-100">
    <div class="border-b border-slate-800 px-6 py-6">
        <a href="{{ route('dashboard') }}" class="block">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-300">DPPKB Kota Sukabumi</p>
            <h1 class="mt-2 text-lg font-semibold leading-tight">Monitoring Obat Kontrasepsi</h1>
            <p class="mt-2 text-sm text-slate-400">Panel internal pengelolaan stok, distribusi, dan pelaporan obat KB.</p>
        </a>
    </div>

    <div class="flex-1 space-y-6 overflow-y-auto px-4 py-5">
        @foreach ($menuGroups as $group)
            <div>
                <p class="px-3 text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">{{ $group['title'] }}</p>

                <div class="mt-3 space-y-1">
                    @foreach ($group['items'] as $item)
                        <a
                            href="{{ $item['href'] }}"
                            @class([
                                'flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-medium transition',
                                'bg-amber-400 text-slate-950 shadow-sm' => $item['active'] ?? false,
                                'text-slate-300 hover:bg-slate-900 hover:text-white' => ! ($item['active'] ?? false),
                            ])
                        >
                            <span>{{ $item['label'] }}</span>

                            @if ($item['href'] === '#')
                                <span class="rounded-full border border-slate-700 px-2 py-0.5 text-[10px] uppercase tracking-wide text-slate-500">
                                    Segera
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div class="border-t border-slate-800 px-4 py-4">
        <div class="rounded-2xl bg-slate-900 px-4 py-4">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Pengguna aktif</p>
            <p class="mt-2 font-semibold">{{ Auth::user()->name }}</p>
            <p class="mt-1 text-sm text-slate-400">{{ Auth::user()->role?->name ?? 'Tanpa role' }}</p>
        </div>
    </div>
</aside>
