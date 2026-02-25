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
}
