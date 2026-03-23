<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use App\Modules\Replacement\ReplacementFormHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ReplacementSubmissionWorkflowController extends Controller
{
    public function __construct(private readonly ReplacementFormHandler $handler) {}

    public function submit(Request $request, FormSubmission $formSubmission): JsonResponse
    {
        $this->authorize('submit', $formSubmission);

        return $this->perform(fn () => $this->handler->submit($formSubmission, $request->user()));
    }

    public function approve(Request $request, FormSubmission $formSubmission): JsonResponse
    {
        $this->authorize('approve', $formSubmission);

        return $this->perform(fn () => $this->handler->approve(
            $formSubmission,
            $request->user(),
            $request->string('comment')->toString() ?: null,
        ));
    }

    public function reject(Request $request, FormSubmission $formSubmission): JsonResponse
    {
        $this->authorize('reject', $formSubmission);

        return $this->perform(fn () => $this->handler->reject(
            $formSubmission,
            $request->user(),
            $request->string('comment')->toString() ?: null,
        ));
    }

    private function perform(callable $callback): JsonResponse
    {
        try {
            $submission = $callback();
        } catch (RuntimeException $exception) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Acción aplicada correctamente.',
            'data' => [
                'id' => $submission->id,
                'status' => $submission->status->value,
                'pdf_path' => $submission->pdf_path,
                'show_url' => route('forms.submissions.show', $submission),
                'edit_url' => $submission->status === \App\Enums\FormSubmissionStatus::DRAFT
                    ? route('requests.edit', $submission)
                    : null,
            ],
        ]);
    }
}
