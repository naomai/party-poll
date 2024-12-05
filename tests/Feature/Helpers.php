<?php

namespace Tests\Feature;

use App\Models\Poll;
use App\Models\Question;
use App\Models\User;

class Helpers {
    public static function createPollWithAdmin(array $props=[]): array {
        if(isset($props['owner_id'])){
            $userId = $props['owner_id'];
            $user = User::find($userId);
        } else{
            $user = User::factory()->create();
        }
        $poll = self::createPollFor($user, $props);
        
        return [$poll, $user];
    }

    public static function createPollFor(User $owner, array $props=[]): Poll {
        $poll = Poll::factory()->create([...$props, 'owner_id'=>$owner->id]);
        return $poll;
    }

    public static function createQuestionsForPoll(Poll $poll): void {
        Question::factory()->count(5)->create(['poll_id'=>$poll->id]);
    }
}
