<?php

namespace Database\Factories;

use App\Models\Poll;
use App\Models\PollParticipant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PollParticipant>
 */
class PollParticipantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'poll_id'=>Poll::all()->random()->id,
            'user_id' => function(array $attr)  {
                $usersNotAssigned = User::whereNotIn('id', 
                    PollParticipant::where('poll_id', '=', $attr['poll_id'])
                        ->select('user_id')
                )->get();
                return $usersNotAssigned->random()->id;
            }
            
        ];
    }

    public function forPoll(Poll $poll) {
        return $this->state([
            'poll_id' => $poll->id,
        ]);
    }
}
