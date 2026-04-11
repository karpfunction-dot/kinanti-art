<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    // Beritahu Laravel nama tabel aslinya
    protected $table = 'kelas';

    // Beritahu Primary Key-nya (karena bukan 'id')
    protected $primaryKey = 'id_kelas';

    // Karena di tabel Anda cuma ada created_at (tidak ada updated_at), kita atur ini:
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null; 

    // Kolom yang boleh diisi
    protected $fillable = ['nama_kelas', 'deskripsi', 'aktif'];
}