<?php

namespace App\Modules\GuestPhotographer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Models\ActivityLog;
use App\Modules\Core\Models\Event;
use App\Modules\Core\Models\EventModule;
use App\Modules\GuestPhotographer\Events\GuestPhotoUploaded;
use App\Modules\GuestPhotographer\Http\Requests\StoreGuestPhotoRequest;
use App\Modules\GuestPhotographer\Jobs\CompressGuestPhotoJob;
use App\Modules\GuestPhotographer\Models\GuestPhoto;
use App\Support\MediaStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class GuestPhotographerController extends Controller
{
    public function show(Event $event): View
    {
        $isPreview = auth('admin')->check();

        abort_unless($isPreview || in_array($event->status, ['active', 'completed'], true), 404);
        abort_unless($isPreview || $event->isModuleActive('guest_photographer'), 404);

        return view('guest-photographer.show', [
            'event' => $event,
            'storeUrl' => URL::temporarySignedRoute('guest.store', now()->addHours(6), ['event' => $event->slug]),
        ]);
    }

    public function store(StoreGuestPhotoRequest $request, Event $event, MediaStorage $media): JsonResponse
    {
        abort_unless($event->isModuleActive('guest_photographer'), 404);

        $fingerprint = $request->input('device_fingerprint');

        if ($fingerprint && GuestPhoto::where('event_id', $event->id)->where('device_fingerprint', $fingerprint)->count() >= 10) {
            return response()->json(['message' => 'Batas 10 foto per tamu sudah tercapai.'], 422);
        }

        $photo = GuestPhoto::create([
            'event_id' => $event->id,
            'file_path' => $media->store($request->file('photo'), 'guest-photos'),
            'device_fingerprint' => $fingerprint,
            'is_approved' => true,
        ]);

        CompressGuestPhotoJob::dispatch($photo->file_path);
        GuestPhotoUploaded::dispatch($photo);
        ActivityLog::record($event->id, EventModule::GUEST_PHOTOGRAPHER, 'guest_photo_uploaded');

        return response()->json([
            'message' => 'Foto berhasil diunggah.',
            'id' => $photo->id,
            'url' => $media->url($photo->file_path),
        ]);
    }
}
