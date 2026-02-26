<?php

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
    Route::get('/requests/{request}', [RequestController::class, 'show'])->name('requests.show');
    Route::post('/requests', [RequestController::class, 'store'])->name('requests.store');

    Route::post('/requests/{request}/actions/send', [RequestWorkflowController::class, 'send'])
        ->name('requests.actions.send');
});

require __DIR__.'/auth.php';
