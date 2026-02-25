<?php

namespace Database\Seeders;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\Request;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class RequestSeeder extends Seeder
{
    public function run(): void
    {
        $jefeServicio = User::query()
            ->where('role', UserRole::JEFE_SERVICIO)
            ->first();

        if (! $jefeServicio) {
            return;
        }

        $services = Service::query()->limit(3)->get();

        if ($services->isEmpty()) {
            return;
        }

        $statuses = [
            RequestStatus::BORRADOR,
            RequestStatus::BORRADOR,
            RequestStatus::OBSERVADA,
        ];

        foreach ($statuses as $index => $status) {
            $service = $services[$index % $services->count()];

            Request::query()->create([
                'service_id' => $service->id,
                'created_by' => $jefeServicio->id,
                'status' => $status,
            ]);
        }
    }
}
