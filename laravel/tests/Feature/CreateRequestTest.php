<?php

namespace Tests\Feature;

use App\Enums\FormSubmissionStatus;
use App\Enums\UserRole;
use App\Models\AbsenceType;
use App\Models\Employee;
use App\Models\FormSubmission;
use App\Models\FormType;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_jefe_servicio_can_create_a_replacement_form_submission_with_internal_replacement_via_json(): void
    {
        Storage::fake('local');

        $service = Service::query()->create(['name' => 'Cirugía']);
        $formType = $this->createReplacementFormType();
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
            ->assertJsonPath('message', 'Formulario de reemplazo enviado y PDF generado correctamente.')
            ->assertJsonPath('data.status', FormSubmissionStatus::SUBMITTED->value)
            ->assertJsonStructure([
                'ok',
                'message',
                'data' => ['id', 'status', 'pdf_path', 'show_url'],
            ]);

        $submission = FormSubmission::query()->firstOrFail();

        $this->assertDatabaseHas('form_submissions', [
            'id' => $submission->id,
            'form_type_id' => $formType->id,
            'submitted_by' => $jefe->id,
            'status' => FormSubmissionStatus::SUBMITTED->value,
        ]);

        $this->assertSame('Licencia médica', data_get($submission->payload_json, 'motivo'));
        $this->assertSame('Funcionario A', data_get($submission->payload_json, 'subject_employee.full_name'));
        $this->assertSame('Funcionario B', data_get($submission->payload_json, 'replacement.full_name'));
        $this->assertSame('Licencia médica', data_get($submission->payload_json, 'absence.type_name'));
        $this->assertSame('2026-03-01', data_get($submission->payload_json, 'period.start_date'));
        $this->assertSame('2026-03-15', data_get($submission->payload_json, 'period.end_date'));
        $this->assertNotNull($submission->pdf_path);
        Storage::disk('local')->assertExists($submission->pdf_path);

        $this->assertDatabaseHas('form_submission_actions', [
            'form_submission_id' => $submission->id,
            'user_id' => $jefe->id,
            'action' => 'created',
        ]);

        $this->assertDatabaseHas('form_submission_actions', [
            'form_submission_id' => $submission->id,
            'user_id' => $jefe->id,
            'action' => 'pdf_generated',
        ]);
    }

    public function test_jefe_servicio_can_create_a_replacement_form_submission_with_external_replacement_via_json(): void
    {
        Storage::fake('local');

        $service = Service::query()->create(['name' => 'Cirugía']);
        $this->createReplacementFormType();
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

        $submission = FormSubmission::query()->firstOrFail();

        $this->assertSame('Permiso administrativo', data_get($submission->payload_json, 'motivo'));
        $this->assertSame('Médico Externo', data_get($submission->payload_json, 'replacement.full_name'));
        $this->assertTrue((bool) data_get($submission->payload_json, 'replacement.is_external'));
        $this->assertSame('12345678', data_get($submission->payload_json, 'replacement.rut'));
        $this->assertSame('9', data_get($submission->payload_json, 'replacement.dv'));
        $this->assertSame('Médico', data_get($submission->payload_json, 'replacement.profession'));
        $this->assertSame('Cirugía', data_get($submission->payload_json, 'replacement.specialty'));
        $this->assertSame('Ingreso por contingencia', data_get($submission->payload_json, 'replacement.notes'));
        Storage::disk('local')->assertExists($submission->pdf_path);
    }

    public function test_store_fails_if_internal_replacement_is_missing_replacement_employee_id(): void
    {
        $service = Service::query()->create(['name' => 'Cirugía']);
        $this->createReplacementFormType();
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
        $this->createReplacementFormType();
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
        $this->createReplacementFormType();
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

    public function test_store_fails_if_replacement_form_type_is_not_available(): void
    {
        $service = Service::query()->create(['name' => 'Cirugía']);
        $subjectEmployee = $this->createEmployee('Funcionario A');
        $replacementEmployee = $this->createEmployee('Funcionario B');

        $jefe = User::factory()->create([
            'role' => UserRole::JEFE_SERVICIO,
            'service_id' => $service->id,
        ]);

        $this->actingAs($jefe)
            ->withExceptionHandling()
            ->postJson(route('requests.store'), [
                'motivo' => 'Licencia médica',
                'subject_employee_id' => $subjectEmployee->id,
                'replacement_is_external' => false,
                'replacement_employee_id' => $replacementEmployee->id,
                'start_date' => '2026-03-01',
                'end_date' => '2026-03-05',
            ])
            ->assertStatus(500);
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

    private function createReplacementFormType(): FormType
    {
        return FormType::query()->create([
            'code' => 'replacement_request',
            'name' => 'Solicitud de reemplazo',
            'description' => 'Formulario institucional para solicitudes de reemplazo.',
            'active' => true,
        ]);
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
