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

    public function create(User $actor, array $payload): FormSubmission
    {
        $attributes = $this->validator->validate($payload);
        $formType = FormType::query()->where('code', 'replacement_request')->where('active', true)->first();

        if (! $formType) {
            throw new RuntimeException('El tipo de formulario de reemplazo no está disponible.');
        }

        return DB::transaction(function () use ($actor, $attributes, $formType) {
            $normalizedPayload = $this->buildPayload($actor, $attributes);

            $submission = FormSubmission::query()->create([
                'form_type_id' => $formType->id,
                'submitted_by' => $actor->id,
                'status' => FormSubmissionStatus::SUBMITTED,
                'payload_json' => $normalizedPayload,
                'submitted_at' => now(),
            ]);

            $submission->actions()->create([
                'user_id' => $actor->id,
                'action' => 'created',
                'payload_json' => [
                    'message' => 'Formulario de reemplazo creado en flujo simple.',
                ],
            ]);

            $pdfPath = $this->pdfGenerator->generate($submission);
            $submission->forceFill(['pdf_path' => $pdfPath])->save();

            $submission->actions()->create([
                'user_id' => $actor->id,
                'action' => 'pdf_generated',
                'payload_json' => [
                    'pdf_path' => $pdfPath,
                ],
            ]);

            return $submission->refresh(['formType', 'submitter', 'actions']);
        });
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
