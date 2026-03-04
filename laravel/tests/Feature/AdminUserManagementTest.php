<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_users_routes(): void
    {
        $user = User::factory()->create(['role' => UserRole::RRHH]);

        $this->actingAs($user)
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_admin_can_create_rrhh_user_without_service(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $response = $this->actingAs($admin)
            ->post('/admin/users', [
                'name' => 'Usuario RRHH',
                'email' => 'nuevo.rrhh@example.com',
                'role' => UserRole::RRHH->value,
                'service_id' => Service::query()->create(['name' => 'Pabellón'])->id,
                'password' => 'Password123!',
            ]);

        $response->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', [
            'email' => 'nuevo.rrhh@example.com',
            'role' => UserRole::RRHH->value,
            'service_id' => null,
        ]);
    }

    public function test_jefe_servicio_requires_service_id(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $response = $this->actingAs($admin)
            ->from('/admin/users/create')
            ->post('/admin/users', [
                'name' => 'Jefe sin servicio',
                'email' => 'jefe.sin.servicio@example.com',
                'role' => UserRole::JEFE_SERVICIO->value,
                'password' => 'Password123!',
            ]);

        $response->assertRedirect('/admin/users/create');
        $response->assertSessionHasErrors('service_id');
    }

    public function test_admin_can_soft_delete_another_user(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $userToDelete = User::factory()->create(['role' => UserRole::RRHH]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $userToDelete));

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('status', 'Usuario eliminado correctamente.');

        $this->assertSoftDeleted('users', [
            'id' => $userToDelete->id,
        ]);
    }

    public function test_admin_cannot_delete_himself(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $response = $this->actingAs($admin)
            ->from('/admin/users')
            ->delete(route('admin.users.destroy', $admin));

        $response->assertRedirect('/admin/users');
        $response->assertSessionHasErrors('delete_user');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'deleted_at' => null,
        ]);
    }

}
