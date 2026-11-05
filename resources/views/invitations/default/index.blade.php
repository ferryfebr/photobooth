<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $detail?->headline ?: $event->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f7f1e6] text-stone-800 antialiased">
    <main class="mx-auto max-w-2xl">
        <section class="relative overflow-hidden">
            @if ($coverUrl)
                <img src="{{ $coverUrl }}" alt="{{ $event->name }}" class="h-72 w-full object-cover">
            @else
                <div class="h-72 w-full bg-gradient-to-br from-rose-200 via-amber-100 to-emerald-100"></div>
            @endif

            <div class="relative -mt-20 mx-4 rounded-3xl bg-white/95 px-6 py-8 text-center shadow-xl backdrop-blur">
                <p class="text-xs uppercase tracking-[0.35em] text-stone-500">Undangan Acara</p>
                <h1 class="mt-3 text-3xl font-serif font-semibold leading-tight text-stone-900">
                    {{ $detail?->headline ?: $event->name }}
                </h1>
                <p class="mt-2 text-sm text-stone-600">{{ $event->client?->name }}</p>
                <p class="mt-4 inline-block rounded-full bg-stone-900 px-4 py-2 text-sm font-medium text-white">
                    {{ $event->event_date?->translatedFormat('l, d F Y') }}
                </p>
            </div>
        </section>

        <section class="space-y-5 px-4 py-8">
            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($detail?->description)
                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <p class="whitespace-pre-line text-sm leading-relaxed text-stone-600">{{ $detail->description }}</p>
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <p class="text-xs uppercase tracking-widest text-stone-500">Lokasi</p>
                    <p class="mt-2 font-semibold">{{ $detail?->venue_name ?: 'Lokasi acara' }}</p>
                    <p class="mt-1 text-sm text-stone-600">{{ $detail?->venue_address ?: 'Detail lokasi akan diinformasikan.' }}</p>
                    @if ($detail?->maps_url)
                        <a href="{{ $detail->maps_url }}" target="_blank" rel="noopener"
                           class="mt-3 inline-block rounded-full bg-stone-900 px-4 py-2 text-xs font-medium text-white">
                            Buka Google Maps
                        </a>
                    @endif
                </div>

                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <p class="text-xs uppercase tracking-widest text-stone-500">Konfirmasi Kehadiran</p>
                    <p class="mt-2 text-sm text-stone-600">
                        {{ $attendingGuests }} tamu sudah konfirmasi hadir.
                    </p>
                    <a href="#rsvp" class="mt-3 inline-block rounded-full border border-stone-300 px-4 py-2 text-xs font-medium text-stone-700">
                        Isi RSVP
                    </a>
                </div>
            </div>

            @if ($detail?->story)
                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <p class="text-xs uppercase tracking-widest text-stone-500">Cerita Kami</p>
                    <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-stone-600">{{ $detail->story }}</p>
                </div>
            @endif

            @if ($galleryUrls->isNotEmpty())
                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <p class="text-xs uppercase tracking-widest text-stone-500">Galeri</p>
                    <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                        @foreach ($galleryUrls as $url)
                            <img src="{{ $url }}" alt="Galeri {{ $event->name }}" class="aspect-square w-full rounded-2xl object-cover" loading="lazy">
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($detail?->gift_info)
                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <p class="text-xs uppercase tracking-widest text-stone-500">Amplop Digital</p>
                    <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-stone-600">{{ $detail->gift_info }}</p>
                </div>
            @endif

            <div class="rounded-3xl bg-white p-6 shadow-sm">
                <p class="text-xs uppercase tracking-widest text-stone-500">Modul Acara</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @if ($event->isModuleActive('photobooth'))
                        <a href="{{ route('photobooth.show', $event->slug) }}" class="rounded-full bg-stone-100 px-4 py-2 text-sm font-medium text-stone-700 hover:bg-stone-200">Photobooth</a>
                    @endif
                    @if ($event->isModuleActive('guest_photographer'))
                        <a href="{{ route('guest.show', $event->slug) }}" class="rounded-full bg-stone-100 px-4 py-2 text-sm font-medium text-stone-700 hover:bg-stone-200">Jadi Fotografer</a>
                    @endif
                    @if ($event->isModuleActive('voice_note'))
                        <a href="{{ route('voicenote.show', $event->slug) }}" class="rounded-full bg-stone-100 px-4 py-2 text-sm font-medium text-stone-700 hover:bg-stone-200">Kirim Ucapan</a>
                    @endif
                </div>
            </div>

            <div id="rsvp" class="rounded-3xl bg-white p-6 shadow-sm">
                <h2 class="font-serif text-xl font-semibold">RSVP</h2>
                <p class="mt-1 text-sm text-stone-600">Beri tahu kami apakah kamu bisa hadir.</p>

                @if ($errors->any())
                    <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ $rsvpUrl }}" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="device_fingerprint" data-fingerprint>

                    <div>
                        <label for="guest_name" class="mb-1 block text-sm font-medium">Nama</label>
                        <input id="guest_name" name="guest_name" type="text" value="{{ old('guest_name') }}" required
                               class="w-full rounded-2xl border border-stone-300 px-4 py-2.5 text-sm focus:border-stone-900 focus:outline-none">
                    </div>

                    <div>
                        <p class="mb-2 text-sm font-medium">Kehadiran</p>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <label class="flex items-center gap-2 rounded-2xl border border-stone-200 px-4 py-3 text-sm">
                                <input type="radio" name="attendance" value="hadir" @checked(old('attendance') === 'hadir') required>
                                Hadir
                            </label>
                            <label class="flex items-center gap-2 rounded-2xl border border-stone-200 px-4 py-3 text-sm">
                                <input type="radio" name="attendance" value="tidak_hadir" @checked(old('attendance') === 'tidak_hadir')>
                                Tidak hadir
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="guest_count" class="mb-1 block text-sm font-medium">Jumlah Tamu</label>
                        <input id="guest_count" name="guest_count" type="number" min="1" max="20" value="{{ old('guest_count', 1) }}"
                               class="w-full rounded-2xl border border-stone-300 px-4 py-2.5 text-sm focus:border-stone-900 focus:outline-none">
                    </div>

                    <div>
                        <label for="note" class="mb-1 block text-sm font-medium">Catatan (opsional)</label>
                        <textarea id="note" name="note" rows="3"
                                  class="w-full rounded-2xl border border-stone-300 px-4 py-2.5 text-sm focus:border-stone-900 focus:outline-none">{{ old('note') }}</textarea>
                    </div>

                    <button type="submit" class="w-full rounded-2xl bg-stone-900 px-4 py-3 text-sm font-semibold text-white hover:bg-stone-800">
                        Kirim RSVP
                    </button>
                </form>
            </div>
        </section>

        <footer class="pb-10 text-center text-xs text-stone-500">{{ config('app.name') }}</footer>
    </main>

    <script>
        document.querySelectorAll('[data-fingerprint]').forEach(function (input) {
            var key = 'photobooth-device-id';
            var value = localStorage.getItem(key);
            if (!value) {
                value = (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : String(Date.now()) + Math.random().toString(16).slice(2);
                localStorage.setItem(key, value);
            }
            input.value = value;
        });
    </script>
</body>
</html>
