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
    Route::get('/requests/{request}', [RequestController::class, 'show'])->name('requests.show');
    Route::post('/requests', [RequestController::class, 'store'])->name('requests.store');

    Route::post('/requests/{request}/actions/{action}', [RequestWorkflowController::class, 'perform'])
        ->name('requests.actions.perform');

    Route::post('/requests/{request}/actions/send', [RequestWorkflowController::class, 'send'])
        ->name('requests.actions.send');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    });
});

require __DIR__.'/auth.php';
