<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\Request;
use App\Models\RequestAction;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestWorkflowActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_gestion_personas_transition_routes_are_not_available(): void
    {
        $service = Service::query()->create(['name' => 'Urgencias']);

        $jefe = User::factory()->create([
            'role' => UserRole::JEFE_SERVICIO,
            'service_id' => $service->id,
        ]);

        $request = Request::query()->create([
            'service_id' => $service->id,
            'created_by' => $jefe->id,
            'status' => RequestStatus::ENVIADA,
        ]);

        $this->assertFalse(app('router')->has('requests.actions.take'));
        $this->assertFalse(app('router')->has('requests.actions.send_to_rrhh'));
        $this->assertFalse(app('router')->has('requests.actions.mark_contract_done'));
        $this->assertFalse($jefe->can('take', $request));
    }

    public function test_observe_and_reject_require_comment(): void
    {
        $service = Service::query()->create(['name' => 'Pediatría']);

        $rrhh = User::factory()->create([
            'role' => UserRole::RRHH,
        ]);

        $request = Request::query()->create([
            'service_id' => $service->id,
            'created_by' => User::factory()->create(['service_id' => $service->id])->id,
            'status' => RequestStatus::EN_RRHH,
        ]);

        $observeResponse = $this->actingAs($rrhh)
            ->postJson(route('requests.actions.observe', $request), ['comment' => '']);

        $observeResponse->assertStatus(422);

        $rejectResponse = $this->actingAs($rrhh)
            ->postJson(route('requests.actions.reject', $request), ['comment' => 'Falta documentación']);

        $rejectResponse->assertOk();

        $this->assertDatabaseHas('request_actions', [
            'request_id' => $request->id,
            'action' => 'reject',
            'from_status' => RequestStatus::EN_RRHH->value,
            'to_status' => RequestStatus::RECHAZADA->value,
            'comment' => 'Falta documentación',
        ]);

        $this->assertSame(1, RequestAction::query()->count());
    }
}
