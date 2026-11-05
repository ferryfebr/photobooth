@csrf

<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="name" class="mb-1 block text-sm font-medium">Nama</label>
        <input id="name" name="name" type="text" value="{{ old('name', $client?->name) }}" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
        @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="phone" class="mb-1 block text-sm font-medium">Nomor WhatsApp</label>
        <input id="phone" name="phone" type="text" value="{{ old('phone', $client?->phone) }}" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
        @error('phone')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="email" class="mb-1 block text-sm font-medium">Email (opsional)</label>
        <input id="email" name="email" type="email" value="{{ old('email', $client?->email) }}"
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
        @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="password" class="mb-1 block text-sm font-medium">Password Login Client (opsional)</label>
        <input id="password" name="password" type="password" minlength="8" autocomplete="new-password"
               placeholder="{{ $client ? 'Kosongkan jika tidak diganti' : 'Minimal 8 karakter' }}"
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
        @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="status" class="mb-1 block text-sm font-medium">Status</label>
        <select id="status" name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
            @foreach (['active' => 'Aktif', 'inactive' => 'Nonaktif'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $client?->status ?? 'active') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label for="notes" class="mb-1 block text-sm font-medium">Catatan Internal</label>
        <textarea id="notes" name="notes" rows="4"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">{{ old('notes', $client?->notes) }}</textarea>
        @error('notes')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
        Simpan
    </button>
    <a href="{{ route('admin.clients.index') }}" class="text-sm text-slate-600 hover:underline">Batal</a>
</div>
