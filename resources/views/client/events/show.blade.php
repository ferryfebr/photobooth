@extends('client.layouts.app')

@section('title', $event->name)

@php
    $media = app(\App\Support\MediaStorage::class);
    $labels = \App\Modules\Core\Models\EventModule::labels();
@endphp

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <a href="{{ route('client.dashboard') }}" class="text-sm text-slate-500 hover:underline">Kembali ke dashboard</a>
            <h1 class="mt-1 text-2xl font-semibold">{{ $event->name }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $event->event_date?->format('d M Y') }} · {{ $event->package?->name ?? 'Tanpa paket' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ $event->publicUrl() }}" target="_blank" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50">Buka Halaman Tamu</a>
            @if ($event->isModuleActive('invitation'))
                <a href="{{ route('invitation.show', $event->slug) }}" target="_blank" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Lihat Undangan</a>
            @endif
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500">Tamu Hadir</p>
            <p class="mt-2 text-2xl font-semibold">{{ $event->rsvps->where('attendance', 'hadir')->sum('guest_count') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500">Foto Tamu</p>
            <p class="mt-2 text-2xl font-semibold">{{ $event->guestPhotos->count() }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500">Voice Note</p>
            <p class="mt-2 text-2xl font-semibold">{{ $event->voiceNotes->count() }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs uppercase tracking-wide text-slate-500">Sesi Photobooth</p>
            <p class="mt-2 text-2xl font-semibold">{{ $event->photoboothSessions->count() }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h2 class="font-semibold">Undangan Digital</h2>
                <p class="mt-2 text-sm text-slate-600">{{ $event->invitationDetail?->headline ?? 'Belum ada data undangan.' }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ $event->invitationDetail?->venue_name }}</p>
                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                    <span class="rounded-full bg-emerald-50 px-3 py-1 font-medium text-emerald-700">Hadir: {{ $event->rsvps->where('attendance', 'hadir')->sum('guest_count') }} tamu</span>
                    <span class="rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-600">Tidak hadir: {{ $event->rsvps->where('attendance', 'tidak_hadir')->count() }}</span>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h2 class="font-semibold">Galeri Guest Photographer</h2>
                @if ($event->guestPhotos->isEmpty())
                    <p class="mt-3 text-sm text-slate-500">Belum ada foto tamu.</p>
                @else
                    <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach ($event->guestPhotos as $photo)
                            <div class="overflow-hidden rounded-lg border border-slate-200 {{ $photo->is_approved ? '' : 'opacity-40' }}">
                                <img src="{{ $media->url($photo->file_path) }}" alt="Foto tamu" class="aspect-square w-full object-cover">
                                <div class="p-2 text-center">
                                    @if ($photo->is_approved)
                                        <form method="POST" action="{{ route('client.guest-photos.hide', $photo) }}">
                                            @csrf
                                            <button type="submit" class="text-xs text-slate-600 hover:underline">Sembunyikan</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('client.guest-photos.approve', $photo) }}">
                                            @csrf
                                            <button type="submit" class="text-xs text-emerald-700 hover:underline">Tampilkan</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h2 class="font-semibold">Kotak Ucapan</h2>
                @if ($event->voiceNotes->isEmpty())
                    <p class="mt-3 text-sm text-slate-500">Belum ada voice note.</p>
                @else
                    <ul class="mt-3 space-y-3">
                        @foreach ($event->voiceNotes as $voiceNote)
                            <li class="rounded-lg border border-slate-200 p-3">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-medium">{{ $voiceNote->sender_name ?: 'Anonim' }}</p>
                                    <p class="text-xs text-slate-500">{{ $voiceNote->duration_seconds }} detik</p>
                                </div>
                                <audio controls preload="none" src="{{ $media->url($voiceNote->file_path) }}" class="mt-2 w-full"></audio>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h2 class="font-semibold">RSVP Terbaru</h2>
                @if ($event->rsvps->isEmpty())
                    <p class="mt-3 text-sm text-slate-500">Belum ada RSVP.</p>
                @else
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="py-2">Nama</th>
                                    <th class="py-2">Kehadiran</th>
                                    <th class="py-2">Jumlah</th>
                                    <th class="py-2">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($event->rsvps as $rsvp)
                                    <tr>
                                        <td class="py-2">{{ $rsvp->guest_name }}</td>
                                        <td class="py-2">{{ str_replace('_', ' ', $rsvp->attendance) }}</td>
                                        <td class="py-2">{{ $rsvp->guest_count }}</td>
                                        <td class="py-2 text-slate-500">{{ $rsvp->note }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h2 class="font-semibold">Modul Aktif</h2>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($event->modules->where('is_active', true) as $module)
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">{{ $labels[$module->module_key] ?? $module->module_key }}</span>
                    @endforeach
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h2 class="font-semibold">Arsip Data</h2>
                <p class="mt-1 text-xs text-slate-500">Gabungkan semua foto dan voice note ke satu ZIP.</p>
                <form method="POST" action="{{ route('client.events.archive', $event) }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800">Buat Arsip</button>
                </form>
                <a href="{{ route('client.events.archive.download', $event) }}" class="mt-2 block rounded-lg border border-slate-300 px-3 py-2 text-center text-sm font-medium hover:bg-slate-50">Download Arsip</a>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h2 class="font-semibold">Statistik Aktivitas</h2>
                @if ($event->activityLogs->isEmpty())
                    <p class="mt-3 text-sm text-slate-500">Belum ada aktivitas tamu.</p>
                @else
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($event->activityLogs->groupBy('action') as $action => $logs)
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ str_replace('_', ' ', $action) }}: {{ $logs->count() }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.Echo) return;

            var timer;
            var refresh = function () {
                clearTimeout(timer);
                timer = setTimeout(function () { window.location.reload(); }, 1000);
            };

            var channel = window.Echo.private('event.' + {{ $event->id }});

            ['guest-photo.uploaded', 'voice-note.received', 'rsvp.submitted', 'photobooth.completed'].forEach(function (eventName) {
                channel.listen('.' + eventName, refresh);
            });
        });
    </script>
@endsection
