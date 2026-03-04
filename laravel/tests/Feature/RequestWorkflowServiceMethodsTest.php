<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\Request;
use App\Models\Service;
use App\Models\User;
use App\Services\RequestWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class RequestWorkflowServiceMethodsTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_method_changes_status_to_enviada(): void
    {
        $service = Service::query()->create(['name' => 'Urgencias']);

        $jefe = User::factory()->create([
            'role' => UserRole::JEFE_SERVICIO,
            'service_id' => $service->id,
        ]);

        $request = Request::query()->create([
            'service_id' => $service->id,
            'created_by' => $jefe->id,
            'status' => RequestStatus::BORRADOR,
        ]);

        $updatedRequest = app(RequestWorkflowService::class)->send($jefe, $request);

        $this->assertSame(RequestStatus::ENVIADA, $updatedRequest->status);
    }

    public function test_observe_method_requires_comment(): void
    {
        $gestion = User::factory()->create([
            'role' => UserRole::GESTION_PERSONAS,
        ]);

        $request = Request::query()->create([
            'service_id' => Service::query()->create(['name' => 'Pediatría'])->id,
            'created_by' => User::factory()->create()->id,
            'status' => RequestStatus::EN_GESTION_PERSONAS,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Debe ingresar un comentario para esta acción.');

        app(RequestWorkflowService::class)->observe($gestion, $request, ['comment' => '']);
    }
}
