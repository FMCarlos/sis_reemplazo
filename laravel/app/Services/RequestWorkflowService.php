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

            RequestAction::query()->create([
                'request_id' => $request->id,
                'user_id' => $user->id,
                'action' => 'send',
                'from_status' => $fromStatus->value,
                'to_status' => RequestStatus::ENVIADA->value,
                'comment' => $comment,
            ]);

            return $request->refresh();
        });
    }
}

