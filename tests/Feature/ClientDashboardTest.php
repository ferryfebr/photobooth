<?php

namespace Tests\Feature;

use App\Modules\Core\Models\Client;
use App\Modules\Core\Models\Event;
use App\Modules\Core\Models\EventModule;
use App\Modules\GuestPhotographer\Models\GuestPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_client_login(): void
    {
        $this->get(route('client.dashboard'))->assertRedirect(route('client.login'));
    }

    public function test_client_can_login_and_see_own_event(): void
    {
        $client = $this->client('client@example.com');
        $event = $this->event($client, 'event-own');

        $this->post(route('client.login.store'), [
            'email' => 'client@example.com',
            'password' => 'secret123',
        ])->assertRedirect(route('client.dashboard'));

        $this->assertAuthenticatedAs($client, 'client');

        $this->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee($event->name);

        $this->get(route('client.events.show', $event))
            ->assertOk()
            ->assertSee($event->name);
    }

    public function test_client_cannot_view_another_client_event(): void
    {
        $owner = $this->client('owner@example.com');
        $other = $this->client('other@example.com');
        $event = $this->event($owner, 'event-owner');

        $this->actingAs($other, 'client')
            ->get(route('client.events.show', $event))
            ->assertForbidden();
    }

    public function test_client_can_hide_guest_photo(): void
    {
        Storage::fake('public');

        $client = $this->client('client@example.com');
        $event = $this->event($client, 'event-own');

        $photo = GuestPhoto::create([
            'event_id' => $event->id,
            'file_path' => 'guest-photos/foto.jpg',
            'is_approved' => true,
        ]);

        $this->actingAs($client, 'client')
            ->post(route('client.guest-photos.hide', $photo))
            ->assertRedirect();

        $this->assertFalse($photo->fresh()->is_approved);
    }

    private function client(string $email): Client
    {
        return Client::create([
            'name' => 'Client '.$email,
            'phone' => '08123456789',
            'email' => $email,
            'password' => 'secret123',
            'status' => 'active',
        ]);
    }

    private function event(Client $client, string $slug): Event
    {
        $event = Event::create([
            'client_id' => $client->id,
            'name' => 'Event '.$slug,
            'slug' => $slug,
            'event_date' => '2026-12-01',
            'storage_expires_at' => '2027-03-01',
            'status' => 'active',
        ]);

        foreach (EventModule::MODULE_KEYS as $key) {
            EventModule::create([
                'event_id' => $event->id,
                'module_key' => $key,
                'is_active' => true,
            ]);
        }

        return $event;
    }
}
