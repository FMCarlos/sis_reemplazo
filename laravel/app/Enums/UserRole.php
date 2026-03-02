<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'ADMIN';
    case JEFE_SERVICIO = 'JEFE_SERVICIO';
    case GESTION_PERSONAS = 'GESTION_PERSONAS';
    case RRHH = 'RRHH';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::JEFE_SERVICIO => 'Jefe de Servicio',
            self::GESTION_PERSONAS => 'Gestión de Personas',
            self::RRHH => 'RRHH',
        };
    }
}
