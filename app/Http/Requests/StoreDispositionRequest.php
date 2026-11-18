<?php

namespace App\Http\Requests;

use App\Actions\Letter\AuthorizeFollowUpAction;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDispositionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return app(AuthorizeFollowUpAction::class)->handle(
            $this->user(),
            $this->input('letter_recipient_id'),
            $this->input('letter_id_primary'),
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string'],
            'letter_id_primary' => ['required', 'integer'],
            'letter_id' => ['required', 'string'],
            'letter_recipient_id' => ['required', 'integer'],
            'letter_receipt_date' => ['required', 'string'],
            'penerima' => ['required', 'array', 'min:1'],
            'penerima.*.id' => ['required', 'integer'],
            'penerima.*.name' => ['required', 'string'],
            'penerima_cc' => ['nullable', 'array'],
            'penerima_cc.*.id' => ['nullable', 'integer'],
            'penerima_cc.*.name' => ['nullable', 'string'],
            'catatan' => ['nullable', 'string'],
            'followup' => ['nullable', 'array'],
            'followup.*.id' => ['nullable', 'integer'],
            'followup.*.name' => ['nullable', 'string'],
            'lampiran' => ['nullable', 'array'],
            'lampiran.*' => ['nullable', 'file', 'mimes:pdf'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Tipe disposisi wajib diisi.',
            'letter_id_primary.required' => 'ID utama surat wajib disertakan.',
            'letter_id_primary.integer' => 'ID utama surat harus berupa angka.',
            'letter_id.required' => 'Kode surat wajib disertakan.',
            'letter_recipient_id.required' => 'ID penerima surat wajib disertakan.',
            'letter_receipt_date.required' => 'Tanggal penerimaan surat wajib disertakan.',
            'penerima.required' => 'Penerima disposisi wajib dipilih minimal satu.',
            'penerima.array' => 'Format penerima disposisi tidak valid.',
            'penerima.min' => 'Penerima disposisi wajib dipilih minimal satu.',
            'penerima.*.id.required' => 'ID penerima tidak valid.',
            'penerima.*.name.required' => 'Nama penerima tidak valid.',
            'penerima_cc.array' => 'Format penerima tembusan tidak valid.',
            'followup.array' => 'Format tindak lanjut tidak valid.',
            'lampiran.array' => 'Format lampiran tidak valid.',
            'lampiran.*.file' => 'Lampiran harus berupa file.',
            'lampiran.*.mimes' => 'Format lampiran hanya diperbolehkan file PDF.',
        ];
    }
}
