<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegalQualitySeeder extends Seeder
{
    public function run(): void
    {
        $legalQualities = [
            'Comision de Servicio',
            'Contrata',
            'Honorario por hora',
            'Honorarios',
            'Planta',
            'Reemplazo Brecha',
            'Reemplazo cargo vacante',
            'Reemplazo transitorio',
            'Suplencia',
            'Supernumerario',
            'Titular',
        ];

        foreach ($legalQualities as $name) {
            DB::table('legal_qualities')->updateOrInsert(
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