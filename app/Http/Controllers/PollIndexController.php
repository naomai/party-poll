<?php

namespace App\Http\Controllers;

use App\Services\PollManagementService;
use Inertia\Inertia;

class PollIndexController extends Controller {
    public function view(PollManagementService $manage) {
        $pollList = $manage->index();
        return Inertia::render("PollIndex/Index", [
            'polls' => $pollList,
        ]);
    }

}
