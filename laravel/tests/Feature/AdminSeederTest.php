<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RoleUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_users_seeder_creates_admin_user(): void
    {
        $this->seed(RoleUsersSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@demo.cl',
            'name' => 'Administrador',
            'role' => UserRole::ADMIN->value,
            'service_id' => null,
        ]);

        $admin = User::query()->where('email', 'admin@demo.cl')->first();

        $this->assertNotNull($admin);
        $this->assertTrue(password_verify('Password123!', $admin->password));
    }
}
