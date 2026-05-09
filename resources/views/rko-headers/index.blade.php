<x-app-layout>
    <x-slot name="header">RKO Header</x-slot>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total dokumen RKO</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($summary['total_headers']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Status draft</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($summary['draft_count']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <p class="text-sm text-amber-800">Status diajukan</p>
            <p class="mt-2 text-3xl font-semibold text-amber-900">{{ number_format($summary['submitted_count']) }}</p>
        </article>
        <article class="rounded-[2rem] border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
            <p class="text-sm text-emerald-800">Status disetujui</p>
            <p class="mt-2 text-3xl font-semibold text-emerald-900">{{ number_format($summary['approved_count']) }}</p>
        </article>
    </section>

    <section class="mt-6 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Daftar RKO</h3>
                <p class="mt-1 text-sm text-slate-500">Kelola pengajuan RKO per periode, lalu lakukan persetujuan pada form terpisah agar nilai estimasi dan nilai persetujuan tidak tercampur.</p>
            </div>
            @can('create-rko')
                <a href="{{ route('rko.header.create') }}" class="inline-flex rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    Tambah RKO
                </a>
            @endcan
        </div>

		        <div class="mt-6 overflow-x-auto">
		            <form method="GET" action="{{ route('rko.header.index') }}" class="flex flex-nowrap items-end gap-3 min-w-max">
		                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor RKO atau catatan..." class="min-w-[260px] flex-1 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
		                <select name="status" class="w-44 shrink-0 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
		                <option value="">Semua status</option>
		                <option value="draft" @selected($status === 'draft')>Draft</option>
		                <option value="submitted" @selected($status === 'submitted')>Diajukan</option>
		                <option value="approved" @selected($status === 'approved')>Disetujui</option>
		                <option value="rejected" @selected($status === 'rejected')>Ditolak</option>
		                </select>
		                <select name="funding_source_id" class="w-64 shrink-0 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
		                <option value="">Semua sumber dana</option>
		                @foreach ($fundingSources as $fundingSource)
		                    <option value="{{ $fundingSource->id }}" @selected($fundingSourceId === (string) $fundingSource->id)>{{ $fundingSource->name }}</option>
		                @endforeach
		                </select>
		                <select name="period_year" class="w-40 shrink-0 rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
		                <option value="">Semua tahun</option>
		                @foreach ($availableYears as $year)
		                    <option value="{{ $year }}" @selected($periodYear === (string) $year)>{{ $year }}</option>
		                @endforeach
		                </select>
		                <button type="submit" class="w-28 shrink-0 rounded-2xl border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Filter</button>
		                <a href="{{ route('rko.header.export.excel', request()->query()) }}" class="w-32 shrink-0 rounded-2xl border border-slate-300 px-5 py-2 text-center text-sm font-medium text-slate-700 hover:bg-slate-50">Cetak Excel</a>
		            </form>
		        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-[1580px] w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Nomor RKO</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Periode</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Sumber Dana</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Jumlah Item</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Total Rencana</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Total Disetujui</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Total Anggaran</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal Pengajuan</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal Persetujuan</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Penyusun</th>
                            <th class="px-4 py-3 font-semibold text-right whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($headers as $header)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{{ $header->rko_number }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ sprintf('%02d', $header->period_month) }}/{{ $header->period_year }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $header->fundingSource?->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold',
                                        'bg-slate-100 text-slate-700' => $header->status === 'draft',
                                        'bg-amber-100 text-amber-800' => $header->status === 'submitted',
                                        'bg-emerald-100 text-emerald-800' => $header->status === 'approved',
                                        'bg-rose-100 text-rose-800' => $header->status === 'rejected',
                                    ])>
                                        {{ match($header->status) { 'draft' => 'Draft', 'submitted' => 'Diajukan', 'approved' => 'Disetujui', default => 'Ditolak' } }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format($header->items_count) }} item</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format((int) ($header->items_sum_planned_quantity ?? 0)) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ number_format((int) ($header->items_sum_approved_quantity ?? 0)) }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">Rp {{ number_format((float) $header->total_budget, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $header->submitted_at?->format('d M Y') ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $header->approved_at?->format('d M Y') ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $header->submitter?->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2 whitespace-nowrap">
                                        <a href="{{ route('rko.header.show', $header) }}" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">Detail</a>
                                        @can('create-rko')
                                            @if ($header->status !== 'approved')
                                                <a href="{{ route('rko.header.edit', $header) }}" class="rounded-xl border border-amber-300 px-3 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-50">Pengajuan</a>
                                            @endif
                                        @endcan
                                        @can('approve-rko')
                                            @if ($header->status === 'submitted')
                                                <a href="{{ route('rko.header.approval.edit', $header) }}" class="rounded-xl border border-emerald-300 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-50">Persetujuan</a>
                                            @endif
                                        @endcan
                                        @can('create-rko')
                                            <form method="POST" action="{{ route('rko.header.destroy', $header) }}" onsubmit="return confirm('Hapus dokumen RKO ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-xl border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50">Hapus</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="12" class="px-4 py-8 text-center text-slate-500">Belum ada dokumen RKO.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $headers->links() }}</div>
    </section>
</x-app-layout>
