<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use App\Enums\FormSubmissionStatus;
use App\Modules\Replacement\ReplacementFormHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ReplacementSubmissionWorkflowController extends Controller
{
    public function __construct(private readonly ReplacementFormHandler $handler) {}

    public function submit(Request $request, FormSubmission $formSubmission): JsonResponse|RedirectResponse
    {
        $this->authorize('submit', $formSubmission);

        return $this->perform($request, fn () => $this->handler->submit($formSubmission, $request->user()));
    }

    public function approve(Request $request, FormSubmission $formSubmission): JsonResponse|RedirectResponse
    {
        $this->authorize('approve', $formSubmission);

        return $this->perform($request, fn () => $this->handler->approve(
            $formSubmission,
            $request->user(),
            $request->string('comment')->toString() ?: null,
        ));
    }

    public function reject(Request $request, FormSubmission $formSubmission): JsonResponse|RedirectResponse
    {
        $this->authorize('reject', $formSubmission);

        return $this->perform($request, fn () => $this->handler->reject(
            $formSubmission,
            $request->user(),
            $request->string('comment')->toString() ?: null,
        ));
    }

    private function perform(Request $request, callable $callback): JsonResponse|RedirectResponse
    {
        try {
            $submission = $callback();
        } catch (RuntimeException $exception) {
            if (! $request->expectsJson()) {
                return back()->with('status', $exception->getMessage());
            }

            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        if (! $request->expectsJson()) {
            return redirect()
                ->route('forms.submissions.show', $submission)
                ->with('status', $this->successMessageFor($submission));
        }

        return response()->json([
            'ok' => true,
            'message' => 'Acción aplicada correctamente.',
            'data' => [
                'id' => $submission->id,
                'status' => $submission->status->value,
                'pdf_path' => $submission->pdf_path,
                'show_url' => route('forms.submissions.show', $submission),
                'edit_url' => $submission->status === FormSubmissionStatus::DRAFT
                    ? route('requests.edit', $submission)
                    : null,
            ],
        ]);
    }

    private function successMessageFor(FormSubmission $submission): string
    {
        return match ($submission->status) {
            FormSubmissionStatus::SUBMITTED => 'Solicitud enviada correctamente a RRHH.',
            FormSubmissionStatus::APPROVED => 'Solicitud aprobada correctamente por RRHH.',
            FormSubmissionStatus::REJECTED => 'Solicitud rechazada correctamente por RRHH.',
            default => 'Acción aplicada correctamente.',
        };
    }
}
