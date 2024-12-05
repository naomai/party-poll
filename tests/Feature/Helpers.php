<?php

namespace Tests\Feature;

use App\Models\Poll;
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
}
