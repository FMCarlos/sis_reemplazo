<?php

namespace App\Policies;

use App\Enums\RequestStatus;
use App\Models\Request;
use App\Models\User;

class RequestPolicy
{
    public function send(User $user, Request $request): bool
    {
        if ($request->created_by !== $user->id) {
            return false;
        }

        return in_array($request->status, [RequestStatus::BORRADOR, RequestStatus::OBSERVADA], true);
    }
}

