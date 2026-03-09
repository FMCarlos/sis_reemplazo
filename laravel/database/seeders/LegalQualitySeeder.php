<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegalQualitySeeder extends Seeder
{
    public function run(): void
    {
        $legalQualities = [
            'Contrata',
            'Planta',
            'Honorarios',
            'Reemplazo',
            'Suplencia',
            'Titular'
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