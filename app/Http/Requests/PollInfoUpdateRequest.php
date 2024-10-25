<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PollInfoUpdateRequest extends FormRequest {
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'title' => ['required', 'string', 'max:255'],
            'enable_link_invite' => ['boolean'],
            'close_after_start' => ['boolean'],
            'wait_for_everybody' => ['boolean'],
            'enable_revise_response' => ['boolean'],
            'show_question_results' => ['boolean'],
            'show_question_answers' => ['boolean'],
            'show_end_results' => ['boolean'],
            'show_end_answers' => ['boolean'],

        ];
    }
}
