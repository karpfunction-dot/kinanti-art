<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // Sesuai SQL Dump
    protected $table = 'roles';
    protected $primaryKey = 'id_role';
    
    // Di dump ada created_at, tapi tidak ada updated_at default
    // Kita matikan timestamps otomatis agar aman
    public $timestamps = false;

    protected $fillable = ['nama_role', 'deskripsi', 'aktif'];
}