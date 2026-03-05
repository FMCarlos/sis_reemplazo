<?php

use App\Enums\RequestStatus;

return [
    'order' => [
        'pendientes',
        'en_revision',
        'en_gestion',
        'en_rrhh',
        'en_contrato',
        'cerradas',
    ],

    'definitions' => [
        'pendientes' => [
            'label' => 'Pendientes',
            'colorClass' => 'primary',
        ],
        'en_revision' => [
            'label' => 'En revisión',
            'colorClass' => 'warning',
        ],
        'en_gestion' => [
            'label' => 'En gestión',
            'colorClass' => 'warning',
        ],
        'en_rrhh' => [
            'label' => 'En RRHH',
            'colorClass' => 'info',
        ],
        'en_contrato' => [
            'label' => 'En contrato',
            'colorClass' => 'success',
        ],
        'cerradas' => [
            'label' => 'Cerradas',
            'colorClass' => 'secondary',
        ],
    ],

    'roles' => [
        'ADMIN' => [
            'pendientes' => [RequestStatus::BORRADOR->value],
            'en_revision' => [RequestStatus::ENVIADA->value, RequestStatus::EN_GESTION_PERSONAS->value],
            'en_rrhh' => [RequestStatus::EN_RRHH->value, RequestStatus::OBSERVADA->value],
            'en_contrato' => [RequestStatus::EN_TRAMITACION_CONTRATO->value],
            'cerradas' => [RequestStatus::RECHAZADA->value, RequestStatus::FINALIZADA->value],
        ],
        'JEFE_SERVICIO' => [
            'pendientes' => [RequestStatus::BORRADOR->value, RequestStatus::OBSERVADA->value],
            'en_revision' => [RequestStatus::ENVIADA->value, RequestStatus::EN_GESTION_PERSONAS->value, RequestStatus::EN_RRHH->value],
            'cerradas' => [RequestStatus::RECHAZADA->value, RequestStatus::EN_TRAMITACION_CONTRATO->value, RequestStatus::FINALIZADA->value],
        ],
        'GESTION_PERSONAS' => [
            'en_gestion' => [RequestStatus::ENVIADA->value, RequestStatus::EN_GESTION_PERSONAS->value],
            'en_rrhh' => [RequestStatus::EN_RRHH->value],
            'en_contrato' => [RequestStatus::EN_TRAMITACION_CONTRATO->value],
            'cerradas' => [RequestStatus::FINALIZADA->value, RequestStatus::RECHAZADA->value, RequestStatus::OBSERVADA->value],
        ],
        'RRHH' => [
            'pendientes' => [RequestStatus::EN_RRHH->value],
            'en_contrato' => [RequestStatus::EN_TRAMITACION_CONTRATO->value],
            'cerradas' => [RequestStatus::FINALIZADA->value, RequestStatus::RECHAZADA->value, RequestStatus::OBSERVADA->value],
        ],
    ],
];
