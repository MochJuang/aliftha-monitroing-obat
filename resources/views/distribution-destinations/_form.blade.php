@csrf

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label for="code" class="block text-sm font-medium text-slate-700">Kode tujuan</label>
        <input id="code" name="code" type="text" value="{{ old('code', $destination->code) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
    </div>

    <div>
        <label for="destination_type" class="block text-sm font-medium text-slate-700">Jenis tujuan</label>
        <select id="destination_type" name="destination_type" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
            <option value="">Pilih jenis tujuan</option>
            @foreach (['puskesmas' => 'Puskesmas', 'klinik' => 'Klinik', 'bidan' => 'Bidan', 'lainnya' => 'Lainnya'] as $value => $label)
                <option value="{{ $value }}" @selected(old('destination_type', $destination->destination_type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="md:col-span-2">
        <label for="name" class="block text-sm font-medium text-slate-700">Nama tujuan</label>
        <input id="name" name="name" type="text" value="{{ old('name', $destination->name) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
    </div>

    <div>
        <label for="contact_person" class="block text-sm font-medium text-slate-700">Contact person</label>
        <input id="contact_person" name="contact_person" type="text" value="{{ old('contact_person', $destination->contact_person) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
    </div>

    <div>
        <label for="phone" class="block text-sm font-medium text-slate-700">Telepon</label>
        <input id="phone" name="phone" type="text" value="{{ old('phone', $destination->phone) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
    </div>

    <div class="md:col-span-2">
        <label for="address" class="block text-sm font-medium text-slate-700">Alamat</label>
        <textarea id="address" name="address" rows="4" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('address', $destination->address) }}</textarea>
    </div>

    <div class="md:col-span-2 flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-4">
        <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-slate-300 text-amber-600 shadow-sm focus:ring-amber-500" @checked(old('is_active', $destination->is_active ?? true))>
        <label for="is_active" class="text-sm font-medium text-slate-700">Aktif dan bisa dipilih saat distribusi obat</label>
    </div>
</div>

<div class="mt-8 flex items-center justify-end gap-3">
    <a href="{{ route('faskes.index') }}" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</a>
    <button type="submit" class="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Simpan</button>
</div>
