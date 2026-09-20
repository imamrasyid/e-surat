<?php

namespace App\Http\Requests\Settings;

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->profileRules($this->user()->id);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your name.',
            'name.string' => 'Please enter a valid name.',
            'name.max' => 'Your name cannot exceed 255 characters.',
            'username.required' => 'Please enter your username.',
            'username.min' => 'Your username must be at least 3 characters long.',
            'username.max' => 'Your username cannot exceed 50 characters.',
            'username.unique' => 'Your username is already taken.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'Your email is already taken.',
            'email.max' => 'Your email cannot exceed 255 characters.',
        ];
    }
}
