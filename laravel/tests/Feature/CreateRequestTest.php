<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\Request as WorkflowRequest;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_jefe_servicio_can_create_a_draft_request_via_json(): void
    {
        $service = Service::query()->create(['name' => 'Cirugía']);

        $jefe = User::factory()->create([
            'role' => UserRole::JEFE_SERVICIO,
            'service_id' => $service->id,
        ]);

        $response = $this->actingAs($jefe)->postJson(route('requests.store'), [
            'motivo' => 'Licencia médica',
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-03-15',
            'nombre_reemplazo' => 'Candidato Demo',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('message', 'Solicitud creada en borrador.')
            ->assertJsonPath('data.status', RequestStatus::BORRADOR->value)
            ->assertJsonStructure([
                'ok',
                'message',
                'data' => ['id', 'status'],
            ]);

        $this->assertDatabaseHas('requests', [
            'service_id' => $service->id,
            'created_by' => $jefe->id,
            'status' => RequestStatus::BORRADOR->value,
            'motivo' => 'Licencia médica',
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-03-15',
            'nombre_reemplazo' => 'Candidato Demo',
        ]);

        $createdRequest = WorkflowRequest::query()->where('created_by', $jefe->id)->firstOrFail();

        $this->assertDatabaseHas('request_actions', [
            'request_id' => $createdRequest->id,
            'user_id' => $jefe->id,
            'action' => 'create',
            'from_status' => RequestStatus::BORRADOR->value,
            'to_status' => RequestStatus::BORRADOR->value,
            'comment' => null,
        ]);
    }

    public function test_store_returns_validation_errors_in_json_with_422_status(): void
    {
        $service = Service::query()->create(['name' => 'Cirugía']);

        $jefe = User::factory()->create([
            'role' => UserRole::JEFE_SERVICIO,
            'service_id' => $service->id,
        ]);

        $response = $this->actingAs($jefe)->postJson(route('requests.store'), [
            'motivo' => '',
            'fecha_inicio' => '2026-03-10',
            'fecha_fin' => '2026-03-01',
            'nombre_reemplazo' => '',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['motivo', 'fecha_fin', 'nombre_reemplazo']);
    }

    public function test_rrhh_cannot_access_create_form_or_store(): void
    {
        $rrhh = User::factory()->create([
            'role' => UserRole::RRHH,
        ]);

        $this->actingAs($rrhh)
            ->get(route('requests.create'))
            ->assertForbidden();

        $this->actingAs($rrhh)
            ->postJson(route('requests.store'), [
                'motivo' => 'X',
                'fecha_inicio' => '2026-03-01',
                'fecha_fin' => '2026-03-02',
                'nombre_reemplazo' => 'Y',
            ])
            ->assertForbidden();
    }

    public function test_jefe_servicio_without_service_cannot_access_create_form_or_store(): void
    {
        $jefe = User::factory()->create([
            'role' => UserRole::JEFE_SERVICIO,
            'service_id' => null,
        ]);

        $this->actingAs($jefe)
            ->get(route('requests.create'))
            ->assertForbidden();

        $this->actingAs($jefe)
            ->postJson(route('requests.store'), [
                'motivo' => 'X',
                'fecha_inicio' => '2026-03-01',
                'fecha_fin' => '2026-03-02',
                'nombre_reemplazo' => 'Y',
            ])
            ->assertForbidden();
    }
}
