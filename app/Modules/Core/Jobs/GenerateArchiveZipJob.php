<?php

namespace App\Modules\Core\Jobs;

use App\Modules\Core\Models\Event;
use App\Modules\Core\Services\ArchiveExportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateArchiveZipJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 300;

    public function __construct(public int $eventId, public string $cacheKey) {}

    public function handle(ArchiveExportService $archives): void
    {
        $event = Event::find($this->eventId);

        if (! $event) {
            return;
        }

        $path = $archives->build($event);
        Cache::put($this->cacheKey, $path, now()->addHour());
    }

    public function failed(Throwable $exception): void
    {
        Cache::forget($this->cacheKey);
        Log::error('GenerateArchiveZipJob gagal', [
            'event_id' => $this->eventId,
            'message' => $exception->getMessage(),
        ]);
    }
}
