<?php

use App\Http\Controllers\TaskController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::resource('tickets', TicketController::class)->except(['create', 'edit']);

    Route::post('tickets/{ticket}/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('tickets/{ticket}/tasks/{task}', [TaskController::class, 'update'])
        ->scopeBindings()
        ->name('tasks.update');
    Route::delete('tickets/{ticket}/tasks/{task}', [TaskController::class, 'destroy'])
        ->scopeBindings()
        ->name('tasks.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
