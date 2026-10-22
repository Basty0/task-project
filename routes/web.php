<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [TicketController::class, 'index'])->name('dashboard');

    Route::redirect('tickets', '/dashboard');

    Route::post('tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::patch('tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
    Route::delete('tickets/{ticket}', [TicketController::class, 'destroy'])->name('tickets.destroy');

    Route::post('tickets/{ticket}/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('tickets/{ticket}/tasks/{task}', [TaskController::class, 'update'])
        ->scopeBindings()
        ->name('tasks.update');
    Route::delete('tickets/{ticket}/tasks/{task}', [TaskController::class, 'destroy'])
        ->scopeBindings()
        ->name('tasks.destroy');

    Route::middleware('admin')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
