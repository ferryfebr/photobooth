@extends('admin.layouts.app')

@section('title', $event->name)

@php
    $media = app(\App\Support\MediaStorage::class);
@endphp

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="text-sm text-slate-500">Event</p>
            <h1 class="text-2xl font-semibold">{{ $event->name }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $event->slug }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.events.edit', $event) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                Edit Event
            </a>
            <form method="POST" action="{{ route('admin.events.destroy', $event) }}" onsubmit="return confirm('Hapus event ini beserta seluruh datanya?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-lg border border-rose-200 px-4 py-2 text-sm font-medium text-rose-700 hover:bg-rose-50">
                    Hapus
                </button>
            </form>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h2 class="font-semibold">Detail</h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Client</dt>
                        <dd class="mt-1 text-sm">{{ $event->client?->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Paket</dt>
                        <dd class="mt-1 text-sm">{{ $event->package?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Tanggal Acara</dt>
                        <dd class="mt-1 text-sm">{{ $event->event_date?->format('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Status</dt>
                        <dd class="mt-1 text-sm">{{ $event->status }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Photobooth Aktif</dt>
                        <dd class="mt-1 text-sm">
                            {{ $event->photobooth_active_from?->format('d M Y H:i') ?? '—' }}
                            s/d
                            {{ $event->photobooth_active_until?->format('d M Y H:i') ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Data Kedaluwarsa</dt>
                        <dd class="mt-1 text-sm">{{ $event->storage_expires_at?->format('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Theme Slug</dt>
                        <dd class="mt-1 text-sm">{{ $event->theme_slug ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-500">URL Publik</dt>
                        <dd class="mt-1 truncate text-sm">
                            <a href="{{ $event->publicUrl() }}" target="_blank" class="text-slate-700 underline">{{ $event->publicUrl() }}</a>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="font-semibold">Undangan Digital</h2>
                    <a href="{{ route('admin.invitations.edit', $event) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium hover:bg-slate-50">
                        Atur Undangan
                    </a>
                </div>
                <p class="mt-2 text-sm text-slate-500">
                    {{ $event->invitationDetail?->headline ?? 'Belum ada data undangan.' }}
                </p>
                <div class="mt-3 flex flex-wrap gap-2 text-sm">
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                        Hadir: {{ $event->rsvps->where('attendance', 'hadir')->sum('guest_count') }} tamu
                    </span>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                        Tidak hadir: {{ $event->rsvps->where('attendance', 'tidak_hadir')->count() }}
                    </span>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="font-semibold">Frame Photobooth</h2>
                    <a href="{{ route('admin.frames.create', $event) }}" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-medium text-white hover:bg-slate-800">
                        Tambah Frame
                    </a>
                </div>

                @if ($event->frames->isEmpty())
                    <p class="mt-3 text-sm text-slate-500">Belum ada frame.</p>
                @else
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        @foreach ($event->frames as $frame)
                            <div class="flex items-center gap-3 rounded-lg border border-slate-200 p-3">
                                <img src="{{ $media->url($frame->file_path) }}" alt="{{ $frame->name }}" class="h-16 w-16 rounded-lg border border-slate-200 object-contain">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium">{{ $frame->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $frame->photo_count }} foto · {{ count($frame->slot_coordinates ?? []) }} slot</p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('admin.frames.edit', [$event, $frame]) }}" class="rounded-md border border-slate-300 px-2 py-1 text-xs hover:bg-slate-50">Edit</a>
                                    <form method="POST" action="{{ route('admin.frames.destroy', [$event, $frame]) }}" onsubmit="return confirm('Hapus frame ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-rose-200 px-2 py-1 text-xs text-rose-700 hover:bg-rose-50">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
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
                                <div class="flex items-center justify-between gap-1 p-2">
                                    @if ($photo->is_approved)
                                        <form method="POST" action="{{ route('admin.guest-photos.hide', $photo) }}">
                                            @csrf
                                            <button type="submit" class="text-xs text-slate-600 hover:underline">Sembunyikan</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.guest-photos.approve', $photo) }}">
                                            @csrf
                                            <button type="submit" class="text-xs text-emerald-700 hover:underline">Tampilkan</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.guest-photos.destroy', $photo) }}" onsubmit="return confirm('Hapus foto ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-rose-700 hover:underline">Hapus</button>
                                    </form>
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
                                    <div>
                                        <p class="text-sm font-medium">{{ $voiceNote->sender_name ?: 'Anonim' }}</p>
                                        <p class="text-xs text-slate-500">{{ $voiceNote->duration_seconds }} detik · {{ $voiceNote->converted_at ? 'MP3' : 'WebM' }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('admin.voice-notes.destroy', $voiceNote) }}" onsubmit="return confirm('Hapus voice note ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-rose-700 hover:underline">Hapus</button>
                                    </form>
                                </div>
                                <audio controls preload="none" src="{{ $media->url($voiceNote->file_path) }}" class="mt-2 w-full"></audio>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h2 class="font-semibold">Statistik Aktivitas</h2>
                @if ($event->activityLogs->isEmpty())
                    <p class="mt-3 text-sm text-slate-500">Belum ada aktivitas tamu.</p>
                @else
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($event->activityLogs->groupBy('action') as $action => $logs)
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                                {{ str_replace('_', ' ', $action) }}: {{ $logs->count() }}
                            </span>
                        @endforeach
                    </div>
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
            <div class="rounded-xl border border-slate-200 bg-white p-6 text-center">
                <h2 class="font-semibold">QR Code Event</h2>
                <img src="{{ route('admin.events.qr', $event) }}" alt="QR {{ $event->name }}" class="mx-auto mt-4 w-full max-w-[240px] rounded-lg border border-slate-200">
                <a href="{{ route('admin.events.qr', $event) }}" target="_blank" class="mt-4 inline-block text-sm text-slate-600 underline">
                    Buka QR ukuran penuh
                </a>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h2 class="font-semibold">Preview Halaman Tamu</h2>
                <p class="mt-1 text-xs text-slate-500">Admin bisa membuka halaman draft/inaktif untuk pengecekan.</p>
                <div class="mt-3 space-y-2 text-sm">
                    <a href="{{ $event->publicUrl() }}" target="_blank" class="block rounded-lg border border-slate-300 px-3 py-2 hover:bg-slate-50">Landing Event</a>
                    <a href="{{ route('invitation.show', $event->slug) }}" target="_blank" class="block rounded-lg border border-slate-300 px-3 py-2 hover:bg-slate-50">Undangan Digital</a>
                    <a href="{{ route('photobooth.show', $event->slug) }}" target="_blank" class="block rounded-lg border border-slate-300 px-3 py-2 hover:bg-slate-50">Photobooth</a>
                    <a href="{{ route('guest.show', $event->slug) }}" target="_blank" class="block rounded-lg border border-slate-300 px-3 py-2 hover:bg-slate-50">Guest Photographer</a>
                    <a href="{{ route('voicenote.show', $event->slug) }}" target="_blank" class="block rounded-lg border border-slate-300 px-3 py-2 hover:bg-slate-50">Voice Note</a>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h2 class="font-semibold">Arsip Data</h2>
                <p class="mt-1 text-xs text-slate-500">Menggabungkan foto photobooth, foto tamu, dan voice note ke satu ZIP.</p>
                <form method="POST" action="{{ route('admin.events.archive', $event) }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800">
                        Buat Arsip
                    </button>
                </form>
                <a href="{{ route('admin.events.archive.download', $event) }}" class="mt-2 block rounded-lg border border-slate-300 px-3 py-2 text-center text-sm font-medium hover:bg-slate-50">
                    Download Arsip
                </a>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h2 class="font-semibold">Modul Aktif</h2>
                <div class="mt-3 flex flex-wrap gap-2">
                    @forelse ($event->modules->where('is_active', true) as $module)
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                            {{ \App\Modules\Core\Models\EventModule::labels()[$module->module_key] ?? $module->module_key }}
                        </span>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada modul aktif.</p>
                    @endforelse
                </div>
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
