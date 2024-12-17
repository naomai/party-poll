<?php

use App\Models\Poll;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('Poll.{poll}', function (User $user, Poll $poll) {
    return $poll->hasMember($user);
});
