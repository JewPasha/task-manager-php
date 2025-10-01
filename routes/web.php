<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    $tasks = auth()->user()->tasks()->with('category')->latest()->take(5)->get();
    $categories = auth()->user()->categories()->get();
    
    
    $tasks->transform(function ($task) {
        $task->is_due_soon = $task->isDueSoon();
        $task->is_overdue = $task->isOverdue();
        return $task;
    });
    
    return Inertia::render('Dashboard', [
        'tasks' => $tasks,
        'categories' => $categories,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    
    Route::post('/tasks', [\App\Http\Controllers\TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}', [\App\Http\Controllers\TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [\App\Http\Controllers\TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::patch('/tasks/{task}/toggle', [\App\Http\Controllers\TaskController::class, 'toggle'])->name('tasks.toggle');
    
    
    Route::get('/tasks/export', [\App\Http\Controllers\TaskExportController::class, 'index'])->name('tasks.export.index');
    Route::post('/tasks/export', [\App\Http\Controllers\TaskExportController::class, 'export'])->name('tasks.export');
    Route::post('/tasks/import', [\App\Http\Controllers\TaskExportController::class, 'import'])->name('tasks.import');
    
  
    Route::resource('categories', \App\Http\Controllers\CategoryController::class);
});

require __DIR__.'/auth.php';
