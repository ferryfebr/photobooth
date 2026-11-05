<?php

namespace App\Modules\Core\Services;

use App\Modules\Core\Models\Event;
use App\Support\MediaStorage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class ArchiveExportService
{
    public function __construct(private MediaStorage $media) {}

    public function build(Event $event): string
    {
        $event->load(['frames', 'photoboothSessions.photos', 'guestPhotos', 'voiceNotes']);

        $temporaryPath = tempnam(sys_get_temp_dir(), 'event-archive-');
        $zip = new ZipArchive;

        if ($temporaryPath === false || $zip->open($temporaryPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Gagal membuat arsip ZIP.');
        }

        $disk = Storage::disk($this->media->disk());

        foreach ($event->photoboothSessions as $session) {
            foreach ($session->photos as $photo) {
                $this->add($zip, $disk, $photo->file_path, 'photobooth/'.$session->id.'/foto-'.$photo->order.'.jpg');
            }

            $this->add($zip, $disk, $session->result_file_path, 'photobooth/'.$session->id.'/hasil.jpg');
        }

        foreach ($event->guestPhotos as $photo) {
            $this->add($zip, $disk, $photo->file_path, 'guest-photographer/foto-'.$photo->id.'.jpg');
        }

        foreach ($event->voiceNotes as $voiceNote) {
            $extension = pathinfo($voiceNote->file_path, PATHINFO_EXTENSION) ?: 'mp3';
            $this->add($zip, $disk, $voiceNote->file_path, 'voice-note/ucapan-'.$voiceNote->id.'.'.$extension);
        }

        $zip->close();

        $path = 'archives/event-'.$event->id.'-'.Str::uuid().'.zip';
        Storage::disk($this->media->disk())->put($path, fopen($temporaryPath, 'r'));

        @unlink($temporaryPath);

        return $path;
    }

    private function add(ZipArchive $zip, $disk, ?string $path, string $name): void
    {
        if ($path && $disk->exists($path)) {
            $zip->addFromString($name, $disk->get($path));
        }
    }
}
