<?php

namespace App\Http\Requests\Transaksi;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Middleware handles this
    }

    public function rules(): array
    {
        $rules = [
            'jenis' => 'required|in:SPP,Tabungan,Lainnya',
            'id_user' => 'required|exists:users,id_user',
            'total' => 'required|numeric|min:0',
            'tanggal_pembayaran' => 'required|date',
            'keterangan' => 'nullable|string',
        ];

        if ($this->jenis === 'SPP') {
            $rules['bulan'] = 'required|string';
            $rules['tahun'] = 'required|numeric';
        }

        if ($this->jenis === 'Tabungan') {
            $rules['jenis_tabungan'] = 'required|in:Setor,Tarik';
        }

        if ($this->jenis === 'Lainnya') {
            $rules['kategori'] = 'required|string';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'id_user.exists' => 'User tidak ditemukan dalam sistem.',
            'total.numeric' => 'Total harus berupa angka.',
            'tanggal_pembayaran.date' => 'Format tanggal tidak valid.',
        ];
    }
}
