<?php

namespace App\Services;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\Employee;
use App\Models\Request as WorkflowRequest;
use App\Models\RequestAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use RuntimeException;

class RequestWorkflowService
{
    public function createDraft(User $actor, array $payload): WorkflowRequest
    {
        $attributes = Validator::make($payload, [
            'motivo' => ['required', 'string', 'max:255'],
            'subject_employee_id' => ['required', 'integer', 'exists:employees,id'],
            'replacement_is_external' => ['required', 'boolean'],
            'replacement_employee_id' => [
                'nullable',
                'integer',
                'exists:employees,id',
                'required_if:replacement_is_external,false',
                'exclude_if:replacement_is_external,true',
                'different:subject_employee_id',
            ],
            'replacement_full_name' => [
                'nullable',
                'string',
                'max:255',
                'required_if:replacement_is_external,true',
                'exclude_unless:replacement_is_external,true',
            ],
            'replacement_rut' => ['nullable', 'string', 'max:20', 'exclude_unless:replacement_is_external,true'],
            'replacement_dv' => ['nullable', 'string', 'size:1', 'exclude_unless:replacement_is_external,true'],
            'replacement_profession' => ['nullable', 'string', 'max:255', 'exclude_unless:replacement_is_external,true'],
            'replacement_specialty' => ['nullable', 'string', 'max:255', 'exclude_unless:replacement_is_external,true'],
            'replacement_notes' => ['nullable', 'string', 'max:2000', 'exclude_unless:replacement_is_external,true'],
            'absence_type_id' => ['nullable', 'integer', 'exists:absence_types,id'],
            'absence_detail' => ['nullable', 'string', 'max:2000'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ])->validate();

        $replacementEmployee = null;
        $replacementName = $attributes['replacement_full_name'] ?? null;

        if (! $attributes['replacement_is_external']) {
            $replacementEmployee = Employee::query()->findOrFail($attributes['replacement_employee_id']);
            $replacementName = $replacementEmployee->full_name;
        }

        return DB::transaction(function () use ($actor, $attributes, $replacementEmployee, $replacementName) {
            $request = WorkflowRequest::query()->create([
                'service_id' => $actor->service_id,
                'created_by' => $actor->id,
                'status' => RequestStatus::BORRADOR,
                'motivo' => $attributes['motivo'],
                'fecha_inicio' => $attributes['start_date'],
                'fecha_fin' => $attributes['end_date'],
                'nombre_reemplazo' => $replacementName,
            ]);

            $request->staffing()->create([
                'subject_employee_id' => $attributes['subject_employee_id'],
                'replacement_employee_id' => $replacementEmployee?->id,
                'replacement_is_external' => $attributes['replacement_is_external'],
                'replacement_rut' => $attributes['replacement_rut'] ?? null,
                'replacement_dv' => $attributes['replacement_dv'] ?? null,
                'replacement_full_name' => $replacementName,
                'replacement_profession' => $attributes['replacement_profession'] ?? null,
                'replacement_specialty' => $attributes['replacement_specialty'] ?? null,
                'replacement_notes' => $attributes['replacement_notes'] ?? null,
                'absence_type_id' => $attributes['absence_type_id'] ?? null,
                'absence_detail' => $attributes['absence_detail'] ?? null,
                'start_date' => $attributes['start_date'],
                'end_date' => $attributes['end_date'],
            ]);

            $this->logAction(
                $request,
                $actor,
                'create_draft',
                RequestStatus::BORRADOR,
                RequestStatus::BORRADOR
            );

            return $request;
        });
    }

    public function send(User $actor, WorkflowRequest $request, array $payload = []): WorkflowRequest
    {
        return $this->apply($actor, $request, 'send', $payload);
    }

    public function take(User $actor, WorkflowRequest $request, array $payload = []): WorkflowRequest
    {
        return $this->apply($actor, $request, 'take', $payload);
    }

    public function send_to_rrhh(User $actor, WorkflowRequest $request, array $payload = []): WorkflowRequest
    {
        return $this->apply($actor, $request, 'send_to_rrhh', $payload);
    }

    public function observe(User $actor, WorkflowRequest $request, array $payload = []): WorkflowRequest
    {
        return $this->apply($actor, $request, 'observe', $payload);
    }

    public function reject(User $actor, WorkflowRequest $request, array $payload = []): WorkflowRequest
    {
        return $this->apply($actor, $request, 'reject', $payload);
    }

    public function approve_rrhh(User $actor, WorkflowRequest $request, array $payload = []): WorkflowRequest
    {
        return $this->apply($actor, $request, 'approve_rrhh', $payload);
    }

    public function mark_contract_done(User $actor, WorkflowRequest $request, array $payload = []): WorkflowRequest
    {
        return $this->apply($actor, $request, 'mark_contract_done', $payload);
    }

    public function sendToRrhh(User $actor, WorkflowRequest $request, array $payload = []): WorkflowRequest
    {
        return $this->send_to_rrhh($actor, $request, $payload);
    }

    public function approveRrhh(User $actor, WorkflowRequest $request, array $payload = []): WorkflowRequest
    {
        return $this->approve_rrhh($actor, $request, $payload);
    }

    public function markContractDone(User $actor, WorkflowRequest $request, array $payload = []): WorkflowRequest
    {
        return $this->mark_contract_done($actor, $request, $payload);
    }

    public function apply(User $actor, WorkflowRequest $request, string $action, array $payload = []): WorkflowRequest
    {
        $normalizedAction = str_replace('-', '_', $action);
        $comment = isset($payload['comment']) ? trim((string) $payload['comment']) : null;

        $transitions = $this->transitions();

        if (! array_key_exists($normalizedAction, $transitions)) {
            throw new InvalidArgumentException('Acción no soportada.');
        }

        $fromStatus = $request->status;
        $transition = collect($transitions[$normalizedAction])
            ->first(fn (array $rule) => $actor->role === $rule['role'] && in_array($fromStatus, $rule['from'], true));

        if ($transition === null) {
            throw new RuntimeException('No tienes permisos o la solicitud no está en un estado válido para esta acción.');
        }

        if (($transition['owner_only'] ?? false) && $request->created_by !== $actor->id) {
            throw new RuntimeException('Solo la jefatura creadora puede ejecutar esta acción.');
        }

        if (($transition['comment_required'] ?? false) && blank($comment)) {
            throw new RuntimeException('Debe ingresar un comentario para esta acción.');
        }

        return DB::transaction(function () use ($request, $actor, $comment, $fromStatus, $transition, $normalizedAction) {
            $request->status = $transition['to'];
            $request->save();

            $this->logAction(
                $request,
                $actor,
                $normalizedAction,
                $fromStatus,
                $transition['to'],
                $comment
            );

            return $request->refresh();
        });
    }

    /**
     * @return array<string, array<int, array{role: UserRole, from: array<int, RequestStatus>, to: RequestStatus, owner_only?: bool, comment_required?: bool}>>
     */
    public function transitions(): array
    {
        return [
            'send' => [[
                'role' => UserRole::JEFE_SERVICIO,
                'from' => [RequestStatus::BORRADOR, RequestStatus::OBSERVADA],
                'to' => RequestStatus::ENVIADA,
                'owner_only' => true,
            ]],
            'take' => [[
                'role' => UserRole::GESTION_PERSONAS,
                'from' => [RequestStatus::ENVIADA],
                'to' => RequestStatus::EN_GESTION_PERSONAS,
            ]],
            'send_to_rrhh' => [[
                'role' => UserRole::GESTION_PERSONAS,
                'from' => [RequestStatus::EN_GESTION_PERSONAS],
                'to' => RequestStatus::EN_RRHH,
            ]],
            'observe' => [
                [
                    'role' => UserRole::GESTION_PERSONAS,
                    'from' => [RequestStatus::EN_GESTION_PERSONAS],
                    'to' => RequestStatus::OBSERVADA,
                    'comment_required' => true,
                ],
                [
                    'role' => UserRole::RRHH,
                    'from' => [RequestStatus::EN_RRHH],
                    'to' => RequestStatus::EN_GESTION_PERSONAS,
                    'comment_required' => true,
                ],
            ],
            'reject' => [[
                'role' => UserRole::RRHH,
                'from' => [RequestStatus::EN_RRHH],
                'to' => RequestStatus::RECHAZADA,
                'comment_required' => true,
            ]],
            'approve_rrhh' => [[
                'role' => UserRole::RRHH,
                'from' => [RequestStatus::EN_RRHH],
                'to' => RequestStatus::EN_TRAMITACION_CONTRATO,
            ]],
            'mark_contract_done' => [[
                'role' => UserRole::GESTION_PERSONAS,
                'from' => [RequestStatus::EN_TRAMITACION_CONTRATO],
                'to' => RequestStatus::FINALIZADA,
            ]],
        ];
    }

    private function logAction(
        WorkflowRequest $request,
        User $user,
        string $action,
        RequestStatus $fromStatus,
        RequestStatus $toStatus,
        ?string $comment = null,
    ): void {
        RequestAction::query()->create([
            'request_id' => $request->id,
            'user_id' => $user->id,
            'action' => $action,
            'from_status' => $fromStatus->value,
            'to_status' => $toStatus->value,
            'comment' => $comment,
        ]);
    }
}
