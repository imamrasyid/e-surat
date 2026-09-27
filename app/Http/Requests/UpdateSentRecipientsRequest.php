<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSentRecipientsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'recipients' => ['required', 'array', 'min:1'],
            'recipients.*.id' => ['nullable', 'integer', 'exists:letter_recipients,id'],
            'recipients.*.to_position_id' => ['required', 'integer', 'exists:positions,id'],
            'recipients.*.instruction' => ['nullable', 'string', 'max:500'],
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
            'recipients.required' => 'Penerima surat wajib dipilih minimal satu.',
            'recipients.array' => 'Format penerima surat tidak valid.',
            'recipients.min' => 'Penerima surat wajib dipilih minimal satu.',
            'recipients.*.to_position_id.required' => 'Jabatan penerima wajib dipilih.',
            'recipients.*.to_position_id.exists' => 'Jabatan penerima yang dipilih tidak valid.',
            'recipients.*.instruction.max' => 'Instruksi maksimal 500 karakter.',
        ];
    }
}
