<?php

namespace App\Services;

use App\Enums\RequestStatus;
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
        if ($action !== 'send') {
            throw new InvalidArgumentException('Acción no soportada.');
        }

        $fromStatus = $request->status;

        if (! in_array($fromStatus, [RequestStatus::BORRADOR, RequestStatus::OBSERVADA], true)) {
            throw new RuntimeException('La solicitud no puede ser enviada desde su estado actual.');
        }

        return DB::transaction(function () use ($request, $user, $comment, $fromStatus) {
            $request->status = RequestStatus::ENVIADA;
            $request->save();

            $this->logAction(
                $request,
                $user,
                'send',
                $fromStatus,
                RequestStatus::ENVIADA,
                $comment
            );

            return $request->refresh();
        });
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
