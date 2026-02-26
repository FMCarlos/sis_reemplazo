<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequestRequest;
use App\Services\RequestWorkflowService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
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

    public function store(StoreRequestRequest $request, RequestWorkflowService $workflowService): RedirectResponse
    {
        $this->authorize('create', \App\Models\Request::class);

        $workflowService->createDraft(
            $request->user(),
            $request->validated()
        );

        return redirect()
            ->route('requests.index')
            ->with('status', 'Solicitud creada en borrador.');
    }
}
