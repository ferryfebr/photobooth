<?php

namespace App\Modules\Core\Jobs;

use App\Modules\Core\Models\Event;
use App\Support\MediaStorage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class PurgeExpiredEventDataJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function handle(MediaStorage $media): void
    {
        Event::query()
            ->where('storage_expires_at', '<', now())
            ->whereIn('status', ['completed', 'archived'])
            ->with(['frames', 'photoboothSessions.photos', 'guestPhotos', 'voiceNotes'])
            ->chunkById(25, function ($events) use ($media) {
                foreach ($events as $event) {
                    foreach ($event->frames as $frame) {
                        $media->delete($frame->file_path);
                    }

                    foreach ($event->photoboothSessions as $session) {
                        foreach ($session->photos as $photo) {
                            $media->delete($photo->file_path);
                        }

                        $media->delete($session->result_file_path);
                    }

                    foreach ($event->guestPhotos as $photo) {
                        $media->delete($photo->file_path);
                    }

                    foreach ($event->voiceNotes as $voiceNote) {
                        $media->delete($voiceNote->file_path);
                        $media->delete($voiceNote->original_file_path);
                    }

                    $event->delete();
                }
            });
    }

    public function failed(Throwable $exception): void
    {
        Log::error('PurgeExpiredEventDataJob gagal', [
            'message' => $exception->getMessage(),
        ]);
    }
}
