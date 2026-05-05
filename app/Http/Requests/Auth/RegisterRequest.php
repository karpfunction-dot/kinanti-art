<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_lengkap'  => 'required|string|max:100',
            'email'         => 'required|email|unique:pendaftar,email',
            'telepon'       => 'nullable|string|max:20',
            'password'      => 'required|string|min:6|confirmed',
            'alamat'        => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
        ];
    }
}