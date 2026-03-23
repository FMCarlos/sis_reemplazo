<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FormSubmissionController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', FormSubmission::class);

        $submissions = FormSubmission::query()
            ->with(['formType', 'submitter'])
            ->visibleTo(auth()->user())
            ->latest()
            ->paginate(10);

        return view('forms.submissions.index', [
            'submissions' => $submissions,
        ]);
    }

    public function show(FormSubmission $formSubmission): View
    {
        $this->authorize('view', $formSubmission);

        $formSubmission->load([
            'formType',
            'submitter',
            'actions.user',
        ]);

        return view('forms.submissions.show', [
            'submission' => $formSubmission,
        ]);
    }

    public function downloadPdf(FormSubmission $formSubmission): StreamedResponse
    {
        $this->authorize('downloadPdf', $formSubmission);

        abort_unless($formSubmission->pdf_path && Storage::disk('local')->exists($formSubmission->pdf_path), 404);

        return Storage::disk('local')->download(
            $formSubmission->pdf_path,
            'replacement-form-'.$formSubmission->id.'.pdf',
            ['Content-Type' => 'application/pdf'],
        );
    }
}
