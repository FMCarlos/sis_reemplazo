<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $specialties = [

            // anteriores
            'N/A',
            'Ginecología y Obstetricia',
            'Cirugía General',
            'Rehabilitación Oral',
            'Cirugía y Traumatología Buco Maxilofacial',

            // nuevos
            'Ingeniero Comercial',
            'Otros Técnicos',
            'Administrador Público',
            'Nutricionista',
            'Ingeniero en Prevención de Riesgos',
            'Otros Profesionales',
            'Ingeniero Biomédico',
            'Administrativo',
            'Ingeniero en Administración',
            'Enfermero/a',
            'Relacionador Público',
            'Cirujano Dentista',
            'Técnico en Enfermería',
            'Ingeniero en Control de Gestión',
            'Médico Cirujano',
            'Tecnólogo Médico Imagenología',
            'Ingeniero Industrial',
            'Auxiliar',
            'Matrón/a',
            'Psicólogo/a',
            'Asistente Social',
            'Fonoaudiólogo/a',
            'Educadora de Párvulos',
            'Técnico en Educación Parvularia',
            'Tecnólogo Médico Oftalmología',
            'Tecnólogo Médico Otorrinolaringología',
            'Kinesiólogo/a',
            'Sociólogo/a',
            'Ingeniero Informático'
        ];

        foreach ($specialties as $name) {
            DB::table('specialties')->updateOrInsert(
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