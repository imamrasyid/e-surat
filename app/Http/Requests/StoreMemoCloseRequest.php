<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMemoCloseRequest extends FormRequest
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
            'letter_id_primary' => ['required', 'integer'],
            'letter_recipient_id' => ['required', 'integer'],
            'to_position_id' => ['required', 'integer'],
            'catatan' => ['nullable', 'string'],
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
            'letter_id_primary.required' => 'ID utama surat wajib disertakan.',
            'letter_id_primary.integer' => 'ID utama surat harus berupa angka.',
            'letter_recipient_id.required' => 'ID penerima surat wajib disertakan.',
            'letter_recipient_id.integer' => 'ID penerima surat harus berupa angka.',
            'to_position_id.required' => 'Jabatan tujuan penutupan surat wajib dipilih.',
            'to_position_id.integer' => 'Jabatan tujuan penutupan surat tidak valid.',
        ];
    }
}
