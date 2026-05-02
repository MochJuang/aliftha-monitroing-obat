<div class="mb-6 flex flex-wrap gap-2">
    <a href="{{ route('reports.stock') }}" @class([
        'rounded-full px-4 py-2 text-sm font-semibold transition',
        'bg-slate-950 text-white' => request()->routeIs('reports.stock'),
        'bg-slate-100 text-slate-700 hover:bg-slate-200' => !request()->routeIs('reports.stock'),
    ])>
        Laporan Stok
    </a>
    <a href="{{ route('reports.receipts') }}" @class([
        'rounded-full px-4 py-2 text-sm font-semibold transition',
        'bg-slate-950 text-white' => request()->routeIs('reports.receipts'),
        'bg-slate-100 text-slate-700 hover:bg-slate-200' => !request()->routeIs('reports.receipts'),
    ])>
        Stok Masuk
    </a>
    <a href="{{ route('reports.distributions') }}" @class([
        'rounded-full px-4 py-2 text-sm font-semibold transition',
        'bg-slate-950 text-white' => request()->routeIs('reports.distributions'),
        'bg-slate-100 text-slate-700 hover:bg-slate-200' => !request()->routeIs('reports.distributions'),
    ])>
        Stok Keluar
    </a>
    <a href="{{ route('reports.adjustments') }}" @class([
        'rounded-full px-4 py-2 text-sm font-semibold transition',
        'bg-slate-950 text-white' => request()->routeIs('reports.adjustments'),
        'bg-slate-100 text-slate-700 hover:bg-slate-200' => !request()->routeIs('reports.adjustments'),
    ])>
        Adjustment
    </a>
</div>
