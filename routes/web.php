<?php

use App\Http\Controllers\Api\PollManagementController;
use App\Http\Controllers\Web\PollIndexController;
use App\Http\Controllers\Web\ProfileController;
use App\Models\Poll;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth:sanctum'])->group(function(){
    Route::get('/', [PollIndexController::class, 'view'])
        ->name("index");
});
    

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
