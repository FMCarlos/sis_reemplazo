<?php

use App\Http\Controllers\FormSubmissionController;
use App\Http\Controllers\FormTypeController;
use App\Http\Controllers\ReplacementSubmissionWorkflowController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('forms')->name('forms.')->group(function () {
    Route::get('/', [FormTypeController::class, 'index'])->name('index');

    Route::prefix('submissions')->name('submissions.')->group(function () {
        Route::get('/', [FormSubmissionController::class, 'index'])->name('index');
        Route::get('/{formSubmission}', [FormSubmissionController::class, 'show'])->name('show');
        Route::get('/{formSubmission}/pdf', [FormSubmissionController::class, 'downloadPdf'])->name('pdf');
        Route::post('/{formSubmission}/submit', [ReplacementSubmissionWorkflowController::class, 'submit'])->name('submit');
        Route::post('/{formSubmission}/approve', [ReplacementSubmissionWorkflowController::class, 'approve'])->name('approve');
        Route::post('/{formSubmission}/reject', [ReplacementSubmissionWorkflowController::class, 'reject'])->name('reject');
    });
});
