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
        if (! $this->update($user, $request)) {
            return false;
        }

        return $request->created_by === $user->id;
    }
}
