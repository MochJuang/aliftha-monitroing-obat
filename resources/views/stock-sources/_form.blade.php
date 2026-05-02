@csrf

<div class="grid gap-6 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="name" class="block text-sm font-medium text-slate-700">Nama sumber</label>
        <input id="name" name="name" type="text" value="{{ old('name', $source->name) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
    </div>

    <div>
        <label for="source_type" class="block text-sm font-medium text-slate-700">Jenis sumber</label>
        <select id="source_type" name="source_type" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
            <option value="">Pilih jenis sumber</option>
            @foreach (['dinkes' => 'Dinkes', 'bkkbn' => 'BKKBN', 'supplier' => 'Supplier', 'lainnya' => 'Lainnya'] as $value => $label)
                <option value="{{ $value }}" @selected(old('source_type', $source->source_type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="contact_person" class="block text-sm font-medium text-slate-700">Contact person</label>
        <input id="contact_person" name="contact_person" type="text" value="{{ old('contact_person', $source->contact_person) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
    </div>

    <div>
        <label for="phone" class="block text-sm font-medium text-slate-700">Telepon</label>
        <input id="phone" name="phone" type="text" value="{{ old('phone', $source->phone) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
    </div>

    <div class="md:col-span-2">
        <label for="address" class="block text-sm font-medium text-slate-700">Alamat</label>
        <textarea id="address" name="address" rows="4" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('address', $source->address) }}</textarea>
    </div>
</div>

<div class="mt-8 flex items-center justify-end gap-3">
    <a href="{{ route('pengadaan.sumber.index') }}" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</a>
    <button type="submit" class="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Simpan</button>
</div>
