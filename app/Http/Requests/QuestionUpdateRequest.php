<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuestionUpdateRequest extends FormRequest {
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'question' => ['string', 'max:255'],
            'poll_sequence_id' => ['integer'],
            'type' => ['required', Rule::in(['input','range','rating','select'])],
            'response_params' => ['array', 'nullable'],
            
            'response_params.type' => ['exclude_unless:type,input','required', Rule::in(['text','pass','longtext'])],
            'response_params.max_length' => ['exclude_unless:type,input','required', 'integer', "between: 0, 1000"],
            
            'response_params.min' => ['exclude_unless:type,range','required', 'integer'],
            'response_params.max' => ['exclude_unless:type,range','required', 'integer', "gt:response_params.min"],

            'response_params.options' => ['exclude_unless:type,select',"required", "array", "between:1,20"],
            'response_params.options.*.caption' => ['exclude_unless:type,select',"present", "string", "between:0,500"],
            'response_params.max_selected' => ['exclude_unless:type,select','required', 'integer', "between: 0, 20"],

        ];
    }
}
