<?php

namespace App\Policies;

use App\Models\Poll;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;

class PollPolicy {
    
    public function index(User $user): bool {
        return true;
    }
    
    public function view(User $user, Poll $poll): bool {
        return $poll->hasParticipant($user);
    }
    
    public function store(User $user): bool {
        return true;
    }
    
    public function update(User $user, Poll $poll): bool {
        $participation = $poll->getUserParticipation($user);
        return $participation->can_modify_poll;
    }
    
    public function delete(User $user, Poll $poll): bool {
        $participation = $poll->getUserParticipation($user);
        return $participation->can_modify_poll;
    }
    
    public function restore(User $user, Poll $poll): bool {
        return false;
    }
    
    public function forceDelete(User $user, Poll $poll): bool {
        return false;
    }

    public function answer(User $user, Poll $poll): bool {
        return true;
        $participation = $poll->getUserParticipation($user);
        $pollStarted = $poll->sequence_id !== null;
        return $participation->can_answer || $pollStarted;
    }

    public function listQuestions(User $user, Poll $poll): bool {
        return true;
        $participation = $poll->getUserParticipation($user);
        $canSeeAll = 
            $participation->can_control_flow ||
            $participation->can_see_progress ||
            $participation->can_modify_poll;
        $pollStarted = $poll->sequence_id !== null;
        return $canSeeAll || $pollStarted;
    }

    public function controlFlow(User $user, Poll $poll): bool {
        $participation = $poll->getUserParticipation($user);
        return $participation->can_control_flow;
    }

    public function seeProgress(User $user, Poll $poll): bool {
        $participation = $poll->getUserParticipation($user);
        return $participation->can_see_progress;
    }
}
