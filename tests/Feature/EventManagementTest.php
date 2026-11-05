<?php

namespace Tests\Feature;

use App\Modules\Core\Models\AdminUser;
use App\Modules\Core\Models\Client;
use App\Modules\Core\Models\Event;
use App\Modules\Core\Models\EventModule;
use App\Modules\Core\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_event_with_modules(): void
    {
        $client = $this->client();
        $package = $this->package();

        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.events.store'), [
                'client_id' => $client->id,
                'package_id' => $package->id,
                'name' => 'Pernikahan Siti & Budi',
                'event_date' => '2026-12-01',
                'status' => 'active',
                'storage_days' => 30,
                'modules' => ['photobooth', 'invitation'],
            ])
            ->assertRedirect();

        $event = Event::firstOrFail();

        $this->assertStringStartsWith('pernikahan-siti-budi-', $event->slug);
        $this->assertSame('2026-12-31', $event->storage_expires_at->format('Y-m-d'));

        $this->assertDatabaseHas('event_modules', [
            'event_id' => $event->id,
            'module_key' => EventModule::PHOTOBOOTH,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('event_modules', [
            'event_id' => $event->id,
            'module_key' => EventModule::VOICE_NOTE,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_preview_draft_event_with_inactive_invitation_module(): void
    {
        $event = $this->event(['status' => 'draft']);
        $event->modules()->update(['is_active' => false]);

        $this->actingAs($this->admin(), 'admin')
            ->get(route('invitation.show', $event->slug))
            ->assertOk();

        auth('admin')->logout();

        $this->get(route('invitation.show', $event->slug))->assertNotFound();
    }

    public function test_admin_event_detail_renders(): void
    {
        $event = $this->event();

        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.events.show', $event))
            ->assertOk()
            ->assertSee('Undangan Digital')
            ->assertSee('Frame Photobooth')
            ->assertSee('Kotak Ucapan');
    }

    public function test_admin_can_download_event_qr_code(): void
    {
        $event = $this->event();

        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.events.qr', $event))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml')
            ->assertSee('<svg', false);
    }

    public function test_active_event_landing_page_is_public(): void
    {
        $event = $this->event(['status' => 'active']);

        $this->get(route('events.landing', $event->slug))
            ->assertOk()
            ->assertSee($event->name);
    }

    public function test_draft_event_landing_page_is_not_found(): void
    {
        $event = $this->event(['status' => 'draft']);

        $this->get(route('events.landing', $event->slug))->assertNotFound();
    }

    private function event(array $attributes = []): Event
    {
        $event = Event::create(array_merge([
            'client_id' => $this->client()->id,
            'package_id' => $this->package()->id,
            'name' => 'Pernikahan Siti & Budi',
            'slug' => 'pernikahan-siti-budi-test',
            'event_date' => '2026-12-01',
            'storage_expires_at' => '2027-03-01',
            'status' => 'active',
        ], $attributes));

        foreach (EventModule::MODULE_KEYS as $moduleKey) {
            EventModule::create([
                'event_id' => $event->id,
                'module_key' => $moduleKey,
                'is_active' => in_array($moduleKey, ['photobooth', 'invitation'], true),
            ]);
        }

        return $event;
    }

    private function client(): Client
    {
        return Client::create([
            'name' => 'Siti',
            'phone' => '08123456789',
            'status' => 'active',
        ]);
    }

    private function package(): Package
    {
        return Package::create([
            'name' => 'Wedding',
            'default_modules' => ['invitation', 'photobooth'],
            'default_storage_days' => 90,
        ]);
    }

    private function admin(): AdminUser
    {
        return AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'secret123',
        ]);
    }
}
