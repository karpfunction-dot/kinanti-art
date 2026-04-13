<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    // Konfigurasi Tabel
    protected $table = 'users';
    protected $primaryKey = 'id_user';
    public $timestamps = false; // Matikan timestamps otomatis

    // Kolom yang boleh diisi
    protected $fillable = [
        'kode_barcode', 
        'password', 
        'id_role', 
        'aktif', 
        'last_login'
    ];

    // Sembunyikan password
    protected $hidden = [
        'password',
    ];

    // --- RELASI (HUBUNGAN ANTAR TABEL) ---

    // 1. Relasi ke Jabatan (Role)
    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    // 2. Relasi ke Profil Anggota (Untuk ambil Nama Lengkap & Foto)
    public function profil()
    {
        // User memiliki satu Profil (One to One)
        // Pastikan Anda sudah membuat model 'ProfilAnggota.php' juga
        return $this->hasOne(ProfilAnggota::class, 'id_user', 'id_user');
    }
}