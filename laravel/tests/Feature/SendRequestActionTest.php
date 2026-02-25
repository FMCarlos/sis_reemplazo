<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Models\Request;
use App\Models\RequestAction;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SendRequestActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creator_can_send_draft_request_and_audit_log_is_created(): void
    {
        $user = User::factory()->create();
        $service = Service::query()->create(['name' => 'Servicio Test']);

        $request = Request::query()->create([
            'service_id' => $service->id,
            'created_by' => $user->id,
            'status' => RequestStatus::BORRADOR,
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('requests.actions.send', $request));

        $response->assertOk()
            ->assertJson([
                'ok' => true,
                'data' => [
                    'id' => $request->id,
                    'status' => RequestStatus::ENVIADA->value,
                ],
            ]);

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'status' => RequestStatus::ENVIADA->value,
        ]);

        $this->assertDatabaseHas('request_actions', [
            'request_id' => $request->id,
            'user_id' => $user->id,
            'action' => 'send',
            'from_status' => RequestStatus::BORRADOR->value,
            'to_status' => RequestStatus::ENVIADA->value,
        ]);

        $this->assertSame(1, RequestAction::query()->count());
    }
}
