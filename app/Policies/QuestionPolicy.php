<?php

namespace App\Policies;

use App\Models\Poll;
use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;

class QuestionPolicy {
    public function view(User $user, Question $question): bool {
        return $question->poll->hasMember($user);
    }

    public function create(User $user, Poll $poll): bool {
        $membership = $poll->getMembership($user);
        return $membership->can_modify_poll;
    }

    public function update(User $user, Question $question): bool {
        $membership = $question->poll->getMembership($user);
        return $membership->can_modify_poll;
    }

    public function delete(User $user, Question $question): bool {
        $membership = $question->poll->getMembership($user);
        return $membership->can_modify_poll;
    }

    public function restore(User $user, Question $question): bool {
        //
    }

    public function forceDelete(User $user, Question $question): bool {
        //
    }
}
