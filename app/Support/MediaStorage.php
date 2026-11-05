<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaStorage
{
    public function disk(): string
    {
        return config('media.disk', 'public');
    }

    public function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, $this->disk());
    }

    public function storeAs(UploadedFile $file, string $directory, string $extension): string
    {
        return $file->storeAs($directory, Str::uuid().'.'.$extension, $this->disk());
    }

    public function storeContents(string $directory, string $extension, string $contents): string
    {
        $path = $directory.'/'.Str::uuid().'.'.$extension;

        Storage::disk($this->disk())->put($path, $contents);

        return $path;
    }

    public function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $disk = Storage::disk($this->disk());

        if ($disk->providesTemporaryUrls()) {
            return $disk->temporaryUrl($path, now()->addHour());
        }

        return $disk->url($path);
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk($this->disk())->delete($path);
        }
    }

    public function get(string $path): ?string
    {
        $disk = Storage::disk($this->disk());

        return $disk->exists($path) ? $disk->get($path) : null;
    }
}
