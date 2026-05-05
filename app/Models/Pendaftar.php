<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pendaftar extends Model
{
    protected $table = 'pendaftar';

    const UPDATED_AT = null;

    protected $fillable = [
        'nama_lengkap',
        'email',
        'telepon',
        'password',
        'alamat',
        'tanggal_lahir',
        'jenis_kelamin',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];
}