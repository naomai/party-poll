<?php

use App\Http\Controllers\Web\AnswerController;
use App\Http\Controllers\Auth\GuestAccountController;
use App\Http\Controllers\Web\InvitationController;
use App\Http\Controllers\Web\OnboardingController;
use App\Http\Controllers\Web\PollController;
use App\Http\Controllers\Web\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [OnboardingController::class, 'view'])
    ->name("index");

Route::put("/guest_upgrade", [GuestAccountController::class, 'store'])
    ->name("guest_upgrade");

Route::middleware(['auth:sanctum'])->group(function(){
    Route::resource('/polls', PollController::class);
    Route::put('/questions/{question}/answer', [AnswerController::class, "store"])
        ->name('question.answer.store');
});
    

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/invite/{poll}', [InvitationController::class, 'view'])->name('invite.view');

require __DIR__.'/auth.php';
