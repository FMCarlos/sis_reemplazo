<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AbsenceTypeSeeder::class,
            ServiceSeeder::class,
            SubUnitSeeder::class,
            EstamentSeeder::class,
            LegalQualitySeeder::class,
            ProfessionSeeder::class,
            SpecialtySeeder::class,
            RoleUsersSeeder::class,
            FormTypeSeeder::class,
            RequestSeeder::class,
        ]);
    }
}
