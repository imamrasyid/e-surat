<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSentLetterRequest extends FormRequest
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
            'sender' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'receipt_date' => ['required', 'date'],
            'category' => ['required', 'in:1,2'],
            'subject' => ['required', 'string', 'max:1000'],
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
            'sender.required' => 'Pengirim surat wajib diisi.',
            'number.required' => 'Nomor surat wajib diisi.',
            'date.required' => 'Tanggal surat wajib diisi.',
            'date.date' => 'Format tanggal surat tidak valid.',
            'receipt_date.required' => 'Tanggal penerimaan surat wajib diisi.',
            'receipt_date.date' => 'Format tanggal penerimaan surat tidak valid.',
            'category.required' => 'Kategori surat wajib dipilih.',
            'category.in' => 'Kategori surat yang dipilih tidak valid.',
            'subject.required' => 'Perihal surat wajib diisi.',
        ];
    }
}
