<?php

use App\Http\Controllers\Api\AnswerController;
use App\Http\Controllers\Auth\GuestAccountController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\QuestionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/guest', [GuestAccountController::class, 'login']);


Route::group([
    'prefix' => '/polls', 
    'middleware' => 'auth:sanctum'
    ], function () {
        Route::get('/', [PollController::class, 'list'])->name("poll.list");
        
        Route::group([
            'prefix' => '{poll}', 
            'middleware' => 'can:view,poll',
            ], function () {
                Route::get('', [PollController::class, 'get'])
                    ->name("poll.get");

                Route::get('state', [PollController::class, 'state'])
                    ->name("poll.state");

                Route::get('questions', [PollController::class, 'questions'])
                    ->name("poll.question.list");
                
            }
        );
    }
);

Route::group([
    'prefix' => '/questions/{question}',
    'middleware' => ['auth:sanctum', 'can:view, poll'],
    ], function () { 
        Route::get('', [QuestionController::class, 'get'])
            ->name("poll.question.get");

        Route::get('answers', [QuestionController::class, 'getAnswers'])
            ->name("poll.question.answer.list");

        Route::get('answer', [AnswerController::class, 'show']);
    }
);

