<?php

namespace App\Modules\Photobooth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Models\ActivityLog;
use App\Modules\Core\Models\Event;
use App\Modules\Core\Models\EventModule;
use App\Modules\Photobooth\Events\PhotoboothSessionCompleted;
use App\Modules\Photobooth\Http\Requests\StorePhotoboothRequest;
use App\Modules\Photobooth\Jobs\CompressPhotoJob;
use App\Modules\Photobooth\Models\Frame;
use App\Modules\Photobooth\Models\PhotoboothPhoto;
use App\Modules\Photobooth\Models\PhotoboothSession;
use App\Support\MediaStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PhotoboothController extends Controller
{
    public function show(Event $event, MediaStorage $media): View
    {
        $isPreview = auth('admin')->check();

        abort_unless($isPreview || in_array($event->status, ['active', 'completed'], true), 404);
        abort_unless($isPreview || $event->isModuleActive('photobooth'), 404);

        $event->load('frames');

        return view('photobooth.show', [
            'event' => $event,
            'isOpen' => $event->photoboothIsOpen(),
            'storeUrl' => URL::temporarySignedRoute('photobooth.store', now()->addHours(6), ['event' => $event->slug]),
            'frames' => $event->frames->map(fn (Frame $frame) => [
                'id' => $frame->id,
                'name' => $frame->name,
                'url' => $media->url($frame->file_path),
                'photo_count' => $frame->photo_count,
                'slots' => $frame->slot_coordinates,
            ])->values(),
        ]);
    }

    public function store(StorePhotoboothRequest $request, Event $event, MediaStorage $media): JsonResponse
    {
        abort_unless($event->isModuleActive('photobooth'), 404);
        abort_unless($event->photoboothIsOpen(), 403, 'Photobooth sedang tidak aktif.');

        $fingerprint = $request->input('device_fingerprint');

        if ($fingerprint && PhotoboothSession::where('event_id', $event->id)->where('device_fingerprint', $fingerprint)->count() >= 5) {
            return response()->json(['message' => 'Batas 5 sesi photobooth per perangkat sudah tercapai.'], 429);
        }

        $frame = Frame::where('event_id', $event->id)->findOrFail($request->integer('frame_id'));
        $photos = $request->file('photos', []);

        if (count($photos) !== $frame->photo_count) {
            return response()->json([
                'message' => 'Jumlah foto tidak sesuai dengan frame.',
            ], 422);
        }

        $session = PhotoboothSession::create([
            'event_id' => $event->id,
            'frame_id' => $frame->id,
            'device_fingerprint' => $fingerprint,
            'status' => 'composed',
            'result_file_path' => $media->store($request->file('result'), 'photobooth/results'),
        ]);

        foreach ($photos as $index => $photo) {
            $path = $media->store($photo, 'photobooth/raw');

            PhotoboothPhoto::create([
                'photobooth_session_id' => $session->id,
                'file_path' => $path,
                'order' => $index + 1,
            ]);

            CompressPhotoJob::dispatch($path);
        }

        CompressPhotoJob::dispatch($session->result_file_path);
        PhotoboothSessionCompleted::dispatch($session);
        ActivityLog::record($event->id, EventModule::PHOTOBOOTH, 'photo_captured');

        return response()->json([
            'message' => 'Foto berhasil disimpan.',
            'download_url' => URL::temporarySignedRoute('photobooth.result', now()->addHours(6), [
                'event' => $event->slug,
                'session' => $session->id,
            ]),
        ]);
    }

    public function result(Event $event, PhotoboothSession $session, MediaStorage $media): StreamedResponse
    {
        abort_unless($session->event_id === $event->id, 404);
        abort_unless($session->result_file_path, 404);

        return Storage::disk($media->disk())->download(
            $session->result_file_path,
            'photobooth-'.$session->id.'.jpg',
        );
    }
}
