@php
    $user = Auth::user();
    $menuGroups = [
        [
            'title' => 'Utama',
            'items' => [
                ['label' => 'Dashboard', 'href' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
                ['label' => 'Profil', 'href' => route('profile.edit'), 'active' => request()->routeIs('profile.*')],
            ],
        ],
        [
            'title' => 'Faskes',
            'items' => array_values(array_filter([
                ['label' => 'Data Faskes', 'href' => route('faskes.index'), 'active' => request()->routeIs('faskes.index', 'faskes.create', 'faskes.show', 'faskes.edit', 'distribution-destinations.*')],
            ], fn ($item) => $user?->can('manage-faskes'))),
        ],
        [
            'title' => 'Master Obat',
            'items' => array_values(array_filter([
                ['label' => 'Kategori Obat', 'href' => route('master-obat.kategori.index'), 'active' => request()->routeIs('master-obat.kategori.*', 'medicine-categories.*')],
                ['label' => 'Satuan', 'href' => route('master-obat.satuan.index'), 'active' => request()->routeIs('master-obat.satuan.*', 'units.*')],
                ['label' => 'Data Obat', 'href' => route('master-obat.obat.index'), 'active' => request()->routeIs('master-obat.obat.*', 'medicines.*')],
            ], fn ($item) => $user?->can('manage-master-obat'))),
        ],
        [
            'title' => 'RKO',
            'items' => array_values(array_filter([
                ['label' => 'Sumber Dana', 'href' => route('rko.sumber-dana.index'), 'active' => request()->routeIs('rko.sumber-dana.*', 'funding-sources.*')],
                ['label' => 'RKO Header', 'href' => route('rko.header.index'), 'active' => request()->routeIs('rko.header.*', 'rko-headers.*')],
                ['label' => 'RKO Detail', 'href' => route('rko.detail.index'), 'active' => request()->routeIs('rko.detail.*')],
                ['label' => 'Realisasi Pengadaan', 'href' => route('rko.realisasi.index'), 'active' => request()->routeIs('rko.realisasi.*', 'procurement-realizations.*')],
            ], function ($item) use ($user) {
                return match ($item['label']) {
                    'Sumber Dana' => $user?->can('manage-funding-sources'),
                    'Realisasi Pengadaan' => $user?->can('view-procurement-realizations'),
                    default => $user?->can('view-rko'),
                };
            })),
        ],
        [
            'title' => 'Transaksi',
            'items' => array_values(array_filter([
                ['label' => 'Mutasi Stok', 'href' => route('transaksi.mutasi.index'), 'active' => request()->routeIs('transaksi.mutasi.*', 'stock-mutations.*')],
            ], fn ($item) => $user?->can('manage-stock-mutations'))),
        ],
        [
            'title' => 'Monitoring',
            'items' => array_values(array_filter([
                ['label' => 'Stok Terkini', 'href' => route('monitoring.stok.index'), 'active' => request()->routeIs('monitoring.stok.*', 'stock-monitoring.current-stock')],
            ], fn ($item) => $user?->can('view-monitoring'))),
        ],
        [
            'title' => 'Lainnya',
            'items' => array_values(array_filter([
                $user?->can('view-reports')
                    ? ['label' => 'Laporan', 'href' => route('laporan.stok'), 'active' => request()->routeIs('laporan.*', 'reports.*')]
                    : null,
                $user?->can('manage-users')
                    ? ['label' => 'Pengguna', 'href' => route('users.index'), 'active' => request()->routeIs('users.*')]
                    : null,
                $user?->can('view-activity-logs')
                    ? ['label' => 'Log Aktivitas', 'href' => route('activity-logs.index'), 'active' => request()->routeIs('activity-logs.*')]
                    : null,
            ])),
        ],
    ];

    $menuGroups = array_values(array_filter($menuGroups, fn ($group) => count($group['items']) > 0));
@endphp

<aside class="flex h-full w-72 flex-col border-r border-slate-200 bg-slate-950 text-slate-100">
    <div class="border-b border-slate-800 px-6 py-6">
        <a href="{{ route('dashboard') }}" class="block">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-300">DPPKB Kota Sukabumi</p>
            <h1 class="mt-2 text-lg font-semibold leading-tight">Monitoring Obat Kontrasepsi</h1>
            <p class="mt-2 text-sm text-slate-400">Panel internal monitoring kebutuhan, mutasi, dan pelaporan obat KB.</p>
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
