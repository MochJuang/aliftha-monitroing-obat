<div class="mb-6 flex flex-wrap gap-2">
    <a href="{{ route('laporan.stok') }}" @class([
        'rounded-full px-4 py-2 text-sm font-semibold transition',
        'bg-slate-950 text-white' => request()->routeIs('laporan.stok', 'reports.stock'),
        'bg-slate-100 text-slate-700 hover:bg-slate-200' => !request()->routeIs('laporan.stok', 'reports.stock'),
    ])>
        Laporan Stok
    </a>
    <a href="{{ route('laporan.mutasi') }}" @class([
        'rounded-full px-4 py-2 text-sm font-semibold transition',
        'bg-slate-950 text-white' => request()->routeIs('laporan.mutasi', 'reports.mutations'),
        'bg-slate-100 text-slate-700 hover:bg-slate-200' => !request()->routeIs('laporan.mutasi', 'reports.mutations'),
    ])>
        Laporan Mutasi
    </a>
    <a href="{{ route('laporan.rko') }}" @class([
        'rounded-full px-4 py-2 text-sm font-semibold transition',
        'bg-slate-950 text-white' => request()->routeIs('laporan.rko', 'reports.rko-realization'),
        'bg-slate-100 text-slate-700 hover:bg-slate-200' => !request()->routeIs('laporan.rko', 'reports.rko-realization'),
    ])>
        RKO vs Realisasi
    </a>
</div>
