<?php

namespace Tests\Feature;

use App\Modules\Core\Models\Client;
use App\Modules\Core\Models\Event;
use App\Modules\Core\Models\EventModule;
use App\Modules\GuestPhotographer\Events\GuestPhotoUploaded;
use App\Modules\GuestPhotographer\Jobs\CompressGuestPhotoJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class GuestPhotographerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_page_renders(): void
    {
        $event = $this->event();

        $this->get(route('guest.show', $event->slug))
            ->assertOk()
            ->assertSee('Jadi Fotografer');
    }

    public function test_guest_can_upload_photo_and_see_gallery(): void
    {
        Storage::fake('public');
        Queue::fake();
        EventFacade::fake([GuestPhotoUploaded::class]);

        $event = $this->event();

        $url = URL::temporarySignedRoute('guest.store', now()->addHour(), ['event' => $event->slug]);

        $this->post($url, [
            'photo' => UploadedFile::fake()->image('tamu.jpg', 800, 1000),
            'device_fingerprint' => 'device-1',
        ])->assertOk()->assertJsonStructure(['id', 'url']);

        $this->assertDatabaseHas('guest_photos', [
            'event_id' => $event->id,
            'device_fingerprint' => 'device-1',
            'is_approved' => true,
        ]);

        Queue::assertPushed(CompressGuestPhotoJob::class);
        EventFacade::assertDispatched(GuestPhotoUploaded::class);
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
            'storage_expires_at' => '2027-03-01',
            'status' => 'active',
        ]);

        foreach (EventModule::MODULE_KEYS as $key) {
            EventModule::create([
                'event_id' => $event->id,
                'module_key' => $key,
                'is_active' => $key === EventModule::GUEST_PHOTOGRAPHER,
            ]);
        }

        return $event;
    }
}
