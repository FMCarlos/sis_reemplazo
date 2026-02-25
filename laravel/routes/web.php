<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RequestWorkflowController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/requests/{request}/actions/send', [RequestWorkflowController::class, 'send'])
        ->name('requests.actions.send');
});

require __DIR__.'/auth.php';
