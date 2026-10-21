<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi ganti password (dipakai ProfileController@updatePassword).
 * Selalu memvalidasi password milik user yang sedang login ($this->user()).
 */
class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'password_lama' => ['required', 'current_password'],
            'password'      => ['required', 'string', 'min:8', 'confirmed', 'different:password_lama'],
        ];
    }

    public function messages(): array
    {
        return [
            'password_lama.required'         => 'Password lama wajib diisi.',
            'password_lama.current_password' => 'Password lama tidak sesuai.',
            'password.required'              => 'Password baru wajib diisi.',
            'password.min'                   => 'Password baru minimal 8 karakter.',
            'password.confirmed'             => 'Konfirmasi password baru tidak cocok.',
            'password.different'             => 'Password baru tidak boleh sama dengan password lama.',
        ];
    }
}