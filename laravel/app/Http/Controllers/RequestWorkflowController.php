<?php

namespace App\Http\Controllers;

use App\Models\Request as WorkflowRequest;
use App\Services\RequestWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class RequestWorkflowController extends Controller
{
    public function perform(Request $httpRequest, WorkflowRequest $request, string $action, RequestWorkflowService $workflowService): JsonResponse
    {
        $this->authorize('actions', $request);

        try {
            $updatedRequest = $workflowService->apply(
                $request,
                $httpRequest->user(),
                $action,
                $httpRequest->string('comment')->toString()
            );
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 404);
        } catch (RuntimeException $exception) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Acción aplicada correctamente.',
            'data' => [
                'id' => $updatedRequest->id,
                'status' => $updatedRequest->status->value,
            ],
        ]);
    }

    public function send(Request $httpRequest, WorkflowRequest $request, RequestWorkflowService $workflowService): JsonResponse
    {
        return $this->perform($httpRequest, $request, 'send', $workflowService);
    }
}
