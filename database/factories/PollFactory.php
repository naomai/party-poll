<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Poll>
 */
class PollFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'owner_id'=>User::all()->random()->id,
            'title'=>fake()->sentence(),
            'enable_link_invite'=>fake()->boolean(),
            'close_after_start'=>fake()->boolean(),
            'wait_for_everybody'=>fake()->boolean(),
        ];
    }
}
