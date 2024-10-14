<?php

use App\Http\Controllers\Auth\GuestAccountController;
use App\Http\Controllers\PollController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/create_guest_profile', [GuestAccountController::class, 'login']);

Route::get('/polls', [PollController::class, 'list'])->name("poll.list");
Route::get('/polls/{id}', [PollController::class, 'get'])->name("poll.get");
Route::get('/polls/{id}/questions', [PollController::class, 'questions'])->name("poll.questions");
