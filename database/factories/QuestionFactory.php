<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        $questionType = fake()->randomElement(['range', 'rating', 'select', 'input']);
        
        switch($questionType) {
            case 'range':
                $responseParams = [
                    'min'=>fake()->numberBetween(0, 5),
                    'max'=>fake()->numberBetween(6, 100),
                ];
                break;
            case 'select':
                $answersCount = fake()->numberBetween(2, 5);
                $responseParams = [
                    'options'=>[],
                    'max_selected'=>fake()->numberBetween(0, $answersCount),
                ];
                for($i=0; $i<$answersCount; $i++){
                    $responseParams['options'][]=[
                        'caption'=>fake()->sentence()
                    ];
                }
                break;
            case 'input':
                $responseParams = [
                    'type'=>fake()->randomElement(['text', 'pass', 'longtext']),
                    'max_length'=>fake()->numberBetween(0, 50),
                ];
                break;
            default:
                $responseParams = [];
                break;
        }

        $seqId = Question::max('poll_sequence_id') + 1;

        return [
            'owner_id'=>User::all()->random()->id,
            'question'=>fake()->sentence(),
            'type' =>               $questionType,
            'response_params' =>    $responseParams,
        ];
    }
}
