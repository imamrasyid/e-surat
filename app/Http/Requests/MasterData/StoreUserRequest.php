<?php

namespace App\Http\Requests\MasterData;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'lowercase', 'max:255', 'unique:'.User::class],
            'position_id' => ['required', 'integer', 'exists:positions,id'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
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
            'name.required' => 'Nama pengguna wajib diisi.',
            'name.string' => 'Nama pengguna harus berupa teks.',
            'name.max' => 'Nama pengguna maksimal 255 karakter.',
            'username.required' => 'Username wajib diisi.',
            'username.string' => 'Username harus berupa teks.',
            'username.lowercase' => 'Username harus berupa huruf kecil.',
            'username.max' => 'Username maksimal 255 karakter.',
            'username.unique' => 'Username sudah digunakan.',
            'position_id.required' => 'Jabatan wajib dipilih.',
            'position_id.integer' => 'Jabatan tidak valid.',
            'position_id.exists' => 'Jabatan tidak ditemukan.',
            'role_id.required' => 'Role wajib dipilih.',
            'role_id.integer' => 'Role tidak valid.',
            'role_id.exists' => 'Role tidak ditemukan.',
        ];
    }
}
