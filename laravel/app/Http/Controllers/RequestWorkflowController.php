<?php

namespace App\Http\Controllers;

use App\Models\Request as WorkflowRequest;
use App\Services\RequestWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class RequestWorkflowController extends Controller
{
    public function send(Request $httpRequest, WorkflowRequest $request, RequestWorkflowService $workflowService): JsonResponse
    {
        $this->authorize('send', $request);

        try {
            $updatedRequest = $workflowService->apply(
                $request,
                $httpRequest->user(),
                'send',
                $httpRequest->input('comment')
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Solicitud enviada correctamente.',
            'data' => [
                'id' => $updatedRequest->id,
                'status' => $updatedRequest->status->value,
            ],
        ]);
    }
}
