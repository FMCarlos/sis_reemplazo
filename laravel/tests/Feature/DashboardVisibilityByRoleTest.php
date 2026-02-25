<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\Request;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardVisibilityByRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_jefe_servicio_only_sees_their_own_requests_in_their_service(): void
    {
        $serviceA = Service::query()->create(['name' => 'Servicio A']);
        $serviceB = Service::query()->create(['name' => 'Servicio B']);

        $jefe = User::factory()->create([
            'role' => UserRole::JEFE_SERVICIO,
            'service_id' => $serviceA->id,
        ]);

        $otherJefe = User::factory()->create([
            'role' => UserRole::JEFE_SERVICIO,
            'service_id' => $serviceA->id,
        ]);

        $visibleRequest = Request::query()->create([
            'service_id' => $serviceA->id,
            'created_by' => $jefe->id,
            'status' => RequestStatus::BORRADOR,
        ]);

        $hiddenSameServiceOtherUser = Request::query()->create([
            'service_id' => $serviceA->id,
            'created_by' => $otherJefe->id,
            'status' => RequestStatus::BORRADOR,
        ]);

        $hiddenOtherService = Request::query()->create([
            'service_id' => $serviceB->id,
            'created_by' => $jefe->id,
            'status' => RequestStatus::BORRADOR,
        ]);

        $response = $this->actingAs($jefe)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('#'.$visibleRequest->id, false);
        $response->assertDontSee('#'.$hiddenSameServiceOtherUser->id, false);
        $response->assertDontSee('#'.$hiddenOtherService->id, false);
        $response->assertSee('Rol: Jefe de Servicio');
    }

    public function test_rrhh_sees_all_requests_in_dashboard(): void
    {
        $serviceA = Service::query()->create(['name' => 'Servicio A']);
        $serviceB = Service::query()->create(['name' => 'Servicio B']);

        $rrhh = User::factory()->create([
            'role' => UserRole::RRHH,
        ]);

        $jefeA = User::factory()->create([
            'role' => UserRole::JEFE_SERVICIO,
            'service_id' => $serviceA->id,
        ]);

        $jefeB = User::factory()->create([
            'role' => UserRole::JEFE_SERVICIO,
            'service_id' => $serviceB->id,
        ]);

        $requestA = Request::query()->create([
            'service_id' => $serviceA->id,
            'created_by' => $jefeA->id,
            'status' => RequestStatus::BORRADOR,
        ]);

        $requestB = Request::query()->create([
            'service_id' => $serviceB->id,
            'created_by' => $jefeB->id,
            'status' => RequestStatus::OBSERVADA,
        ]);

        $response = $this->actingAs($rrhh)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('#'.$requestA->id, false);
        $response->assertSee('#'.$requestB->id, false);
        $response->assertSee('Rol: RRHH');
    }
}
