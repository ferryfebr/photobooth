<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $event->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-white antialiased">
    <main class="mx-auto flex min-h-screen max-w-2xl flex-col justify-center px-5 py-12">
        <header class="text-center">
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Undangan Acara</p>
            <h1 class="mt-3 text-3xl font-semibold leading-tight sm:text-4xl">{{ $event->name }}</h1>
            <p class="mt-3 text-sm text-slate-300">
                {{ $event->event_date?->translatedFormat('d F Y') }}
                @if ($event->client)
                    · {{ $event->client->name }}
                @endif
            </p>
        </header>

        <section class="mt-10 space-y-3">
            @php
                $labels = \App\Modules\Core\Models\EventModule::labels();
                $descriptions = [
                    'photobooth' => 'Ambil foto langsung dari kamera HP-mu.',
                    'guest_photographer' => 'Jadi fotografer dan bagikan momen acara.',
                    'voice_note' => 'Rekam ucapan dan doa untuk tuan rumah.',
                    'invitation' => 'Lihat detail acara dan konfirmasi kehadiran.',
                ];
                $urls = [
                    'photobooth' => route('photobooth.show', $event->slug),
                    'guest_photographer' => route('guest.show', $event->slug),
                    'voice_note' => route('voicenote.show', $event->slug),
                    'invitation' => route('invitation.show', $event->slug),
                ];
            @endphp

            @forelse ($event->modules->where('is_active', true) as $module)
                <a href="{{ $urls[$module->module_key] ?? '#' }}"
                   class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-white/5 px-5 py-4 transition hover:border-white/30 hover:bg-white/10">
                    <div>
                        <p class="font-medium">{{ $labels[$module->module_key] ?? $module->module_key }}</p>
                        <p class="mt-1 text-sm text-slate-400">{{ $descriptions[$module->module_key] ?? '' }}</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-900">Buka</span>
                </a>
            @empty
                <p class="rounded-2xl border border-white/10 bg-white/5 px-5 py-6 text-center text-sm text-slate-400">
                    Modul acara belum diaktifkan.
                </p>
            @endforelse
        </section>

        <footer class="mt-12 text-center text-xs text-slate-500">
            {{ config('app.name') }}
        </footer>
    </main>
</body>
</html>
