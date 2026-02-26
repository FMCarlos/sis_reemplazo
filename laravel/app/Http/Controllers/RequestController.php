<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Http\Requests\StoreRequestRequest;
use App\Models\Request as WorkflowRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function index(Request $request): View
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

    public function create(Request $request): View
    {
        $this->authorize('create', WorkflowRequest::class);

        return view('requests.create');
    }

    public function store(StoreRequestRequest $request): RedirectResponse
    {
        $this->authorize('create', WorkflowRequest::class);

        $user = $request->user();
        $validated = $request->validated();

        WorkflowRequest::query()->create([
            'service_id' => $user->service_id,
            'created_by' => $user->id,
            'status' => RequestStatus::BORRADOR,
            'motivo' => $validated['motivo'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'nombre_reemplazo' => $validated['nombre_reemplazo'],
        ]);

        return redirect()
            ->route('requests.index')
            ->with('status', 'Solicitud creada en borrador.');
    }
}
