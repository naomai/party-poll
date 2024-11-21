<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuestionStoreRequest extends FormRequest {
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'question' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['input','range','rating','select'])],
            'response_params' => ['required', 'array'],

        ];
    }
}
