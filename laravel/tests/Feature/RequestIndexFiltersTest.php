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
                'tab' => 'en_revision',
                'servicio' => $serviceA->id,
            ]));

        $response->assertOk()
            ->assertSeeText('Servicio')
            ->assertSeeText('#'.$serviceARequest->id)
            ->assertDontSeeText('#'.$serviceBRequest->id);
    }

    public function test_tab_counts_respect_active_search_filter(): void
    {
        $service = Service::query()->create(['name' => 'Urgencias']);

        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $creator = User::factory()->create([
            'role' => UserRole::JEFE_SERVICIO,
            'service_id' => $service->id,
        ]);

        Request::query()->create([
            'service_id' => $service->id,
            'created_by' => $creator->id,
            'status' => RequestStatus::BORRADOR,
            'motivo' => 'Cobertura UTI',
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-03-05',
            'nombre_reemplazo' => 'Ana Soto',
        ]);

        Request::query()->create([
            'service_id' => $service->id,
            'created_by' => $creator->id,
            'status' => RequestStatus::ENVIADA,
            'motivo' => 'Reemplazo general',
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-03-05',
            'nombre_reemplazo' => 'Carlos Ruiz',
        ]);

        Request::query()->create([
            'service_id' => $service->id,
            'created_by' => $creator->id,
            'status' => RequestStatus::FINALIZADA,
            'motivo' => 'Otra cobertura',
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-03-05',
            'nombre_reemplazo' => 'María López',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('requests.index', [
                'tab' => 'pendientes',
                'q' => 'Ana',
            ]));

        $response->assertOk()
            ->assertViewHas('tabCounts', function (array $tabCounts): bool {
                return ($tabCounts['pendientes'] ?? null) === 1
                    && ($tabCounts['en_revision'] ?? null) === 0
                    && ($tabCounts['en_rrhh'] ?? null) === 0
                    && ($tabCounts['en_contrato'] ?? null) === 0
                    && ($tabCounts['cerradas'] ?? null) === 0;
            });
    }

}
