<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\RequestWorkflowController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [RequestController::class, 'index'])->name('dashboard');

    Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [RequestController::class, 'create'])->name('requests.create');
    Route::post('/requests', [RequestController::class, 'store'])->name('requests.store');
    Route::get('/requests/{formSubmission}/edit', [RequestController::class, 'edit'])->name('requests.edit');
    Route::put('/requests/{formSubmission}', [RequestController::class, 'update'])->name('requests.update');
    Route::get('/requests/{request}', [RequestController::class, 'show'])->name('requests.show');

    Route::prefix('/requests/{request}/actions')->name('requests.actions.')->group(function () {
        Route::post('/send', [RequestWorkflowController::class, 'send'])->name('send');
        Route::post('/take', [RequestWorkflowController::class, 'take'])->name('take');
        Route::post('/send-to-rrhh', [RequestWorkflowController::class, 'sendToRrhh'])->name('send_to_rrhh');
        Route::post('/observe', [RequestWorkflowController::class, 'observe'])->name('observe');
        Route::post('/reject', [RequestWorkflowController::class, 'reject'])->name('reject');
        Route::post('/approve-rrhh', [RequestWorkflowController::class, 'approveRrhh'])->name('approve_rrhh');
        Route::post('/mark-contract-done', [RequestWorkflowController::class, 'markContractDone'])->name('mark_contract_done');
    });

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    });
});

require __DIR__.'/forms.php';
require __DIR__.'/auth.php';
