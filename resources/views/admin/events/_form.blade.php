@php
    $selectedModules = (array) old('modules', $event ? $event->modules->where('is_active', true)->pluck('module_key')->all() : []);
@endphp

@csrf
<input type="hidden" name="modules_submitted" value="1">

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="client_id" class="mb-1 block text-sm font-medium">Client</label>
        <select id="client_id" name="client_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
            <option value="">Pilih client</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}" @selected((int) old('client_id', $event?->client_id) === $client->id)>{{ $client->name }}</option>
            @endforeach
        </select>
        @error('client_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="package_id" class="mb-1 block text-sm font-medium">Paket</label>
        <select id="package_id" name="package_id"
                data-is-create="{{ $event ? 'false' : 'true' }}"
                data-has-selection="{{ $selectedModules ? 'true' : 'false' }}"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
            <option value="">Tanpa paket</option>
            @foreach ($packages as $package)
                <option value="{{ $package->id }}"
                        data-default-modules="{{ implode(',', $package->default_modules ?? []) }}"
                        @selected((int) old('package_id', $event?->package_id) === $package->id)>{{ $package->name }}</option>
            @endforeach
        </select>
        @error('package_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label for="name" class="mb-1 block text-sm font-medium">Nama Event</label>
        <input id="name" name="name" type="text" value="{{ old('name', $event?->name) }}" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
        @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="event_date" class="mb-1 block text-sm font-medium">Tanggal Acara</label>
        <input id="event_date" name="event_date" type="date" value="{{ old('event_date', $event?->event_date?->format('Y-m-d')) }}" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
        @error('event_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="status" class="mb-1 block text-sm font-medium">Status</label>
        <select id="status" name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
            @foreach (['draft' => 'Draft', 'active' => 'Aktif', 'completed' => 'Selesai', 'archived' => 'Arsip'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $event?->status ?? 'active') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="theme_slug" class="mb-1 block text-sm font-medium">Theme Slug (opsional)</label>
        <input id="theme_slug" name="theme_slug" type="text" value="{{ old('theme_slug', $event?->theme_slug) }}"
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
        @error('theme_slug')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="storage_days" class="mb-1 block text-sm font-medium">Masa Simpan (hari)</label>
        <input id="storage_days" name="storage_days" type="number" min="1" max="3650" value="{{ old('storage_days') }}" placeholder="Otomatis dari paket"
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
        @error('storage_days')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="photobooth_active_from" class="mb-1 block text-sm font-medium">Photobooth Mulai</label>
        <input id="photobooth_active_from" name="photobooth_active_from" type="datetime-local"
               value="{{ old('photobooth_active_from', $event?->photobooth_active_from?->format('Y-m-d\TH:i')) }}"
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
        @error('photobooth_active_from')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="photobooth_active_until" class="mb-1 block text-sm font-medium">Photobooth Selesai</label>
        <input id="photobooth_active_until" name="photobooth_active_until" type="datetime-local"
               value="{{ old('photobooth_active_until', $event?->photobooth_active_until?->format('Y-m-d\TH:i')) }}"
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
        @error('photobooth_active_until')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <p class="mb-2 text-sm font-medium">Modul Aktif</p>
        <div class="grid gap-2 sm:grid-cols-2">
            @foreach ($moduleKeys as $moduleKey)
                <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    <input type="checkbox" name="modules[]" value="{{ $moduleKey }}" @checked(in_array($moduleKey, $selectedModules, true)) class="rounded border-slate-300">
                    {{ \App\Modules\Core\Models\EventModule::labels()[$moduleKey] }}
                </label>
            @endforeach
        </div>
        @error('modules')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        @error('modules.*')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
        Simpan
    </button>
    <a href="{{ route('admin.events.index') }}" class="text-sm text-slate-600 hover:underline">Batal</a>
</div>

<script>
    (function () {
        var select = document.getElementById('package_id');
        if (!select) return;

        function applyPackageDefaults() {
            var option = select.options[select.selectedIndex];
            var defaults = (option ? option.dataset.defaultModules : '').split(',').filter(Boolean);

            document.querySelectorAll('input[name="modules[]"]').forEach(function (checkbox) {
                checkbox.checked = defaults.includes(checkbox.value);
            });
        }

        select.addEventListener('change', applyPackageDefaults);

        if (select.dataset.isCreate === 'true' && select.dataset.hasSelection !== 'true') {
            applyPackageDefaults();
        }
    })();
</script>
