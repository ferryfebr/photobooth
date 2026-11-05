<?php

namespace App\Modules\Invitation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Models\ActivityLog;
use App\Modules\Core\Models\Event;
use App\Modules\Core\Models\EventModule;
use App\Modules\Core\Services\ThemeResolverService;
use App\Modules\Invitation\Events\RsvpSubmitted;
use App\Modules\Invitation\Http\Requests\StoreRsvpRequest;
use App\Modules\Invitation\Models\Rsvp;
use App\Support\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function show(Event $event, ThemeResolverService $themes, MediaStorage $media): View
    {
        $isPreview = auth('admin')->check();

        abort_unless($isPreview || in_array($event->status, ['active', 'completed'], true), 404);
        abort_unless($isPreview || $event->isModuleActive('invitation'), 404);

        $event->load(['client', 'invitationDetail', 'rsvps', 'modules']);
        $detail = $event->invitationDetail;

        return view($themes->resolve($event->theme_slug), [
            'event' => $event,
            'detail' => $detail,
            'coverUrl' => $media->url($detail?->cover_image_path),
            'galleryUrls' => collect($detail?->gallery_images ?? [])
                ->map(fn (string $path) => $media->url($path))
                ->filter()
                ->values(),
            'attendingGuests' => $event->rsvps->where('attendance', 'hadir')->sum('guest_count'),
            'notAttendingCount' => $event->rsvps->where('attendance', 'tidak_hadir')->count(),
            'rsvpUrl' => URL::temporarySignedRoute('invitation.rsvp', now()->addHours(6), ['event' => $event->slug]),
        ]);
    }

    public function rsvp(StoreRsvpRequest $request, Event $event): RedirectResponse
    {
        abort_unless($event->isModuleActive('invitation'), 404);

        $fingerprint = $request->input('device_fingerprint');

        if ($fingerprint && Rsvp::where('event_id', $event->id)->where('device_fingerprint', $fingerprint)->exists()) {
            return back()->withErrors(['guest_name' => 'RSVP dari perangkat ini sudah pernah dikirim.']);
        }

        $rsvp = Rsvp::create($request->validated() + ['event_id' => $event->id]);

        RsvpSubmitted::dispatch($rsvp);
        ActivityLog::record($event->id, EventModule::INVITATION, 'rsvp_submitted');

        return back()->with('status', 'Terima kasih, konfirmasi kehadiranmu sudah tersimpan.');
    }
}
