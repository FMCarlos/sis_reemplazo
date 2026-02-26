<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Http\Requests\StoreRequestRequest;
use App\Models\Request as WorkflowRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;

class RequestController extends Controller
{
    public function index(HttpRequest $request): View
    {
        $this->authorize('viewAny', WorkflowRequest::class);

        $requests = WorkflowRequest::query()
            ->with('service')
            ->visibleTo($request->user())
            ->latest()
            ->get();

        return view('requests.index', [
            'requests' => $requests,
        ]);
    }

    public function create(HttpRequest $request): View
    {
        $this->authorize('create', WorkflowRequest::class);

        return view('requests.create');
    }

    public function show(WorkflowRequest $request): View
    {
        $this->authorize('view', $request);

        $request->load([
            'service',
            'creator',
            'actions' => fn ($query) => $query->with('user')->latest(),
            'attachments',
        ]);

        return view('requests.show', [
            'requestModel' => $request,
        ]);
    }

    public function store(StoreRequestRequest $request): JsonResponse
    {
        $this->authorize('create', WorkflowRequest::class);

        $user = $request->user();
        $validated = $request->validated();

        $createdRequest = WorkflowRequest::query()->create([
            'service_id' => $user->service_id,
            'created_by' => $user->id,
            'status' => RequestStatus::BORRADOR,
            'motivo' => $validated['motivo'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'nombre_reemplazo' => $validated['nombre_reemplazo'],
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Solicitud creada en borrador.',
            'data' => [
                'id' => $createdRequest->id,
                'status' => $createdRequest->status->value,
            ],
        ], 201);
    }
}
