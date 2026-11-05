<?php

namespace Tests\Feature;

use App\Modules\Core\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BroadcastingAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'test-key',
            'broadcasting.connections.reverb.secret' => 'test-secret',
            'broadcasting.connections.reverb.app_id' => '1',
        ]);
    }

    public function test_admin_can_authorize_event_private_channel(): void
    {
        $admin = AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'secret123',
        ]);

        $this->withSession([
            app('auth')->guard('admin')->getName() => $admin->id,
        ]);

        $this->postJson('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-event.1',
        ])
            ->assertOk()
            ->assertJsonStructure(['auth']);
    }

    public function test_guest_cannot_authorize_event_private_channel(): void
    {
        $this->postJson('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-event.1',
        ])->assertForbidden();
    }
}
