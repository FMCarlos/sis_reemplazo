<?php

namespace App\Http\Controllers;

use App\Models\FormType;
use Illuminate\Contracts\View\View;

class FormTypeController extends Controller
{
    public function index(): View
    {
        $formTypes = FormType::query()
            ->withCount('submissions')
            ->orderByDesc('active')
            ->orderBy('name')
            ->get();

        return view('forms.index', [
            'formTypes' => $formTypes,
        ]);
    }
}
