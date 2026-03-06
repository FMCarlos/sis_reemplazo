<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\AbsenceType;
use App\Models\Employee;
use App\Models\Request as WorkflowRequest;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_jefe_servicio_can_create_a_draft_request_with_internal_replacement_via_json(): void
    {
        $service = Service::query()->create(['name' => 'Cirugía']);
        $absenceType = AbsenceType::query()->create(['name' => 'Licencia médica', 'is_active' => true]);
        $subjectEmployee = $this->createEmployee('Funcionario A');
        $replacementEmployee = $this->createEmployee('Funcionario B');

        $jefe = User::factory()->create([
            'role' => UserRole::JEFE_SERVICIO,
            'service_id' => $service->id,
        ]);

        $response = $this->actingAs($jefe)->postJson(route('requests.store'), [
            'motivo' => 'Licencia médica',
            'subject_employee_id' => $subjectEmployee->id,
            'replacement_is_external' => false,
            'replacement_employee_id' => $replacementEmployee->id,
            'absence_type_id' => $absenceType->id,
            'absence_detail' => 'Reposo postoperatorio',
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-15',
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
            'nombre_reemplazo' => 'Funcionario B',
        ]);

        $createdRequest = WorkflowRequest::query()->where('created_by', $jefe->id)->firstOrFail();

        $this->assertDatabaseHas('request_staffing', [
            'request_id' => $createdRequest->id,
            'subject_employee_id' => $subjectEmployee->id,
            'replacement_is_external' => false,
            'replacement_employee_id' => $replacementEmployee->id,
            'replacement_full_name' => 'Funcionario B',
            'absence_type_id' => $absenceType->id,
            'absence_detail' => 'Reposo postoperatorio',
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-15',
        ]);

        $this->assertDatabaseHas('request_actions', [
            'request_id' => $createdRequest->id,
            'user_id' => $jefe->id,
            'action' => 'create_draft',
            'from_status' => RequestStatus::BORRADOR->value,
            'to_status' => RequestStatus::BORRADOR->value,
            'comment' => null,
        ]);
    }

    public function test_jefe_servicio_can_create_a_draft_request_with_external_replacement_via_json(): void
    {
        $service = Service::query()->create(['name' => 'Cirugía']);
        $subjectEmployee = $this->createEmployee('Funcionario A');

        $jefe = User::factory()->create([
            'role' => UserRole::JEFE_SERVICIO,
            'service_id' => $service->id,
        ]);

        $response = $this->actingAs($jefe)->postJson(route('requests.store'), [
            'motivo' => 'Permiso administrativo',
            'subject_employee_id' => $subjectEmployee->id,
            'replacement_is_external' => true,
            'replacement_full_name' => 'Médico Externo',
            'replacement_rut' => '12345678',
            'replacement_dv' => '9',
            'replacement_profession' => 'Médico',
            'replacement_specialty' => 'Cirugía',
            'replacement_notes' => 'Ingreso por contingencia',
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-10',
        ]);

        $response->assertCreated();

        $createdRequest = WorkflowRequest::query()->where('created_by', $jefe->id)->firstOrFail();

        $this->assertDatabaseHas('requests', [
            'id' => $createdRequest->id,
            'motivo' => 'Permiso administrativo',
            'fecha_inicio' => '2026-04-01',
            'fecha_fin' => '2026-04-10',
            'nombre_reemplazo' => 'Médico Externo',
        ]);

        $this->assertDatabaseHas('request_staffing', [
            'request_id' => $createdRequest->id,
            'subject_employee_id' => $subjectEmployee->id,
            'replacement_is_external' => true,
            'replacement_employee_id' => null,
            'replacement_full_name' => 'Médico Externo',
            'replacement_rut' => '12345678',
            'replacement_dv' => '9',
            'replacement_profession' => 'Médico',
            'replacement_specialty' => 'Cirugía',
            'replacement_notes' => 'Ingreso por contingencia',
        ]);
    }

    public function test_store_fails_if_internal_replacement_is_missing_replacement_employee_id(): void
    {
        $service = Service::query()->create(['name' => 'Cirugía']);
        $subjectEmployee = $this->createEmployee('Funcionario A');

        $jefe = User::factory()->create([
            'role' => UserRole::JEFE_SERVICIO,
            'service_id' => $service->id,
        ]);

        $response = $this->actingAs($jefe)->postJson(route('requests.store'), [
            'motivo' => 'Licencia médica',
            'subject_employee_id' => $subjectEmployee->id,
            'replacement_is_external' => false,
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-05',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['replacement_employee_id']);
    }

    public function test_store_fails_if_internal_replacement_is_same_as_subject_employee(): void
    {
        $service = Service::query()->create(['name' => 'Cirugía']);
        $subjectEmployee = $this->createEmployee('Funcionario A');

        $jefe = User::factory()->create([
            'role' => UserRole::JEFE_SERVICIO,
            'service_id' => $service->id,
        ]);

        $response = $this->actingAs($jefe)->postJson(route('requests.store'), [
            'motivo' => 'Licencia médica',
            'subject_employee_id' => $subjectEmployee->id,
            'replacement_is_external' => false,
            'replacement_employee_id' => $subjectEmployee->id,
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-05',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['replacement_employee_id']);
    }

    public function test_store_fails_if_end_date_is_before_start_date(): void
    {
        $service = Service::query()->create(['name' => 'Cirugía']);
        $subjectEmployee = $this->createEmployee('Funcionario A');
        $replacementEmployee = $this->createEmployee('Funcionario B');

        $jefe = User::factory()->create([
            'role' => UserRole::JEFE_SERVICIO,
            'service_id' => $service->id,
        ]);

        $response = $this->actingAs($jefe)->postJson(route('requests.store'), [
            'motivo' => 'Licencia médica',
            'subject_employee_id' => $subjectEmployee->id,
            'replacement_is_external' => false,
            'replacement_employee_id' => $replacementEmployee->id,
            'start_date' => '2026-03-10',
            'end_date' => '2026-03-01',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
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
            ])
            ->assertForbidden();
    }

    private function createEmployee(string $fullName): Employee
    {
        return Employee::query()->create([
            'rut' => fake()->unique()->numerify('########'),
            'dv' => 'K',
            'nombres' => $fullName,
            'apellido_paterno' => 'Paterno',
            'apellido_materno' => 'Materno',
            'full_name' => $fullName,
            'calidad_juridica' => 'Contrata',
            'ley' => '18834',
            'estamento' => 'Profesional',
            'profesion' => 'Médico',
            'titulo_homologacion' => null,
            'especialidad' => 'Cirugía',
            'unidad' => 'Urgencia',
            'sub_unidad' => null,
            'horas_semanales' => 44,
            'cargo_jornada_turno' => null,
            'nombre_jefatura' => null,
            'is_active' => true,
        ]);
    }
}
