<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfessionSeeder extends Seeder
{
    public function run(): void
    {
        $professions = [
            'N/A',
            'Médico Cirujano',
            'Enfermero/a',
            'Matrón/a',
            'Tecnólogo Médico',
            'Terapeuta Ocupacional',
            'Kinesiólogo/a',
            'Psicólogo/a',
            'Nutricionista',
            'Fonoaudiólogo/a',
            'Químico Farmacéutico',
            'Bioquímico',
            'Cirujano Dentista',
            'Técnico en Enfermería',
            'Otros Técnicos de Salud',
            'Otros Técnicos',
            'Asistente Social',
            'Trabajador/a Social',
            'Ingeniero/a Comercial',
            'Administrador Público',
            'Ingeniero/a Prevención de Riesgos',
            'Ingeniero/a en Administración',
            'Relacionador Público',
            'Ingeniero/a Control de Gestión',
            'Sociólogo/a',
            'Ingeniero/a en Informática',
            'Otros Profesionales'
        ];

        foreach ($professions as $name) {
            DB::table('professions')->updateOrInsert(
                ['name' => $name],
                [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
    }
}