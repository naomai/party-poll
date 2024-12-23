<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PollInfoUpdateRequest;
use App\Models\Poll;
use App\Services\PollManagementService;
use Illuminate\Http\JsonResponse;

class PollManagementController extends Controller {
    public function index(PollManagementService $manage): JsonResponse {
        $index = $manage->index();
        return response()->json($index);   
    }

    public function store(
        PollInfoUpdateRequest $request, 
        PollManagementService $manage
    ) : JsonResponse {

        $pollSummary = $manage->store($request->validated());
        return response()->json($pollSummary);
    }
    
    public function show(
        Poll $poll, 
        PollManagementService $manage
    ): JsonResponse {

        $pollSummary = $manage->show($poll);
        return response()->json($pollSummary);
    }

    public function update(
        Poll $poll, 
        PollInfoUpdateRequest $request, 
        PollManagementService $manage
    ): JsonResponse {
        
        $pollSummary = $manage->update($poll, $request->validated());
        return response()->json($pollSummary);
    }
    
}
