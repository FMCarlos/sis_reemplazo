<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\Request;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestIndexFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_jefe_servicio_sees_tabs_counts_and_filters_requests(): void
    {
        $service = Service::query()->create(['name' => 'Urgencias']);

        $jefe = User::factory()->create([
            'role' => UserRole::JEFE_SERVICIO,
            'service_id' => $service->id,
        ]);

        $pendingRequest = Request::query()->create([
            'service_id' => $service->id,
            'created_by' => $jefe->id,
            'status' => RequestStatus::BORRADOR,
            'motivo' => 'Motivo 1',
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-03-10',
            'nombre_reemplazo' => 'Ana Soto',
            'created_at' => now()->subDays(2),
        ]);

        $reviewRequest = Request::query()->create([
            'service_id' => $service->id,
            'created_by' => $jefe->id,
            'status' => RequestStatus::ENVIADA,
            'motivo' => 'Cobertura turno',
            'fecha_inicio' => '2026-03-11',
            'fecha_fin' => '2026-03-15',
            'nombre_reemplazo' => 'Carlos Ruiz',
            'created_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($jefe)
            ->get(route('requests.index', [
                'tab' => 'pendientes',
                'q' => 'Ana',
            ]));

        $response->assertOk()
            ->assertSeeText('Pendientes')
            ->assertSeeText('En revisión')
            ->assertSeeText('Cerradas')
            ->assertSeeText('#'.$pendingRequest->id)
            ->assertDontSeeText('#'.$reviewRequest->id);
    }

    public function test_admin_can_filter_by_service(): void
    {
        $serviceA = Service::query()->create(['name' => 'Urgencias']);
        $serviceB = Service::query()->create(['name' => 'Pediatría']);

        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $creatorA = User::factory()->create([
            'role' => UserRole::JEFE_SERVICIO,
            'service_id' => $serviceA->id,
        ]);

        $creatorB = User::factory()->create([
            'role' => UserRole::JEFE_SERVICIO,
            'service_id' => $serviceB->id,
        ]);

        $serviceARequest = Request::query()->create([
            'service_id' => $serviceA->id,
            'created_by' => $creatorA->id,
            'status' => RequestStatus::ENVIADA,
            'motivo' => 'A',
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-03-05',
            'nombre_reemplazo' => 'Nombre A',
        ]);

        $serviceBRequest = Request::query()->create([
            'service_id' => $serviceB->id,
            'created_by' => $creatorB->id,
            'status' => RequestStatus::ENVIADA,
            'motivo' => 'B',
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-03-05',
            'nombre_reemplazo' => 'Nombre B',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('requests.index', [
                'tab' => 'activas',
                'servicio' => $serviceA->id,
            ]));

        $response->assertOk()
            ->assertSeeText('Servicio')
            ->assertSeeText('#'.$serviceARequest->id)
            ->assertDontSeeText('#'.$serviceBRequest->id);
    }
}
