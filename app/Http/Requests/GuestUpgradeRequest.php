<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class GuestUpgradeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize() {
        return Auth::user()->isGuest;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'name'=>['required', 'string', 'max:60'],
            'email'=>['required', 'email', 'max:100'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }
}
