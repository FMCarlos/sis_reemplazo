<?php

namespace App\Policies;

use App\Enums\FormSubmissionStatus;
use App\Enums\UserRole;
use App\Models\FormSubmission;
use App\Models\User;

class FormSubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FormSubmission $submission): bool
    {
        if ($user->role === UserRole::ADMIN) {
            return true;
        }

        if (in_array($user->role, [UserRole::RRHH, UserRole::GESTION_PERSONAS], true)) {
            return true;
        }

        return $user->role === UserRole::JEFE_SERVICIO
            && $submission->submitted_by === $user->id;
    }

    public function update(User $user, FormSubmission $submission): bool
    {
        return $user->role === UserRole::JEFE_SERVICIO
            && $submission->submitted_by === $user->id
            && $submission->status === FormSubmissionStatus::DRAFT;
    }

    public function submit(User $user, FormSubmission $submission): bool
    {
        return $this->update($user, $submission);
    }

    public function approve(User $user, FormSubmission $submission): bool
    {
        return $user->role === UserRole::RRHH
            && $submission->status === FormSubmissionStatus::SUBMITTED;
    }

    public function reject(User $user, FormSubmission $submission): bool
    {
        return $user->role === UserRole::RRHH
            && $submission->status === FormSubmissionStatus::SUBMITTED;
    }

    public function downloadPdf(User $user, FormSubmission $submission): bool
    {
        return $this->view($user, $submission)
            && filled($submission->pdf_path)
            && $submission->status === FormSubmissionStatus::APPROVED;
    }
}
