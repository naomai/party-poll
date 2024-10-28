<?php

use App\Http\Controllers\Web\AnswerController;
use App\Http\Controllers\Auth\GuestAccountController;
use App\Http\Controllers\Web\OnboardingController;
use App\Http\Controllers\Web\PollManagementController;
use App\Http\Controllers\Web\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [OnboardingController::class, 'view'])
    ->name("index");

Route::put("/guest_upgrade", [GuestAccountController::class, 'store'])
    ->name("guest_upgrade");

Route::middleware(['auth:sanctum'])->group(function(){
    Route::resource('/polls', PollManagementController::class);
    Route::put('/questions/{question}/answer', [AnswerController::class, "store"])
        ->name('question.answer.store');
});
    

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
