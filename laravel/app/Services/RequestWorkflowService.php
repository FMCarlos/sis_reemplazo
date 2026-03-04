<?php

namespace App\Services;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
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
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'nombre_reemplazo' => ['required', 'string', 'max:255'],
        ])->validate();

        return DB::transaction(function () use ($actor, $attributes) {
            $request = WorkflowRequest::query()->create([
                'service_id' => $actor->service_id,
                'created_by' => $actor->id,
                'status' => RequestStatus::BORRADOR,
                'motivo' => $attributes['motivo'],
                'fecha_inicio' => $attributes['fecha_inicio'],
                'fecha_fin' => $attributes['fecha_fin'],
                'nombre_reemplazo' => $attributes['nombre_reemplazo'],
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
