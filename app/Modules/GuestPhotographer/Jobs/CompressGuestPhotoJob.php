<?php

namespace App\Modules\GuestPhotographer\Jobs;

use App\Support\MediaStorage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

class CompressGuestPhotoJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public string $path) {}

    public function handle(MediaStorage $media): void
    {
        $disk = Storage::disk($media->disk());

        if (! $disk->exists($this->path)) {
            return;
        }

        $manager = new ImageManager(new Driver);
        $image = $manager->read($disk->get($this->path));
        $image->scaleDown(width: 1600, height: 1600);

        $disk->put($this->path, $image->toJpeg(82)->toString());
    }

    public function failed(Throwable $exception): void
    {
        Log::error('CompressGuestPhotoJob gagal', ['path' => $this->path, 'message' => $exception->getMessage()]);
    }
}
