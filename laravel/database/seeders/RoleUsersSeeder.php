<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleUsersSeeder extends Seeder
{
    public function run(): void
    {
        $service = Service::query()->firstOrCreate(['name' => 'Servicio General']);

        User::query()->updateOrCreate(
            ['email' => 'jefe.servicio@example.com'],
            [
                'name' => 'Jefe Servicio Demo',
                'password' => Hash::make('Password123!'),
                'role' => UserRole::JEFE_SERVICIO,
                'service_id' => $service->id,
                'email_verified_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'gestion.personas@example.com'],
            [
                'name' => 'Gestión Personas Demo',
                'password' => Hash::make('Password123!'),
                'role' => UserRole::GESTION_PERSONAS,
                'service_id' => null,
                'email_verified_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'rrhh@example.com'],
            [
                'name' => 'RRHH Demo',
                'password' => Hash::make('Password123!'),
                'role' => UserRole::RRHH,
                'service_id' => null,
                'email_verified_at' => now(),
            ]
        );
    }
}
