<?php

namespace Tests\Feature;

use App\Modules\Core\Models\AdminUser;
use App\Modules\Core\Models\Client;
use App\Modules\Core\Models\Event;
use App\Modules\Core\Models\EventModule;
use App\Modules\Photobooth\Events\PhotoboothSessionCompleted;
use App\Modules\Photobooth\Jobs\CompressPhotoJob;
use App\Modules\Photobooth\Models\Frame;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PhotoboothTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_photobooth_session(): void
    {
        Storage::fake('public');
        Queue::fake();
        EventFacade::fake([PhotoboothSessionCompleted::class]);

        $event = $this->event();
        $frame = Frame::create([
            'event_id' => $event->id,
            'name' => 'Strip 2 Foto',
            'file_path' => 'frames/strip.png',
            'photo_count' => 2,
            'slot_coordinates' => [
                ['x' => 0, 'y' => 0, 'width' => 400, 'height' => 400],
                ['x' => 0, 'y' => 400, 'width' => 400, 'height' => 400],
            ],
        ]);

        Storage::disk('public')->put('frames/strip.png', 'frame');

        $url = URL::temporarySignedRoute('photobooth.store', now()->addHour(), ['event' => $event->slug]);

        $response = $this->post($url, [
            'frame_id' => $frame->id,
            'device_fingerprint' => 'device-1',
            'photos' => [
                UploadedFile::fake()->image('foto-1.jpg', 600, 800),
                UploadedFile::fake()->image('foto-2.jpg', 600, 800),
            ],
            'result' => UploadedFile::fake()->image('hasil.jpg', 800, 1200),
        ]);

        $response->assertOk()->assertJsonStructure(['download_url']);

        $this->assertDatabaseCount('photobooth_sessions', 1);
        $this->assertDatabaseCount('photobooth_photos', 2);

        Queue::assertPushed(CompressPhotoJob::class, 3);
        EventFacade::assertDispatched(PhotoboothSessionCompleted::class);
    }

    public function test_admin_can_open_frame_form(): void
    {
        $event = $this->event();
        $admin = AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'secret123',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.frames.create', $event))
            ->assertOk()
            ->assertSee('Koordinat Slot Foto')
            ->assertSee('slots[0][x]', false);
    }

    public function test_admin_can_upload_frame_png_with_matching_slot_count(): void
    {
        Storage::fake('public');

        $event = $this->event();
        $admin = AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'secret123',
        ]);

        $temporary = tempnam(sys_get_temp_dir(), 'frame-').'.png';
        $image = imagecreatetruecolor(400, 800);
        imagepng($image, $temporary);
        imagedestroy($image);

        $file = new UploadedFile($temporary, 'frame.png', 'image/png', null, true);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.frames.store', $event), [
                'name' => 'Frame Satu Foto',
                'photo_count' => 1,
                'slots' => [
                    ['x' => 20, 'y' => 20, 'width' => 360, 'height' => 760],
                ],
                'frame' => $file,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('frames', [
            'event_id' => $event->id,
            'name' => 'Frame Satu Foto',
            'photo_count' => 1,
        ]);

        @unlink($temporary);
    }

    public function test_photobooth_show_page_renders_frames(): void
    {
        Storage::fake('public');

        $event = $this->event();

        Frame::create([
            'event_id' => $event->id,
            'name' => 'Frame Utama',
            'file_path' => 'frames/utama.png',
            'photo_count' => 1,
            'slot_coordinates' => [['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100]],
        ]);

        $this->get(route('photobooth.show', $event->slug))
            ->assertOk()
            ->assertSee('Frame Utama');
    }

    private function event(): Event
    {
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
            'photobooth_active_from' => now()->subDay(),
            'photobooth_active_until' => now()->addDay(),
            'storage_expires_at' => '2027-03-01',
            'status' => 'active',
        ]);

        foreach (EventModule::MODULE_KEYS as $key) {
            EventModule::create([
                'event_id' => $event->id,
                'module_key' => $key,
                'is_active' => $key === EventModule::PHOTOBOOTH,
            ]);
        }

        return $event;
    }
}
