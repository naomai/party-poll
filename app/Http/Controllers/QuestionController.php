<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function get(Question $question): JsonResponse {
        return response()->json($question);
    }
}
