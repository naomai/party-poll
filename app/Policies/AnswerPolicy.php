<?php

namespace App\Policies;

use App\Models\Answer;
use App\Models\Poll;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AnswerPolicy {

    public function index(User $user): bool {
        return true;
    }

    public function view(User $user, Answer $answer): bool {
        return $answer->user_id == $user->id;
    }

    public function create(User $user, Poll $poll): bool {

    }

    public function update(User $user, Answer $answer): bool {
        //
    }

    public function delete(User $user, Answer $answer): bool {
        //
    }


    public function restore(User $user, Answer $answer): bool {
        //
    }


    public function forceDelete(User $user, Answer $answer): bool {
        //
    }
}
