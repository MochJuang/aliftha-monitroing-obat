@csrf

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label for="category_id" class="block text-sm font-medium text-slate-700">Kategori</label>
        <select id="category_id" name="category_id" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
            <option value="">Pilih kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $medicine->category_id) === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="unit_id" class="block text-sm font-medium text-slate-700">Satuan</label>
        <select id="unit_id" name="unit_id" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
            <option value="">Pilih satuan</option>
            @foreach ($units as $unit)
                <option value="{{ $unit->id }}" @selected((string) old('unit_id', $medicine->unit_id) === (string) $unit->id)>{{ $unit->name }} ({{ $unit->symbol }})</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="code" class="block text-sm font-medium text-slate-700">Kode obat</label>
        <input id="code" name="code" type="text" value="{{ old('code', $medicine->code) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
    </div>

    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Nama obat</label>
        <input id="name" name="name" type="text" value="{{ old('name', $medicine->name) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
    </div>

    <div>
        <label for="medicine_type" class="block text-sm font-medium text-slate-700">Jenis obat</label>
        <input id="medicine_type" name="medicine_type" type="text" value="{{ old('medicine_type', $medicine->medicine_type) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" placeholder="Contoh: Pil, Suntik, Implant, IUD">
    </div>

    <div>
        <label for="brand" class="block text-sm font-medium text-slate-700">Merek</label>
        <input id="brand" name="brand" type="text" value="{{ old('brand', $medicine->brand) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
    </div>

    <div>
        <label for="dosage" class="block text-sm font-medium text-slate-700">Dosis / kemasan</label>
        <input id="dosage" name="dosage" type="text" value="{{ old('dosage', $medicine->dosage) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
    </div>

    <div>
        <label for="minimum_stock" class="block text-sm font-medium text-slate-700">Stok minimum</label>
        <input id="minimum_stock" name="minimum_stock" type="number" min="0" value="{{ old('minimum_stock', $medicine->minimum_stock ?? 0) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
    </div>

    <div>
        <label for="standard_price" class="block text-sm font-medium text-slate-700">Harga standar</label>
        <input id="standard_price" name="standard_price" type="number" min="0" step="0.01" value="{{ old('standard_price', $medicine->standard_price ?? 0) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
    </div>

    <div class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-4">
        <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-slate-300 text-amber-600 shadow-sm focus:ring-amber-500" @checked(old('is_active', $medicine->is_active ?? true))>
        <label for="is_active" class="text-sm font-medium text-slate-700">Aktif dan bisa dipakai untuk transaksi</label>
    </div>

    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-slate-700">Deskripsi</label>
        <textarea id="description" name="description" rows="4" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('description', $medicine->description) }}</textarea>
    </div>
</div>

<div class="mt-8 flex items-center justify-end gap-3">
    <a href="{{ route('master-obat.obat.index') }}" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</a>
    <button type="submit" class="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Simpan</button>
</div>
