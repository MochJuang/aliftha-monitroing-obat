<div class="mb-6 flex flex-wrap gap-2">
    <a href="{{ route('laporan.stok') }}" @class([
        'rounded-full px-4 py-2 text-sm font-semibold transition',
        'bg-slate-950 text-white' => request()->routeIs('laporan.stok', 'reports.stock'),
        'bg-slate-100 text-slate-700 hover:bg-slate-200' => !request()->routeIs('laporan.stok', 'reports.stock'),
    ])>
        Laporan Stok
    </a>
    <a href="{{ route('laporan.pengadaan') }}" @class([
        'rounded-full px-4 py-2 text-sm font-semibold transition',
        'bg-slate-950 text-white' => request()->routeIs('laporan.pengadaan', 'reports.receipts'),
        'bg-slate-100 text-slate-700 hover:bg-slate-200' => !request()->routeIs('laporan.pengadaan', 'reports.receipts'),
    ])>
        Realisasi Pengadaan
    </a>
    <a href="{{ route('laporan.distribusi') }}" @class([
        'rounded-full px-4 py-2 text-sm font-semibold transition',
        'bg-slate-950 text-white' => request()->routeIs('laporan.distribusi', 'reports.distributions'),
        'bg-slate-100 text-slate-700 hover:bg-slate-200' => !request()->routeIs('laporan.distribusi', 'reports.distributions'),
    ])>
        Distribusi Obat
    </a>
    <a href="{{ route('laporan.penyesuaian') }}" @class([
        'rounded-full px-4 py-2 text-sm font-semibold transition',
        'bg-slate-950 text-white' => request()->routeIs('laporan.penyesuaian', 'reports.adjustments'),
        'bg-slate-100 text-slate-700 hover:bg-slate-200' => !request()->routeIs('laporan.penyesuaian', 'reports.adjustments'),
    ])>
        Penyesuaian Stok
    </a>
    <a href="{{ route('laporan.rko') }}" @class([
        'rounded-full px-4 py-2 text-sm font-semibold transition',
        'bg-slate-950 text-white' => request()->routeIs('laporan.rko', 'reports.rko-realization'),
        'bg-slate-100 text-slate-700 hover:bg-slate-200' => !request()->routeIs('laporan.rko', 'reports.rko-realization'),
    ])>
        RKO vs Realisasi
    </a>
</div>
