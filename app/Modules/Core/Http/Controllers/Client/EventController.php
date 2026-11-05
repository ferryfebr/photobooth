<?php

namespace App\Modules\Core\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Modules\Core\Jobs\GenerateArchiveZipJob;
use App\Modules\Core\Models\Event;
use App\Modules\GuestPhotographer\Models\GuestPhoto;
use App\Support\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventController extends Controller
{
    public function show(Event $event): View
    {
        $this->authorizeClient($event);

        $event->load([
            'package',
            'modules',
            'invitationDetail',
            'frames',
            'photoboothSessions',
            'guestPhotos' => fn ($query) => $query->latest()->limit(48),
            'voiceNotes' => fn ($query) => $query->latest()->limit(50),
            'rsvps' => fn ($query) => $query->latest()->limit(100),
            'activityLogs' => fn ($query) => $query->latest()->limit(200),
        ]);

        return view('client.events.show', compact('event'));
    }

    public function archive(Event $event): RedirectResponse
    {
        $this->authorizeClient($event);

        $cacheKey = 'archive:event:'.$event->id;

        Cache::forget($cacheKey);
        GenerateArchiveZipJob::dispatch($event->id, $cacheKey);

        return back()->with('status', 'Arsip sedang dibuat. Muat ulang halaman beberapa saat lagi.');
    }

    public function archiveDownload(Event $event, MediaStorage $media): StreamedResponse|RedirectResponse
    {
        $this->authorizeClient($event);

        $path = Cache::get('archive:event:'.$event->id);

        if (! $path || ! Storage::disk($media->disk())->exists($path)) {
            return back()->with('error', 'Arsip belum siap. Klik "Buat Arsip" terlebih dahulu.');
        }

        return Storage::disk($media->disk())->download($path, 'arsip-'.$event->slug.'.zip');
    }

    public function approveGuestPhoto(GuestPhoto $guestPhoto): RedirectResponse
    {
        $this->authorizeClient($guestPhoto->event);

        $guestPhoto->update(['is_approved' => true]);

        return back()->with('status', 'Foto ditampilkan kembali.');
    }

    public function hideGuestPhoto(GuestPhoto $guestPhoto): RedirectResponse
    {
        $this->authorizeClient($guestPhoto->event);

        $guestPhoto->update(['is_approved' => false]);

        return back()->with('status', 'Foto disembunyikan.');
    }

    private function authorizeClient(Event $event): void
    {
        abort_unless(auth('client')->user()->can('view', $event), 403);
    }
}
