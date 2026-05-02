@csrf

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Nama satuan</label>
        <input id="name" name="name" type="text" value="{{ old('name', $unit->name) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
    </div>

    <div>
        <label for="symbol" class="block text-sm font-medium text-slate-700">Simbol</label>
        <input id="symbol" name="symbol" type="text" value="{{ old('symbol', $unit->symbol) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
    </div>
</div>

<div class="mt-8 flex items-center justify-end gap-3">
    <a href="{{ route('master-obat.satuan.index') }}" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</a>
    <button type="submit" class="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Simpan</button>
</div>
