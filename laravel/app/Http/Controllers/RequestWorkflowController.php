<?php

namespace App\Http\Controllers;

use App\Models\Request as WorkflowRequest;
use App\Services\RequestWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use RuntimeException;

class RequestWorkflowController extends Controller
{
    public function send(Request $httpRequest, WorkflowRequest $request, RequestWorkflowService $workflowService): JsonResponse
    {
        return $this->perform($request, fn () => $workflowService->send($httpRequest->user(), $request));
    }


    public function observe(Request $httpRequest, WorkflowRequest $request, RequestWorkflowService $workflowService): JsonResponse
    {
        return $this->perform($request, fn () => $workflowService->observe(
            $httpRequest->user(),
            $request,
            ['comment' => $httpRequest->string('comment')->toString()]
        ));
    }

    public function reject(Request $httpRequest, WorkflowRequest $request, RequestWorkflowService $workflowService): JsonResponse
    {
        return $this->perform($request, fn () => $workflowService->reject(
            $httpRequest->user(),
            $request,
            ['comment' => $httpRequest->string('comment')->toString()]
        ));
    }

    public function approveRrhh(Request $httpRequest, WorkflowRequest $request, RequestWorkflowService $workflowService): JsonResponse
    {
        return $this->perform($request, fn () => $workflowService->approve_rrhh($httpRequest->user(), $request));
    }

    private function perform(WorkflowRequest $request, callable $callback): JsonResponse
    {
        $this->authorize('actions', $request);
        $user = request()->user();

        try {
            $updatedRequest = $callback();
        } catch (RuntimeException $exception) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        $latestAction = $updatedRequest->actions()
            ->with('user')
            ->latest()
            ->first();

        return response()->json([
            'ok' => true,
            'message' => 'Acción aplicada correctamente.',
            'data' => [
                'id' => $updatedRequest->id,
                'status' => $updatedRequest->status->value,
                'can' => [
                    'send' => Gate::forUser($user)->allows('send', $updatedRequest),
                    'approve_rrhh' => Gate::forUser($user)->allows('approveRrhh', $updatedRequest),
                    'observe' => Gate::forUser($user)->allows('observe', $updatedRequest),
                    'reject' => Gate::forUser($user)->allows('reject', $updatedRequest),
                ],
                'latest_action' => $latestAction ? [
                    'created_at' => $latestAction->created_at?->format('Y-m-d H:i') ?? '—',
                    'user' => $latestAction->user?->name ?? 'Usuario eliminado',
                    'action' => $latestAction->action,
                    'from_status' => $latestAction->from_status,
                    'to_status' => $latestAction->to_status,
                    'comment' => $latestAction->comment ?: '—',
                ] : null,
            ],
        ]);
    }
}
