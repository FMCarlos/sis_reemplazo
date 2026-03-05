<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\Request;
use App\Models\RequestAction;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestShowViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_view_request_detail_with_audit_history(): void
    {
        $service = Service::query()->create(['name' => 'Urgencias']);

        $user = User::factory()->create([
            'service_id' => $service->id,
        ]);

        $request = Request::query()->create([
            'service_id' => $service->id,
            'created_by' => $user->id,
            'status' => RequestStatus::ENVIADA,
            'motivo' => 'Licencia médica',
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-03-15',
            'nombre_reemplazo' => 'Ana Soto',
        ]);

        RequestAction::query()->create([
            'request_id' => $request->id,
            'user_id' => $user->id,
            'action' => 'draft',
            'from_status' => RequestStatus::BORRADOR->value,
            'to_status' => RequestStatus::BORRADOR->value,
            'comment' => 'Borrador inicial',
            'created_at' => now()->subHour(),
        ]);

        RequestAction::query()->create([
            'request_id' => $request->id,
            'user_id' => $user->id,
            'action' => 'send',
            'from_status' => RequestStatus::BORRADOR->value,
            'to_status' => RequestStatus::ENVIADA->value,
            'comment' => 'Envío a flujo',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get(route('requests.show', $request));

        $response->assertOk()
            ->assertSeeText('Detalle solicitud')
            ->assertSeeText('Historial / Auditoría')
            ->assertSeeText('Licencia médica')
            ->assertSeeText('send')
            ->assertSeeText('draft');

        $response->assertSeeInOrder([
            'send',
            'draft',
        ]);
    }

    public function test_admin_can_view_request_detail_from_any_service_and_creator(): void
    {
        $serviceA = Service::query()->create(['name' => 'Urgencias']);

        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $creator = User::factory()->create([
            'service_id' => $serviceA->id,
        ]);

        $request = Request::query()->create([
            'service_id' => $serviceA->id,
            'created_by' => $creator->id,
            'status' => RequestStatus::ENVIADA,
            'motivo' => 'Cobertura interservicio',
            'fecha_inicio' => '2026-04-01',
            'fecha_fin' => '2026-04-15',
            'nombre_reemplazo' => 'Sofía Díaz',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('requests.show', $request));

        $response->assertOk()
            ->assertSeeText('Detalle solicitud')
            ->assertSeeText('Cobertura interservicio');
    }

    public function test_non_admin_user_keeps_existing_visibility_restrictions(): void
    {
        $serviceA = Service::query()->create(['name' => 'Urgencias']);
        $serviceB = Service::query()->create(['name' => 'Pediatría']);

        $jefe = User::factory()->create([
            'service_id' => $serviceA->id,
        ]);

        $otherCreator = User::factory()->create([
            'service_id' => $serviceB->id,
        ]);

        $request = Request::query()->create([
            'service_id' => $serviceB->id,
            'created_by' => $otherCreator->id,
            'status' => RequestStatus::ENVIADA,
            'motivo' => 'Solicitud restringida',
            'fecha_inicio' => '2026-05-01',
            'fecha_fin' => '2026-05-10',
            'nombre_reemplazo' => 'Luis Pérez',
        ]);

        $this->actingAs($jefe)
            ->get(route('requests.show', $request))
            ->assertForbidden();
    }
}
