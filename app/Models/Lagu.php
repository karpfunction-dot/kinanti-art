<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lagu extends Model
{
    protected $table = 'lagu';
    protected $primaryKey = 'id_lagu';

    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'judul_lagu',
        'pencipta',
        'lisensi',
        'status_lisensi',
        'status',
        'status_penggunaan',
        'id_kelas',
        'link_lisensi',
    ];

    /**
     * Relasi ke Kelas
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    /**
     * Relasi ke VideoInventaris
     */
    public function videoInventaris()
    {
        return $this->hasMany(VideoInventaris::class, 'id_lagu', 'id_lagu');
    }

    /**
     * Relasi ke Jadwal
     */
    public function jadwal()
    {
        return $this->hasMany(Jadwal::class, 'id_lagu', 'id_lagu');
    }

    /**
     * Relasi ke Koreografi
     */
    public function koreografi()
    {
        return $this->hasOne(Koreografi::class, 'id_lagu', 'id_lagu');
    }
}
