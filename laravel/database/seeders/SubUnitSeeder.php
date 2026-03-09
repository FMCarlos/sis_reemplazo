<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubUnitSeeder extends Seeder
{
    public function run(): void
    {
        $subUnits = [
            'Sub-unidad de Gestión del Riesgo y Tecnovigilancia',
            'Sub-unidad de Acreditación',
            'Sub-unidad de Autorización Sanitaria',
            'Sub-unidad de Vigilancia Epidemiologia',
            'Sub-unidad de Estadísticas',
            'Sub-unidad Centro de Costos',
            'Sub-unidad Análisis de Registros Clínicos - GRD',
            'Sub-unidad Hospitalización Domiciliaria',
            'Sub-unidad Laboratorio Clínico – UMT',
            'Sub-unidad Laboratorio Biología Molecular',
            'Sub-unidad UCP (unidad central de producción)',
            'Sub-unidad SEDILE - CEFE',
            'Lista de Espera',
            'Admisión',
            'Recaudación',
            'Coordinación',
            'Sub-unidad Bodega Central',
            'Sub-unidad Bodega Fármacos e Insumos',
            'Sub-unidad Movilización',
            'Sub-unidad Aseo',
            'Sub-unidad Lavandería y Ropería',
            'N/A'
        ];

        foreach ($subUnits as $name) {
            DB::table('sub_units')->updateOrInsert(
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