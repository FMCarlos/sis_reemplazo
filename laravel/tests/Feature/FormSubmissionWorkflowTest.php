<?php

namespace Tests\Feature;

use App\Enums\FormSubmissionStatus;
use App\Enums\UserRole;
use App\Models\Employee;
use App\Models\FormSubmission;
use App\Models\FormType;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FormSubmissionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_jefe_servicio_can_submit_a_draft_to_rrhh(): void
    {
        Storage::fake('local');

        [$jefe, $submission] = $this->createDraftSubmission();

        $response = $this->actingAs($jefe)
            ->postJson(route('forms.submissions.submit', $submission));

        $response
            ->assertOk()
            ->assertJsonPath('data.status', FormSubmissionStatus::SUBMITTED->value);

        $submission->refresh();

        $this->assertSame(FormSubmissionStatus::SUBMITTED, $submission->status);
        $this->assertNotNull($submission->submitted_at);
        $this->assertNull($submission->pdf_path);

        $this->assertDatabaseHas('form_submission_actions', [
            'form_submission_id' => $submission->id,
            'action' => 'submitted',
            'user_id' => $jefe->id,
        ]);
    }

    public function test_rrhh_can_approve_submitted_submission_and_generate_pdf(): void
    {
        Storage::fake('local');

        [, $submission] = $this->createDraftSubmission();
        $submission->update([
            'status' => FormSubmissionStatus::SUBMITTED,
            'submitted_at' => now(),
        ]);

        $rrhh = User::factory()->create(['role' => UserRole::RRHH]);

        $response = $this->actingAs($rrhh)
            ->postJson(route('forms.submissions.approve', $submission), [
                'comment' => 'Cumple con antecedentes.',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.status', FormSubmissionStatus::APPROVED->value);

        $submission->refresh();

        $this->assertSame(FormSubmissionStatus::APPROVED, $submission->status);
        $this->assertNotNull($submission->pdf_path);
        Storage::disk('local')->assertExists($submission->pdf_path);

        $this->assertDatabaseHas('form_submission_actions', [
            'form_submission_id' => $submission->id,
            'action' => 'approved',
            'user_id' => $rrhh->id,
        ]);

        $this->assertDatabaseHas('form_submission_actions', [
            'form_submission_id' => $submission->id,
            'action' => 'pdf_generated',
            'user_id' => $rrhh->id,
        ]);
    }

    public function test_rrhh_can_reject_submitted_submission_without_generating_pdf(): void
    {
        Storage::fake('local');

        [, $submission] = $this->createDraftSubmission();
        $submission->update([
            'status' => FormSubmissionStatus::SUBMITTED,
            'submitted_at' => now(),
        ]);

        $rrhh = User::factory()->create(['role' => UserRole::RRHH]);

        $response = $this->actingAs($rrhh)
            ->postJson(route('forms.submissions.reject', $submission), [
                'comment' => 'Antecedentes incompletos.',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.status', FormSubmissionStatus::REJECTED->value);

        $submission->refresh();

        $this->assertSame(FormSubmissionStatus::REJECTED, $submission->status);
        $this->assertNull($submission->pdf_path);

        $this->assertDatabaseHas('form_submission_actions', [
            'form_submission_id' => $submission->id,
            'action' => 'rejected',
            'user_id' => $rrhh->id,
        ]);
    }


    public function test_gestion_personas_cannot_apply_rrhh_actions_to_modular_replacement_submission(): void
    {
        Storage::fake('local');

        [, $submission] = $this->createDraftSubmission();
        $submission->update([
            'status' => FormSubmissionStatus::SUBMITTED,
            'submitted_at' => now(),
        ]);

        $gestion = User::factory()->create(['role' => UserRole::GESTION_PERSONAS]);

        $this->actingAs($gestion)
            ->postJson(route('forms.submissions.approve', $submission), [
                'comment' => 'No corresponde.',
            ])
            ->assertForbidden();

        $this->actingAs($gestion)
            ->postJson(route('forms.submissions.reject', $submission), [
                'comment' => 'No corresponde.',
            ])
            ->assertForbidden();
    }

    public function test_rrhh_sees_approve_and_reject_actions_in_detail_for_submitted_submission(): void
    {
        [, $submission] = $this->createDraftSubmission();
        $submission->update([
            'status' => FormSubmissionStatus::SUBMITTED,
            'submitted_at' => now(),
        ]);

        $rrhh = User::factory()->create(['role' => UserRole::RRHH]);

        $this->actingAs($rrhh)
            ->get(route('forms.submissions.show', $submission))
            ->assertOk()
            ->assertSee('Aprobar RRHH')
            ->assertSee('Rechazar RRHH')
            ->assertDontSee('Observar');
    }

    public function test_rrhh_sees_approve_and_reject_actions_in_index_for_submitted_submission(): void
    {
        [, $submission] = $this->createDraftSubmission();
        $submission->update([
            'status' => FormSubmissionStatus::SUBMITTED,
            'submitted_at' => now(),
        ]);

        $rrhh = User::factory()->create(['role' => UserRole::RRHH]);

        $this->actingAs($rrhh)
            ->get(route('forms.submissions.index'))
            ->assertOk()
            ->assertSee('Aprobar RRHH')
            ->assertSee('Rechazar RRHH');
    }

    public function test_rrhh_can_approve_from_index_using_standard_post_flow(): void
    {
        Storage::fake('local');

        [, $submission] = $this->createDraftSubmission();
        $submission->update([
            'status' => FormSubmissionStatus::SUBMITTED,
            'submitted_at' => now(),
        ]);

        $rrhh = User::factory()->create(['role' => UserRole::RRHH]);

        $this->actingAs($rrhh)
            ->from(route('forms.submissions.index'))
            ->post(route('forms.submissions.approve', $submission))
            ->assertRedirect(route('forms.submissions.show', $submission));

        $submission->refresh();

        $this->assertSame(FormSubmissionStatus::APPROVED, $submission->status);
    }

    private function createDraftSubmission(): array
    {
        $service = Service::query()->create(['name' => 'Cirugía']);
        $formType = FormType::query()->create([
            'code' => 'replacement_request',
            'name' => 'Solicitud de reemplazo',
            'description' => 'Formulario',
            'active' => true,
        ]);

        $subjectEmployee = Employee::query()->create([
            'full_name' => 'Funcionario Titular',
            'rut' => '11111111',
            'dv' => '1',
            'is_active' => true,
        ]);

        $replacementEmployee = Employee::query()->create([
            'full_name' => 'Funcionario Reemplazo',
            'rut' => '22222222',
            'dv' => '2',
            'is_active' => true,
        ]);

        $jefe = User::factory()->create([
            'role' => UserRole::JEFE_SERVICIO,
            'service_id' => $service->id,
        ]);

        $submission = FormSubmission::query()->create([
            'form_type_id' => $formType->id,
            'submitted_by' => $jefe->id,
            'status' => FormSubmissionStatus::DRAFT,
            'payload_json' => [
                'form_code' => 'replacement_request',
                'motivo' => 'Licencia médica',
                'service' => ['id' => $service->id, 'name' => $service->name],
                'subject_employee' => ['id' => $subjectEmployee->id, 'full_name' => $subjectEmployee->full_name, 'rut' => $subjectEmployee->rut, 'dv' => $subjectEmployee->dv],
                'replacement' => ['is_external' => false, 'employee_id' => $replacementEmployee->id, 'full_name' => $replacementEmployee->full_name],
                'absence' => ['type_name' => 'Licencia médica'],
                'period' => ['start_date' => '2026-03-01', 'end_date' => '2026-03-15'],
            ],
        ]);

        return [$jefe, $submission];
    }
}
