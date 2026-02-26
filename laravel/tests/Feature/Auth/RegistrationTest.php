<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_with_service_assigned(): void
    {
        $service = Service::query()->create(['name' => 'Urgencias']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'service_id' => $service->id,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');

        $user = User::query()->where('email', 'test@example.com')->firstOrFail();
        $this->assertSame(UserRole::JEFE_SERVICIO, $user->role);
        $this->assertSame($service->id, $user->service_id);
    }

    public function test_registration_requires_service_id(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('service_id');
        $this->assertGuest();
    }
}
