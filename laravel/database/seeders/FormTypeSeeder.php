<?php

namespace Database\Seeders;

use App\Models\FormType;
use Illuminate\Database\Seeder;

class FormTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            [
                'code' => 'replacement_request',
                'name' => 'Solicitud de reemplazo',
                'description' => 'Formulario institucional para solicitudes de reemplazo.',
                'active' => true,
            ],
            [
                'code' => 'overtime_request',
                'name' => 'Solicitud de horas extraordinarias',
                'description' => 'Formulario institucional para horas extraordinarias.',
                'active' => false,
            ],
            [
                'code' => 'event_notification',
                'name' => 'Notificación de eventos',
                'description' => 'Formulario institucional para notificación de eventos.',
                'active' => false,
            ],
        ] as $formType) {
            FormType::query()->updateOrCreate(
                ['code' => $formType['code']],
                $formType,
            );
        }
    }
}
