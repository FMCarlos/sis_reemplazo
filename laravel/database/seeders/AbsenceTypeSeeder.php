<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbsenceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Licencia Médica',
            'Permiso Administrativo c/goce',
            'Permiso Administrativo s/goce',
            'Licencia Maternal',
            'Cargo Vacante',
        ];

        foreach ($types as $type) {
            DB::table('absence_types')->updateOrInsert(
                ['name' => $type],
                [
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now()
                ]
            );
        }
    }
}