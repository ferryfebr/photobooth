<?php

namespace Tests\Feature;

use App\Modules\Core\Models\Client;
use App\Modules\Core\Models\Event;
use App\Modules\Core\Models\EventModule;
use App\Modules\VoiceNote\Events\VoiceNoteReceived;
use App\Modules\VoiceNote\Jobs\ConvertAudioJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class VoiceNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_voice_note_page_renders(): void
    {
        $event = $this->event();

        $this->get(route('voicenote.show', $event->slug))
            ->assertOk()
            ->assertSee('Voice Note Ucapan');
    }

    public function test_guest_can_submit_voice_note_with_codec_mime_type(): void
    {
        Storage::fake('public');
        Queue::fake();
        EventFacade::fake([VoiceNoteReceived::class]);

        $event = $this->event();
        $url = URL::temporarySignedRoute('voicenote.store', now()->addHour(), ['event' => $event->slug]);

        $this->post($url, [
            'audio' => UploadedFile::fake()->create('ucapan.webm', 100, 'audio/webm;codecs=opus'),
            'sender_name' => 'Tamu Codec',
            'duration_seconds' => 5,
            'device_fingerprint' => 'device-codec',
        ])->assertOk()->assertJsonStructure(['id']);

        $this->assertDatabaseHas('voice_notes', [
            'event_id' => $event->id,
            'sender_name' => 'Tamu Codec',
        ]);
    }

    public function test_guest_can_submit_voice_note(): void
    {
        Storage::fake('public');
        Queue::fake();
        EventFacade::fake([VoiceNoteReceived::class]);

        $event = $this->event();

        $url = URL::temporarySignedRoute('voicenote.store', now()->addHour(), ['event' => $event->slug]);

        $this->post($url, [
            'audio' => UploadedFile::fake()->create('ucapan.webm', 100, 'audio/webm'),
            'sender_name' => 'Tamu',
            'duration_seconds' => 12,
            'device_fingerprint' => 'device-1',
        ])->assertOk()->assertJsonStructure(['id']);

        $this->assertDatabaseHas('voice_notes', [
            'event_id' => $event->id,
            'sender_name' => 'Tamu',
            'duration_seconds' => 12,
        ]);

        Queue::assertPushed(ConvertAudioJob::class);
        EventFacade::assertDispatched(VoiceNoteReceived::class);
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
                'is_active' => $key === EventModule::VOICE_NOTE,
            ]);
        }

        return $event;
    }
}
