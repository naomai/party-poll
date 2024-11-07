<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Poll;
use App\Models\Membership;
use App\Models\Question;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PollSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $polls = Poll::factory()
            ->count(10)
            ->has(Question::factory()->count(5))
            ->create();

        /** @var Poll */
        foreach($polls as $poll) {
            // Membership::factory()
            //     ->count(4)
            //     ->forPoll($poll)
            //     ->create();
            $usersNotAssigned = User::whereNotIn('id', 
                Membership::where('poll_id', '=', $poll->id)
                    ->select('user_id')
            )->get();

            
            $membership = $poll->memberships()->make();
            $membership->user_id= $usersNotAssigned->random()->id;
            $membership->save();
        }
    }
}
