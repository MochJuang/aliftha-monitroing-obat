@csrf

<div class="grid gap-6 lg:grid-cols-2">
    <div>
        <label for="code" class="block text-sm font-medium text-slate-700">Kode</label>
        <input id="code" name="code" type="text" value="{{ old('code', $fundingSource->code) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
    </div>

    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Nama sumber dana</label>
        <input id="name" name="name" type="text" value="{{ old('name', $fundingSource->name) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
    </div>

    <div>
        <label for="source_type" class="block text-sm font-medium text-slate-700">Jenis sumber</label>
        <input id="source_type" name="source_type" type="text" value="{{ old('source_type', $fundingSource->source_type) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" placeholder="APBD / APBN / Dana Khusus">
    </div>

    <div>
        <label for="is_active" class="block text-sm font-medium text-slate-700">Status</label>
        <select id="is_active" name="is_active" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
            <option value="1" @selected((string) old('is_active', (int) $fundingSource->is_active) === '1')>Aktif</option>
            <option value="0" @selected((string) old('is_active', (int) $fundingSource->is_active) === '0')>Nonaktif</option>
        </select>
    </div>

    <div class="lg:col-span-2">
        <label for="notes" class="block text-sm font-medium text-slate-700">Catatan</label>
        <textarea id="notes" name="notes" rows="4" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('notes', $fundingSource->notes) }}</textarea>
    </div>
</div>

<div class="mt-8 flex items-center justify-end gap-3">
    <a href="{{ route('rko.sumber-dana.index') }}" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</a>
    <button type="submit" class="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Simpan</button>
</div>
