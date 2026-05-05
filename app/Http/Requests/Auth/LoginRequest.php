<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_barcode' => 'required|string',
            'password'     => 'required|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'kode_barcode.required' => 'Kode barcode harus diisi',
            'password.required'     => 'Password harus diisi',
            'password.min'          => 'Password minimal 6 karakter',
        ];
    }
}