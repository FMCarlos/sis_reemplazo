<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequestRequest;
use App\Services\RequestWorkflowService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', \App\Models\Request::class);

        $requests = \App\Models\Request::query()
            ->with('service')
            ->visibleTo($request->user())
            ->latest()
            ->get();

        return view('requests.index', [
            'requests' => $requests,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', \App\Models\Request::class);

        return view('requests.create');
    }

    public function store(StoreRequestRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Request::class);

        $user = $request->user();
        $validated = $request->validated();

        $createdRequest = \App\Models\Request::query()->create([
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
