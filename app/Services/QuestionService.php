<?php
namespace App\Services;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class QuestionService {
    public function storeAnswer(Request $request, Question $question) {
        Gate::authorize('view', $question);
        Gate::authorize('answer', $question->poll);

        $validated = $request->validate(static::getValidatorForQuestion($question));

        $answerRecord = [
            'question_id' => $question->id,
            'user_id' => Auth::user()->id,
            'response' => json_encode($validated),
        ];
        Answer::upsert(
            [$answerRecord], 
            uniqueBy: ['question_id', 'user_id'], 
            update: ['response']
        );

    }

    private static function getValidatorForQuestion(Question $question): Array {

       
        $params = $question->response_params;
        switch($question->type) {
            case 'text':
                $maxLen = (int)$params->max_length;
                return ['answer.input'=>["required", "max:{$maxLen}"]];
            case 'range':
                $min = (int)$params->min;
                $max = (int)$params->max;
                return ['answer.input'=>["required", "integer", "gte:{$min}", "lte:{$max}"]];
            case 'rating':
                return ['answer.input'=>["required", "numeric", "gte:0.5", "lte:5"]];
            case 'select':
                $maxSelected = $params->max_selected;
                $maxAnswerId = count($params->options) - 1;
                if($maxSelected == 0) {
                    $maxSelected = count($params->options);
                }
                
                return [
                    'answer.selected'=>["required", "array", "between:1,{$maxSelected}"],
                    'answer.selected.*'=>["required", "integer", "between:0,{$maxAnswerId}"],
                ];
        }
    }
}