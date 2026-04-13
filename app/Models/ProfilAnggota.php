<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilAnggota extends Model
{
    // Nama tabel di database lama
    protected $table = 'profil_anggota';
    
    // Primary Key (Biasanya id_profil atau id_user, kita set id_user agar aman relasinya)
    protected $primaryKey = 'id_user';
    
    public $timestamps = false;

    protected $fillable = ['id_user', 'nama_lengkap', 'foto_profil', 'no_hp'];
}