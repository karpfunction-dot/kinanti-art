<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LaguRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->id_role === 1;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'judul_lagu'        => 'required|string|max:150|unique:lagu,judul_lagu,' . $id . ',id_lagu',
            'pencipta'          => 'nullable|string|max:100',
            'lisensi'           => 'required|in:gratis,berbayar',
            'status_lisensi'    => 'required|in:bebas,izin,internal',
            'status'            => 'required|in:aktif,nonaktif',
            'status_penggunaan' => 'required|in:latihan,lomba,arsip',
            'id_kelas'          => 'nullable|exists:kelas,id_kelas',
            'link_lisensi'      => 'nullable|url|max:255',
        ];
    }
}