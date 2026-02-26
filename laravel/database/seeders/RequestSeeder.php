<?php

namespace Database\Seeders;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\Request;
use App\Models\User;
use Illuminate\Database\Seeder;

class RequestSeeder extends Seeder
{
    public function run(): void
    {
        $jefeServicio = User::query()
            ->where('role', UserRole::JEFE_SERVICIO)
            ->whereNotNull('service_id')
            ->first();

        if (! $jefeServicio) {
            return;
        }

        foreach ([1, 2] as $index) {
            Request::query()->create([
                'service_id' => $jefeServicio->service_id,
                'created_by' => $jefeServicio->id,
                'status' => RequestStatus::BORRADOR,
                'motivo' => 'Cobertura temporal '.$index,
                'fecha_inicio' => now()->addDays($index)->toDateString(),
                'fecha_fin' => now()->addDays($index + 5)->toDateString(),
                'nombre_reemplazo' => 'Reemplazo Demo '.$index,
            ]);
        }
    }
}
