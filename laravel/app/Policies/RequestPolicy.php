<?php

namespace App\Policies;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\Request;
use App\Models\User;

class RequestPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, UserRole::cases(), true);
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::JEFE_SERVICIO
            && $user->service_id !== null;
    }

    public function view(User $user, Request $request): bool
    {
        if (in_array($user->role, [UserRole::GESTION_PERSONAS, UserRole::RRHH], true)) {
            return true;
        }

        return $request->service_id === $user->service_id
            && $request->created_by === $user->id;
    }

    public function update(User $user, Request $request): bool
    {
        if (! $this->view($user, $request)) {
            return false;
        }

        return $user->role === UserRole::JEFE_SERVICIO
            && in_array($request->status, [RequestStatus::BORRADOR, RequestStatus::OBSERVADA], true);
    }

    public function actions(User $user, Request $request): bool
    {
        return $this->view($user, $request);
    }

    public function send(User $user, Request $request): bool
    {
        return $this->canPerform($user, $request, UserRole::JEFE_SERVICIO, [RequestStatus::BORRADOR, RequestStatus::OBSERVADA])
            && $request->created_by === $user->id;
    }

    public function take(User $user, Request $request): bool
    {
        return $this->canPerform($user, $request, UserRole::GESTION_PERSONAS, [RequestStatus::ENVIADA]);
    }

    public function sendToRrhh(User $user, Request $request): bool
    {
        return $this->canPerform($user, $request, UserRole::GESTION_PERSONAS, [RequestStatus::EN_GESTION_PERSONAS]);
    }

    public function observe(User $user, Request $request): bool
    {
        if ($this->canPerform($user, $request, UserRole::GESTION_PERSONAS, [RequestStatus::EN_GESTION_PERSONAS])) {
            return true;
        }

        return $this->canPerform($user, $request, UserRole::RRHH, [RequestStatus::EN_RRHH]);
    }

    public function reject(User $user, Request $request): bool
    {
        return $this->canPerform($user, $request, UserRole::RRHH, [RequestStatus::EN_RRHH]);
    }

    public function approveRrhh(User $user, Request $request): bool
    {
        return $this->canPerform($user, $request, UserRole::RRHH, [RequestStatus::EN_RRHH]);
    }

    public function markContractDone(User $user, Request $request): bool
    {
        return $this->canPerform($user, $request, UserRole::GESTION_PERSONAS, [RequestStatus::EN_TRAMITACION_CONTRATO]);
    }

    private function canPerform(User $user, Request $request, UserRole $role, array $statuses): bool
    {
        return $this->view($user, $request)
            && $user->role === $role
            && in_array($request->status, $statuses, true);
    }
}
