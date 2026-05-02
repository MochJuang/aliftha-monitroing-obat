@csrf

<div class="grid gap-6 lg:grid-cols-2">
    <div>
        <label for="role_id" class="block text-sm font-medium text-slate-700">Role</label>
        <select id="role_id" name="role_id" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
            <option value="">Pilih role</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected((string) old('role_id', $user->role_id) === (string) $role->id)>{{ $role->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="is_active" class="block text-sm font-medium text-slate-700">Status akun</label>
        <select id="is_active" name="is_active" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
            <option value="1" @selected((string) old('is_active', (int) $user->is_active) === '1')>Aktif</option>
            <option value="0" @selected((string) old('is_active', (int) $user->is_active) === '0')>Nonaktif</option>
        </select>
    </div>

    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Nama lengkap</label>
        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
    </div>

    <div>
        <label for="username" class="block text-sm font-medium text-slate-700">Username</label>
        <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
    </div>

    <div>
        <label for="phone" class="block text-sm font-medium text-slate-700">No. telepon</label>
        <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-slate-700">Password {{ $user->exists ? '(opsional)' : '' }}</label>
        <input id="password" name="password" type="password" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" {{ $user->exists ? '' : 'required' }}>
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" class="mt-2 w-full rounded-2xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500" {{ $user->exists ? '' : 'required' }}>
    </div>
</div>

<div class="mt-8 flex items-center justify-end gap-3">
    <a href="{{ route('users.index') }}" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
        Batal
    </a>
    <button type="submit" class="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
        Simpan Pengguna
    </button>
</div>
