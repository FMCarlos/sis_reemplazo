<?php

namespace App\Services;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\Request as WorkflowRequest;
use App\Models\RequestAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class RequestWorkflowService
{
    public function createDraft(User $user, array $attributes): WorkflowRequest
    {
        return DB::transaction(function () use ($user, $attributes) {
            $request = WorkflowRequest::query()->create([
                'service_id' => $user->service_id,
                'created_by' => $user->id,
                'status' => RequestStatus::BORRADOR,
                'motivo' => $attributes['motivo'],
                'fecha_inicio' => $attributes['fecha_inicio'],
                'fecha_fin' => $attributes['fecha_fin'],
                'nombre_reemplazo' => $attributes['nombre_reemplazo'],
            ]);

            $this->logAction(
                $request,
                $user,
                'create',
                RequestStatus::BORRADOR,
                RequestStatus::BORRADOR
            );

            return $request;
        });
    }

    public function apply(WorkflowRequest $request, User $user, string $action, ?string $comment = null): WorkflowRequest
    {
        $normalizedAction = str_replace('-', '_', $action);

        $transitions = $this->transitions();

        if (! array_key_exists($normalizedAction, $transitions)) {
            throw new InvalidArgumentException('Acción no soportada.');
        }

        $fromStatus = $request->status;
        $transition = collect($transitions[$normalizedAction])
            ->first(fn (array $rule) => $user->role === $rule['role'] && in_array($fromStatus, $rule['from'], true));

        if ($transition === null) {
            throw new RuntimeException('No tienes permisos o la solicitud no está en un estado válido para esta acción.');
        }

        if (($transition['owner_only'] ?? false) && $request->created_by !== $user->id) {
            throw new RuntimeException('Solo la jefatura creadora puede ejecutar esta acción.');
        }

        if (($transition['comment_required'] ?? false) && blank($comment)) {
            throw new RuntimeException('Debe ingresar un comentario para esta acción.');
        }

        return DB::transaction(function () use ($request, $user, $comment, $fromStatus, $transition, $normalizedAction) {
            $request->status = $transition['to'];
            $request->save();

            $this->logAction(
                $request,
                $user,
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
