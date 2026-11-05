<?php

namespace Tests\Feature;

use App\Modules\Core\Models\AdminUser;
use App\Modules\Core\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_client(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.clients.store'), [
                'name' => 'Siti',
                'phone' => '08123456789',
                'email' => 'siti@example.com',
                'notes' => 'Paket wedding',
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.clients.index'));

        $this->assertDatabaseHas('clients', [
            'name' => 'Siti',
            'phone' => '08123456789',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_update_client(): void
    {
        $client = Client::create([
            'name' => 'Siti',
            'phone' => '08123456789',
            'status' => 'active',
        ]);

        $this->actingAs($this->admin(), 'admin')
            ->put(route('admin.clients.update', $client), [
                'name' => 'Siti Aminah',
                'phone' => '0899999999',
                'email' => null,
                'notes' => null,
                'status' => 'inactive',
            ])
            ->assertRedirect(route('admin.clients.index'));

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Siti Aminah',
            'status' => 'inactive',
        ]);
    }

    public function test_admin_can_delete_client_without_events(): void
    {
        $client = Client::create([
            'name' => 'Siti',
            'phone' => '08123456789',
            'status' => 'active',
        ]);

        $this->actingAs($this->admin(), 'admin')
            ->delete(route('admin.clients.destroy', $client))
            ->assertRedirect(route('admin.clients.index'));

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
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
