<?php

use App\Http\Controllers\Api\AnswerController;
use App\Http\Controllers\Auth\GuestAccountController;
use App\Http\Controllers\Api\PollManagementController;
use App\Http\Controllers\Api\PollProgressController;
use App\Http\Controllers\Api\QuestionManagementController;
use App\Http\Controllers\QuestionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/guest', [GuestAccountController::class, 'login']);



Route::group([
    'middleware' => 'auth:sanctum'
    ], function () {
        Route::apiResource('/polls', PollManagementController::class);
    
        Route::group([
            'prefix' => '/polls/{poll}', 
            'middleware' => 'can:view,poll',
            ], function () {
                Route::get('state', [PollProgressController::class, 'view'])
                    ->name('poll.state');

                Route::apiResource('questions', QuestionManagementController::class)
                    ->name('index', 'poll.question.list')
                    ->name('show', 'poll.question.get');

                Route::group([
                    'prefix' => 'questions/{question}',
                    'middleware' => ['auth:sanctum'],
                    ], function () { 
                        Route::get('answer', [AnswerController::class, 'show']);

                        /*Route::get('participants_answers', [QuestionController::class, 'getAnswers'])
                            ->name("poll.question.answer.list");*/
                    }
                );
            }
        );
    }
);



