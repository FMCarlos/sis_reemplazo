<?php

namespace App\Modules\Replacement;

use App\Enums\FormSubmissionStatus;
use App\Models\AbsenceType;
use App\Models\Employee;
use App\Models\FormSubmission;
use App\Models\FormType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReplacementFormHandler
{
    public function __construct(
        private readonly ReplacementPayloadValidator $validator,
        private readonly ReplacementPdfGenerator $pdfGenerator,
    ) {}

    public function createDraft(User $actor, array $payload): FormSubmission
    {
        $attributes = $this->validator->validate($payload);
        $formType = $this->resolveFormType();

        return DB::transaction(function () use ($actor, $attributes, $formType) {
            $submission = FormSubmission::query()->create([
                'form_type_id' => $formType->id,
                'submitted_by' => $actor->id,
                'status' => FormSubmissionStatus::DRAFT,
                'payload_json' => $this->buildPayload($actor, $attributes),
            ]);

            $this->logAction($submission, $actor, 'draft_created', [
                'message' => 'Formulario de reemplazo guardado en borrador.',
                'status' => FormSubmissionStatus::DRAFT->value,
            ]);

            return $submission->refresh(['formType', 'submitter', 'actions']);
        });
    }

    public function updateDraft(FormSubmission $submission, User $actor, array $payload): FormSubmission
    {
        if ($submission->status !== FormSubmissionStatus::DRAFT) {
            throw new RuntimeException('Solo los borradores pueden editarse.');
        }

        $attributes = $this->validator->validate($payload);

        return DB::transaction(function () use ($submission, $actor, $attributes) {
            $submission->forceFill([
                'payload_json' => $this->buildPayload($actor, $attributes),
            ])->save();

            $this->logAction($submission, $actor, 'draft_updated', [
                'message' => 'Borrador actualizado por la jefatura solicitante.',
                'status' => FormSubmissionStatus::DRAFT->value,
            ]);

            return $submission->refresh(['formType', 'submitter', 'actions']);
        });
    }

    public function submit(FormSubmission $submission, User $actor): FormSubmission
    {
        if ($submission->status !== FormSubmissionStatus::DRAFT) {
            throw new RuntimeException('Solo los borradores pueden enviarse a RRHH.');
        }

        return DB::transaction(function () use ($submission, $actor) {
            $submission->forceFill([
                'status' => FormSubmissionStatus::SUBMITTED,
                'submitted_at' => now(),
            ])->save();

            $this->logAction($submission, $actor, 'submitted', [
                'from_status' => FormSubmissionStatus::DRAFT->value,
                'to_status' => FormSubmissionStatus::SUBMITTED->value,
                'message' => 'Solicitud enviada por la jefatura para revisión de RRHH.',
            ]);

            return $submission->refresh(['formType', 'submitter', 'actions']);
        });
    }

    public function approve(FormSubmission $submission, User $actor, ?string $comment = null): FormSubmission
    {
        if ($submission->status !== FormSubmissionStatus::SUBMITTED) {
            throw new RuntimeException('Solo las solicitudes enviadas pueden aprobarse.');
        }

        return DB::transaction(function () use ($submission, $actor, $comment) {
            $pdfPath = $this->pdfGenerator->generate($submission);

            $submission->forceFill([
                'status' => FormSubmissionStatus::APPROVED,
                'pdf_path' => $pdfPath,
            ])->save();

            $this->logAction($submission, $actor, 'approved', [
                'from_status' => FormSubmissionStatus::SUBMITTED->value,
                'to_status' => FormSubmissionStatus::APPROVED->value,
                'comment' => $comment,
                'message' => 'Solicitud aprobada por RRHH.',
            ]);

            $this->logAction($submission, $actor, 'pdf_generated', [
                'pdf_path' => $pdfPath,
                'generated_on_status' => FormSubmissionStatus::APPROVED->value,
            ]);

            return $submission->refresh(['formType', 'submitter', 'actions']);
        });
    }

    public function reject(FormSubmission $submission, User $actor, ?string $comment = null): FormSubmission
    {
        if ($submission->status !== FormSubmissionStatus::SUBMITTED) {
            throw new RuntimeException('Solo las solicitudes enviadas pueden rechazarse.');
        }

        return DB::transaction(function () use ($submission, $actor, $comment) {
            $submission->forceFill([
                'status' => FormSubmissionStatus::REJECTED,
            ])->save();

            $this->logAction($submission, $actor, 'rejected', [
                'from_status' => FormSubmissionStatus::SUBMITTED->value,
                'to_status' => FormSubmissionStatus::REJECTED->value,
                'comment' => $comment,
                'message' => 'Solicitud rechazada por RRHH.',
            ]);

            return $submission->refresh(['formType', 'submitter', 'actions']);
        });
    }

    private function resolveFormType(): FormType
    {
        $formType = FormType::query()->where('code', 'replacement_request')->where('active', true)->first();

        if (! $formType) {
            throw new RuntimeException('El tipo de formulario de reemplazo no está disponible.');
        }

        return $formType;
    }

    private function logAction(FormSubmission $submission, User $actor, string $action, array $payload = []): void
    {
        $submission->actions()->create([
            'user_id' => $actor->id,
            'action' => $action,
            'payload_json' => $payload,
        ]);
    }

    private function buildPayload(User $actor, array $attributes): array
    {
        $subjectEmployee = Employee::query()->findOrFail($attributes['subject_employee_id']);
        $replacementEmployee = null;

        if (! $attributes['replacement_is_external']) {
            $replacementEmployee = Employee::query()->findOrFail($attributes['replacement_employee_id']);
        }

        $absenceType = ! empty($attributes['absence_type_id'])
            ? AbsenceType::query()->find($attributes['absence_type_id'])
            : null;

        return [
            'form_code' => 'replacement_request',
            'motivo' => $attributes['motivo'],
            'service' => [
                'id' => $actor->service_id,
                'name' => $actor->service?->name,
            ],
            'subject_employee' => $this->mapEmployee($subjectEmployee),
            'replacement' => [
                'is_external' => (bool) $attributes['replacement_is_external'],
                'employee_id' => $replacementEmployee?->id,
                'full_name' => $replacementEmployee?->full_name ?? ($attributes['replacement_full_name'] ?? null),
                'rut' => $replacementEmployee?->rut ?? ($attributes['replacement_rut'] ?? null),
                'dv' => $replacementEmployee?->dv ?? ($attributes['replacement_dv'] ?? null),
                'profession' => $replacementEmployee?->profesion ?? ($attributes['replacement_profession'] ?? null),
                'specialty' => $replacementEmployee?->especialidad ?? ($attributes['replacement_specialty'] ?? null),
                'notes' => $attributes['replacement_notes'] ?? null,
                'employee_snapshot' => $replacementEmployee ? $this->mapEmployee($replacementEmployee) : null,
            ],
            'absence' => [
                'type_id' => $absenceType?->id,
                'type_name' => $absenceType?->name,
                'detail' => $attributes['absence_detail'] ?? null,
            ],
            'period' => [
                'start_date' => $attributes['start_date'],
                'end_date' => $attributes['end_date'],
            ],
        ];
    }

    private function mapEmployee(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'full_name' => $employee->full_name,
            'rut' => $employee->rut,
            'dv' => $employee->dv,
            'unidad' => $employee->unidad,
            'profesion' => $employee->profesion,
            'especialidad' => $employee->especialidad,
        ];
    }
}
