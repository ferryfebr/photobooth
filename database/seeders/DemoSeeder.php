<?php

namespace Database\Seeders;

use App\Modules\Core\Models\ActivityLog;
use App\Modules\Core\Models\Client;
use App\Modules\Core\Models\Event;
use App\Modules\Core\Models\EventModule;
use App\Modules\Core\Models\Package;
use App\Modules\GuestPhotographer\Models\GuestPhoto;
use App\Modules\Invitation\Models\InvitationDetail;
use App\Modules\Invitation\Models\Rsvp;
use App\Modules\Photobooth\Models\Frame;
use App\Modules\VoiceNote\Jobs\ConvertAudioJob;
use App\Modules\VoiceNote\Models\VoiceNote;
use App\Support\MediaStorage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Throwable;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([AdminUserSeeder::class, PackageSeeder::class]);

        $client = Client::updateOrCreate(
            ['email' => 'client@photobooth.test'],
            [
                'name' => 'Siti & Budi',
                'phone' => '6281234567890',
                'password' => 'password',
                'notes' => 'Client demo lokal.',
                'status' => 'active',
            ],
        );

        $package = Package::where('name', 'Wedding')->first();
        $event = Event::updateOrCreate(
            ['slug' => 'demo-siti-budi'],
            [
                'client_id' => $client->id,
                'package_id' => $package?->id,
                'name' => 'Pernikahan Siti & Budi',
                'theme_slug' => null,
                'event_date' => now()->addDays(14)->toDateString(),
                'photobooth_active_from' => now()->subDay(),
                'photobooth_active_until' => now()->addDays(2),
                'storage_expires_at' => now()->addDays(104),
                'status' => 'active',
            ],
        );

        foreach (EventModule::MODULE_KEYS as $moduleKey) {
            EventModule::updateOrCreate(
                ['event_id' => $event->id, 'module_key' => $moduleKey],
                ['is_active' => true],
            );
        }

        InvitationDetail::updateOrCreate(
            ['event_id' => $event->id],
            [
                'headline' => 'Siti & Budi',
                'description' => 'Dengan penuh syukur kami mengundang Anda untuk hadir di hari bahagia kami.',
                'story' => 'Pertemuan sederhana di kampus tumbuh menjadi cerita yang kami bawa sampai hari ini.',
                'venue_name' => 'Gedung Serbaguna Melati',
                'venue_address' => 'Jl. Melati No. 10, Bandung',
                'maps_url' => 'https://maps.google.com',
                'gift_info' => "BCA 1234567890 a.n. Siti\nDANA 081234567890 a.n. Budi",
            ],
        );

        $this->seedFrame($event);
        $this->seedGuestPhotos($event);
        $this->seedVoiceNote($event);

        if ($event->rsvps()->count() === 0) {
            Rsvp::create([
                'event_id' => $event->id,
                'guest_name' => 'Rina & Keluarga',
                'attendance' => 'hadir',
                'guest_count' => 3,
                'note' => 'Siap datang tepat waktu.',
            ]);

            Rsvp::create([
                'event_id' => $event->id,
                'guest_name' => 'Dimas',
                'attendance' => 'tidak_hadir',
                'guest_count' => 1,
                'note' => 'Berhalangan, doa terbaik dari jauh.',
            ]);
        }

        if ($event->activityLogs()->count() === 0) {
            ActivityLog::record($event->id, EventModule::INVITATION, 'rsvp_submitted');
            ActivityLog::record($event->id, EventModule::GUEST_PHOTOGRAPHER, 'guest_photo_uploaded');
            ActivityLog::record($event->id, EventModule::VOICE_NOTE, 'voice_note_recorded');
        }
    }

    private function seedFrame(Event $event): void
    {
        Storage::disk(config('media.disk'))->makeDirectory('frames');

        $framePath = storage_path('app/public/frames/demo-frame.png');

        if (! is_file($framePath)) {
            $width = 800;
            $height = 1200;
            $image = imagecreatetruecolor($width, $height);
            imagesavealpha($image, true);
            imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));

            $white = imagecolorallocate($image, 255, 255, 255);
            $dark = imagecolorallocate($image, 30, 41, 59);

            imagefilledrectangle($image, 0, 0, $width, 120, $white);
            imagefilledrectangle($image, 0, 600, $width, 640, $white);
            imagefilledrectangle($image, 0, 1120, $width, $height, $white);
            imagefilledrectangle($image, 0, 0, 40, $height, $white);
            imagefilledrectangle($image, $width - 40, 0, $width, $height, $white);

            imagestring($image, 5, 330, 50, 'SITI & BUDI', $dark);
            imagestring($image, 3, 330, 1160, 'PHOTOBOOTH', $dark);

            imagepng($image, $framePath);
            imagedestroy($image);
        }

        Frame::updateOrCreate(
            ['event_id' => $event->id, 'name' => 'Demo Strip 2 Foto'],
            [
                'file_path' => 'frames/demo-frame.png',
                'slot_coordinates' => [
                    ['x' => 50, 'y' => 140, 'width' => 700, 'height' => 440],
                    ['x' => 50, 'y' => 650, 'width' => 700, 'height' => 440],
                ],
                'photo_count' => 2,
            ],
        );
    }

    private function seedGuestPhotos(Event $event): void
    {
        if ($event->guestPhotos()->count() > 0) {
            return;
        }

        Storage::disk(config('media.disk'))->makeDirectory('guest-photos');

        $colors = [[244, 114, 182], [96, 165, 250], [52, 211, 153]];

        foreach ($colors as $index => $rgb) {
            $path = storage_path('app/public/guest-photos/demo-'.($index + 1).'.jpg');
            $image = imagecreatetruecolor(800, 800);
            $color = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
            imagefill($image, 0, 0, $color);
            $white = imagecolorallocate($image, 255, 255, 255);
            imagestring($image, 5, 340, 390, 'TAMU '.($index + 1), $white);
            imagejpeg($image, $path, 85);
            imagedestroy($image);

            GuestPhoto::create([
                'event_id' => $event->id,
                'file_path' => 'guest-photos/demo-'.($index + 1).'.jpg',
                'device_fingerprint' => 'demo-device-'.$index,
                'is_approved' => true,
            ]);
        }
    }

    private function seedVoiceNote(Event $event): void
    {
        if ($event->voiceNotes()->count() > 0) {
            return;
        }

        $ffmpeg = config('media.ffmpeg_path');
        $temporary = tempnam(sys_get_temp_dir(), 'demo-vn-').'.webm';

        try {
            $process = new Process([
                $ffmpeg,
                '-y',
                '-f', 'lavfi',
                '-i', 'sine=frequency=440:duration=2',
                '-c:a', 'libvorbis',
                $temporary,
            ]);
            $process->setTimeout(30);
            $process->run();

            if (! $process->isSuccessful() || ! is_file($temporary)) {
                return;
            }

            Storage::disk(config('media.disk'))->makeDirectory('voice-notes');

            $path = 'voice-notes/demo-ucapan.webm';
            Storage::disk(config('media.disk'))->put($path, file_get_contents($temporary));

            $voiceNote = VoiceNote::create([
                'event_id' => $event->id,
                'sender_name' => 'Rina',
                'file_path' => $path,
                'original_file_path' => $path,
                'mime_type' => 'audio/webm',
                'duration_seconds' => 2,
                'device_fingerprint' => 'demo-device-vn',
            ]);

            (new ConvertAudioJob($voiceNote->id))->handle(app(MediaStorage::class));
        } catch (Throwable) {
            //
        } finally {
            @unlink($temporary);
        }
    }
}
