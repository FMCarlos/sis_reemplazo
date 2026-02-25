<?php

namespace App\Http\Controllers;

use App\Models\Request as WorkflowRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', WorkflowRequest::class);

        $requests = WorkflowRequest::query()
            ->with('service')
            ->visibleTo($request->user())
            ->latest()
            ->get();

        return view('dashboard', [
            'requests' => $requests,
        ]);
    }
}
