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

    public static function getQuestionStats(Question $question): array {
        $stats = ['options'=>[]];
        $answers = $question->answers->all();

        switch($question->type) {
            case "text":
                $stats['type'] = 'list';
                $stats['options'] = array_map(
                    fn($a) => $a->response->input,
                    $answers,
                );
                break;
            case 'select':
                $stats['type'] = 'options';
                $stats['wide'] = false;
                $options = $question->response_params->options;
                
                // create template for options array - [text, votecount]
                $optionsStatsEmpty = array_map(
                    fn($o) => [$o->caption, 0], 
                    $options
                );
                
                // lay selections (checkboxes selected) by all users in one flat array
                $allSelections = array_reduce(
                    $answers,
                    fn($cumulative, $answer) => array_merge($cumulative, $answer->response->selected),
                    []
                );
                
                // fill options array
                $stats['options'] = array_reduce(
                    $allSelections, 
                    function($stat, $item) {
                        $stat[$item][1]++;
                        return $stat;
                    },
                    $optionsStatsEmpty
                );
                break;
            case 'rating':
            case 'range':
                $stats['type'] = 'options';
                $stats['wide'] = true;

                if($question->type=='rating') {
                    // TODO - replace magic values
                    $min = 0.5;
                    $max = 5;
                    $step = 0.5;
                } else{
                    $min = $question->response_params->min;
                    $max = $question->response_params->max;
                    $step = 1;
                }

                // create template for options array - [text, votecount]
                $optionsStatsEmpty = array_fill(0, ceil(($max-$min) / $step + 1), [null, 0]);
                array_walk($optionsStatsEmpty, function(&$o, $i) use ($min, $step) {
                    $o[0] = (string)($min + $i * $step);
                });

                // fill options array
                $stats['options'] = array_reduce(
                    $answers, 
                    function($stat, $answer) {
                        $item = $answer->response->input;
                        $stat[$item][1]++;
                        return $stat;
                    },
                    $optionsStatsEmpty
                );
                break;

        }
        return $stats;
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