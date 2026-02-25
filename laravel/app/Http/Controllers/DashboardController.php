<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $requests = $request->user()
            ->requestsCreated()
            ->with('service')
            ->latest()
            ->get();

        return view('dashboard', [
            'requests' => $requests,
        ]);
    }
}
