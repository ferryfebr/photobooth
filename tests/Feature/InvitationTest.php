<?php

namespace Tests\Feature;

use App\Modules\Core\Models\AdminUser;
use App\Modules\Core\Models\Client;
use App\Modules\Core\Models\Event;
use App\Modules\Core\Models\EventModule;
use App\Modules\Invitation\Events\RsvpSubmitted;
use App\Modules\Invitation\Models\InvitationDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitation_page_renders_event_details(): void
    {
        $event = $this->event();

        InvitationDetail::create([
            'event_id' => $event->id,
            'headline' => 'Siti & Budi',
            'venue_name' => 'Gedung Serbaguna',
            'venue_address' => 'Jl. Mawar No. 1',
        ]);

        $this->get(route('invitation.show', $event->slug))
            ->assertOk()
            ->assertSee('Siti & Budi')
            ->assertSee('Gedung Serbaguna');
    }

    public function test_admin_can_open_invitation_settings(): void
    {
        $event = $this->event();
        $admin = AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'secret123',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.invitations.edit', $event))
            ->assertOk()
            ->assertSee('Simpan Undangan');
    }

    public function test_rsvp_requires_signed_url(): void
    {
        $event = $this->event();

        $this->post(route('invitation.rsvp', $event->slug), [
            'guest_name' => 'Tamu',
            'attendance' => 'hadir',
        ])->assertForbidden();
    }

    public function test_guest_can_submit_rsvp(): void
    {
        $event = $this->event();
        EventFacade::fake([RsvpSubmitted::class]);

        $url = URL::temporarySignedRoute('invitation.rsvp', now()->addHour(), ['event' => $event->slug]);

        $this->post($url, [
            'guest_name' => 'Tamu Undangan',
            'attendance' => 'hadir',
            'guest_count' => 2,
            'note' => 'Datang bersama pasangan',
            'device_fingerprint' => 'device-1',
        ])->assertRedirect();

        $this->assertDatabaseHas('rsvps', [
            'event_id' => $event->id,
            'guest_name' => 'Tamu Undangan',
            'attendance' => 'hadir',
            'guest_count' => 2,
        ]);

        EventFacade::assertDispatched(RsvpSubmitted::class);
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
                'is_active' => $key === EventModule::INVITATION,
            ]);
        }

        return $event;
    }
}
