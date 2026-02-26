<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_jefe_servicio_can_create_a_draft_request_from_form(): void
    {
        $service = Service::query()->create(['name' => 'Cirugía']);

        $jefe = User::factory()->create([
            'role' => UserRole::JEFE_SERVICIO,
            'service_id' => $service->id,
        ]);

        $response = $this->actingAs($jefe)->post(route('requests.store'), [
            'motivo' => 'Licencia médica',
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-03-15',
            'nombre_reemplazo' => 'Candidato Demo',
        ]);

        $response->assertRedirect(route('requests.index'));

        $this->assertDatabaseHas('requests', [
            'service_id' => $service->id,
            'created_by' => $jefe->id,
            'status' => RequestStatus::BORRADOR->value,
            'motivo' => 'Licencia médica',
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-03-15',
            'nombre_reemplazo' => 'Candidato Demo',
        ]);
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
            ->post(route('requests.store'), [
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
            ->post(route('requests.store'), [
                'motivo' => 'X',
                'fecha_inicio' => '2026-03-01',
                'fecha_fin' => '2026-03-02',
                'nombre_reemplazo' => 'Y',
            ])
            ->assertForbidden();
    }
}
