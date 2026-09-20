<?php

namespace App\Http\Requests\Settings;

use App\Concerns\PasswordValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PasswordUpdateRequest extends FormRequest
{
    use PasswordValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => $this->currentPasswordRules(),
            'password' => $this->passwordRules(),
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Please enter your current password.',
            'current_password.current_password' => 'Your current password is incorrect.',
            'password.required' => 'Please enter a new password.',
            'password.confirmed' => 'Your new password does not match the confirmation password.',
            'password.min' => 'Your new password must be at least 8 characters long.',
            'password.max' => 'Your new password cannot exceed 255 characters.',
            'password.letters' => 'Your new password must contain at least one letter.',
            'password.numbers' => 'Your new password must contain at least one number.',
            'password.symbols' => 'Your new password must contain at least one symbol.',
        ];
    }
}
