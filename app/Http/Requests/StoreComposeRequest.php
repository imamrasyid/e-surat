<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComposeRequest extends FormRequest
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
            'pengirim' => ['required', 'string', 'max:150'],
            'nomor' => ['nullable', 'string', 'max:100'],
            'tglSuratStr' => ['required', 'date_format:Y-m-d'],
            'tglDiterimaStr' => ['required', 'date_format:Y-m-d'],
            'perihal' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'digits:1'],
            'security_classification' => ['required', 'in:SR,R,T,B'],
            'received_via' => ['required', 'in:email,physical,internal,other'],
            'agenda' => ['required', 'exists:ref_kode_klasifikasi_arsip,kode'],
            'agendaTahun' => ['required', 'digits:4'],
            'penerima' => ['required', 'array', 'min:1'],
            'penerima.*.id' => ['required', 'integer', Rule::exists('positions', 'id')->where('is_active', true)],
            'penerima.*.name' => ['required', 'string'],
            'catatan' => ['nullable', 'string'],
            'lampiran' => ['required', 'array', 'min:1'],
            'lampiran.*' => ['required', 'file', 'mimes:pdf'],
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
            'pengirim.required' => 'Pengirim surat wajib diisi.',
            'pengirim.string' => 'Pengirim surat harus berupa teks.',
            'pengirim.max' => 'Pengirim surat maksimal 150 karakter.',
            'nomor.string' => 'Nomor surat harus berupa teks.',
            'nomor.max' => 'Nomor surat maksimal 100 karakter.',
            'tglSuratStr.required' => 'Tanggal surat wajib diisi.',
            'tglSuratStr.date_format' => 'Format tanggal surat tidak valid (YYYY-MM-DD).',
            'tglDiterimaStr.required' => 'Tanggal terima surat wajib diisi.',
            'tglDiterimaStr.date_format' => 'Format tanggal terima surat tidak valid (YYYY-MM-DD).',
            'perihal.required' => 'Perihal surat wajib diisi.',
            'perihal.string' => 'Perihal surat harus berupa teks.',
            'perihal.max' => 'Perihal surat maksimal 255 karakter.',
            'kategori.required' => 'Kategori surat wajib dipilih.',
            'kategori.digits' => 'Kategori surat tidak valid.',
            'security_classification.required' => 'Sifat/klasifikasi keamanan wajib dipilih.',
            'security_classification.in' => 'Sifat/klasifikasi keamanan tidak valid.',
            'received_via.required' => 'Sumber penerimaan surat wajib dipilih.',
            'received_via.in' => 'Sumber penerimaan surat tidak valid.',
            'agenda.required' => 'Kode klasifikasi agenda wajib dipilih.',
            'agenda.exists' => 'Kode klasifikasi agenda yang dipilih tidak valid.',
            'agendaTahun.required' => 'Tahun agenda wajib diisi.',
            'agendaTahun.digits' => 'Tahun agenda harus 4 digit angka.',
            'penerima.required' => 'Penerima surat wajib dipilih minimal satu.',
            'penerima.array' => 'Data penerima surat harus berupa array.',
            'penerima.min' => 'Penerima surat minimal 1 jabatan.',
            'penerima.*.id.required' => 'ID penerima wajib disertakan.',
            'penerima.*.id.integer' => 'ID penerima harus berupa angka.',
            'penerima.*.id.exists' => 'Jabatan penerima tidak ditemukan atau tidak aktif.',
            'penerima.*.name.required' => 'Nama jabatan penerima wajib disertakan.',
            'lampiran.required' => 'Lampiran file surat wajib diunggah minimal satu file.',
            'lampiran.array' => 'Daftar lampiran harus berupa array file.',
            'lampiran.min' => 'Wajib melampirkan minimal 1 file.',
            'lampiran.*.required' => 'File lampiran wajib diunggah.',
            'lampiran.*.file' => 'Lampiran harus berupa file yang valid.',
            'lampiran.*.mimes' => 'Format lampiran harus berupa file PDF.',
        ];
    }
}
