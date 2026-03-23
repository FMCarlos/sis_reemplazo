<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use Illuminate\Contracts\View\View;

class FormSubmissionController extends Controller
{
    public function index(): View
    {
        $submissions = FormSubmission::query()
            ->with(['formType', 'submitter'])
            ->latest()
            ->paginate(10);

        return view('forms.submissions.index', [
            'submissions' => $submissions,
        ]);
    }

    public function show(FormSubmission $formSubmission): View
    {
        $formSubmission->load([
            'formType',
            'submitter',
            'actions.user',
        ]);

        return view('forms.submissions.show', [
            'submission' => $formSubmission,
        ]);
    }
}
