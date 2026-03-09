<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstamentSeeder extends Seeder
{
    public function run(): void
    {
        $estaments = [
            'Médico',
            'Odontólogo',
            'Bioquímico',
            'Químico Farmacéutico',
            'Profesional',
            'Técnico',
            'Administrativo',
            'Auxiliar'
        ];

        foreach ($estaments as $name) {
            DB::table('estaments')->updateOrInsert(
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