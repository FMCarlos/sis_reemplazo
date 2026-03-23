<?php

use App\Http\Controllers\FormSubmissionController;
use App\Http\Controllers\FormTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('forms')->name('forms.')->group(function () {
    Route::get('/', [FormTypeController::class, 'index'])->name('index');

    Route::prefix('submissions')->name('submissions.')->group(function () {
        Route::get('/', [FormSubmissionController::class, 'index'])->name('index');
        Route::get('/{formSubmission}', [FormSubmissionController::class, 'show'])->name('show');
    });
});
