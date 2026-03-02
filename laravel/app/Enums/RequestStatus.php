<?php

namespace App\Enums;

enum RequestStatus: string
{
    case BORRADOR = 'BORRADOR';
    case ENVIADA = 'ENVIADA';
    case EN_GESTION_PERSONAS = 'EN_GESTION_PERSONAS';
    case EN_RRHH = 'EN_RRHH';
    case OBSERVADA = 'OBSERVADA';
    case RECHAZADA = 'RECHAZADA';
    case EN_TRAMITACION_CONTRATO = 'EN_TRAMITACION_CONTRATO';
    case FINALIZADA = 'FINALIZADA';

    public function label(): string
    {
        return match ($this) {
            self::BORRADOR => 'Pendiente',
            self::ENVIADA => 'En revisión',
            self::EN_GESTION_PERSONAS => 'En gestión de personas',
            self::EN_RRHH => 'En RRHH',
            self::OBSERVADA => 'Observada',
            self::RECHAZADA => 'Rechazada',
            self::EN_TRAMITACION_CONTRATO => 'En contrato',
            self::FINALIZADA => 'Cerrada',
        };
    }
}
