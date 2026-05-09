@php
    $query = request()->query();
@endphp

<div class="mt-4 flex flex-wrap items-center gap-2">
    <a
        href="{{ route($routeName, array_merge($query, ['format' => 'pdf'])) }}"
        target="_blank"
        class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
    >
        Cetak PDF
    </a>
    <a
        href="{{ route($routeName, array_merge($query, ['format' => 'excel'])) }}"
        class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
    >
        Cetak Excel
    </a>
</div>
