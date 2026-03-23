<?php

namespace App\Enums;

enum FormSubmissionStatus: string
{
    case DRAFT = 'DRAFT';
    case SUBMITTED = 'SUBMITTED';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Borrador',
            self::SUBMITTED => 'Enviada a RRHH',
            self::APPROVED => 'Aprobada',
            self::REJECTED => 'Rechazada',
            self::CANCELLED => 'Cancelada',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-secondary',
            self::SUBMITTED => 'bg-primary',
            self::APPROVED => 'bg-success',
            self::REJECTED, self::CANCELLED => 'bg-danger',
        };
    }
}
