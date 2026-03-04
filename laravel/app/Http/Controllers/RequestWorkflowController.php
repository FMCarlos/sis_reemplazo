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
        return $this->perform($request, fn () => $workflowService->send($request, $httpRequest->user()));
    }

    public function take(Request $httpRequest, WorkflowRequest $request, RequestWorkflowService $workflowService): JsonResponse
    {
        return $this->perform($request, fn () => $workflowService->take($request, $httpRequest->user()));
    }

    public function sendToRrhh(Request $httpRequest, WorkflowRequest $request, RequestWorkflowService $workflowService): JsonResponse
    {
        return $this->perform($request, fn () => $workflowService->sendToRrhh($request, $httpRequest->user()));
    }

    public function observe(Request $httpRequest, WorkflowRequest $request, RequestWorkflowService $workflowService): JsonResponse
    {
        return $this->perform($request, fn () => $workflowService->observe(
            $request,
            $httpRequest->user(),
            $httpRequest->string('comment')->toString()
        ));
    }

    public function reject(Request $httpRequest, WorkflowRequest $request, RequestWorkflowService $workflowService): JsonResponse
    {
        return $this->perform($request, fn () => $workflowService->reject(
            $request,
            $httpRequest->user(),
            $httpRequest->string('comment')->toString()
        ));
    }

    public function approveRrhh(Request $httpRequest, WorkflowRequest $request, RequestWorkflowService $workflowService): JsonResponse
    {
        return $this->perform($request, fn () => $workflowService->approveRrhh($request, $httpRequest->user()));
    }

    public function markContractDone(Request $httpRequest, WorkflowRequest $request, RequestWorkflowService $workflowService): JsonResponse
    {
        return $this->perform($request, fn () => $workflowService->markContractDone($request, $httpRequest->user()));
    }

    private function perform(WorkflowRequest $request, callable $callback): JsonResponse
    {
        $this->authorize('actions', $request);

        try {
            $updatedRequest = $callback();
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
}
