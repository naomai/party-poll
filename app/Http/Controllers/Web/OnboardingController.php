<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class OnboardingController extends Controller {
    public function view(): InertiaResponse|RedirectResponse {
        if(Auth::check()) {
            return redirect(route('polls.index'));
        }
        
        return Inertia::render('Auth/CreateGuestUsername');
    }
}
