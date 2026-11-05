<?php

namespace Tests\Feature;

use App\Modules\Core\Models\Client;
use App\Modules\Core\Models\Event;
use App\Modules\Core\Services\ArchiveExportService;
use App\Modules\GuestPhotographer\Models\GuestPhoto;
use App\Modules\Photobooth\Models\PhotoboothPhoto;
use App\Modules\Photobooth\Models\PhotoboothSession;
use App\Modules\VoiceNote\Models\VoiceNote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class ArchiveExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_archive_contains_all_event_media(): void
    {
        Storage::fake('public');

        $client = Client::create([
            'name' => 'Siti',
            'phone' => '08123456789',
            'status' => 'active',
        ]);

        $event = Event::create([
            'client_id' => $client->id,
            'name' => 'Pernikahan Siti & Budi',
            'slug' => 'pernikahan-siti-budi-test',
            'event_date' => '2026-12-01',
            'storage_expires_at' => '2027-03-01',
            'status' => 'active',
        ]);

        Storage::disk('public')->put('photobooth/raw/foto-1.jpg', 'raw-photo');
        Storage::disk('public')->put('photobooth/results/hasil.jpg', 'result-photo');
        Storage::disk('public')->put('guest-photos/tamu.jpg', 'guest-photo');
        Storage::disk('public')->put('voice-notes/ucapan.webm', 'voice-note');

        $session = PhotoboothSession::create([
            'event_id' => $event->id,
            'status' => 'composed',
            'result_file_path' => 'photobooth/results/hasil.jpg',
        ]);

        PhotoboothPhoto::create([
            'photobooth_session_id' => $session->id,
            'file_path' => 'photobooth/raw/foto-1.jpg',
            'order' => 1,
        ]);

        GuestPhoto::create([
            'event_id' => $event->id,
            'file_path' => 'guest-photos/tamu.jpg',
        ]);

        VoiceNote::create([
            'event_id' => $event->id,
            'file_path' => 'voice-notes/ucapan.webm',
            'duration_seconds' => 10,
        ]);

        $path = app(ArchiveExportService::class)->build($event);

        $this->assertTrue(Storage::disk('public')->exists($path));

        $temporary = tempnam(sys_get_temp_dir(), 'archive-test-');
        file_put_contents($temporary, Storage::disk('public')->get($path));

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($temporary) === true);
        $this->assertGreaterThanOrEqual(4, $zip->numFiles);
        $zip->close();

        @unlink($temporary);
    }
}
