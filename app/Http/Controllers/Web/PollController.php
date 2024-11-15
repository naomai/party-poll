<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\PollInfoUpdateRequest;
use App\Models\Poll;
use App\Services\PollManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PollController extends Controller {
    public function index(PollManagementService $manage): InertiaResponse {
        $pollList = $manage->index();
        return Inertia::render("PollIndex/Index", [
            'polls' => $pollList,
        ]);
    }

    public function store(
        PollInfoUpdateRequest $request, 
        PollManagementService $manage
    ) : RedirectResponse {

        $pollSummary = $manage->store($request->validated());
       
        return to_route("polls.show", [
            'poll'=>$pollSummary->id
        ], 303);
    }
    
    public function show(
        Poll $poll, 
        PollManagementService $manage
    ): InertiaResponse {

        $pollDetails = $manage->show($poll);
        return Inertia::render("Poll/Summary", $pollDetails);
    }

    public function update(
        Poll $poll, 
        PollInfoUpdateRequest $request, 
        PollManagementService $manage
    ): Response  {
        
        $pollSummary = $manage->update($poll, $request->validated());
        return response()->noContent();
    }
    
}
