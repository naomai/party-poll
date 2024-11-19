<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\PollBasicInfoResource;
use App\Models\Poll;
use App\Services\MembershipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class OnboardingController extends Controller {
    public function view(MembershipService $svc): InertiaResponse|RedirectResponse {
        if(Auth::check()) {
            return redirect(route('polls.index'));
        }

        $invitation = null;
        if(session()->exists('invite_poll')) {
            $token = session()->get('invite_token');
            $pollId = session()->get('invite_poll');
            $poll = Poll::find($pollId);
            
            $validInvitation = $svc->checkInvitation($poll, $token);
            if($validInvitation) {
                $invitation = [
                    'poll' => new PollBasicInfoResource($poll),
                ];
            }
        }
        
        return Inertia::render('Auth/CreateGuestUsername', ['invitation'=> $invitation]);
    }
}
