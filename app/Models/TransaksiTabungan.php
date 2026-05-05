<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiTabungan extends Model
{
    protected $table = 'transaksi_tabungan';
    protected $primaryKey = 'id_transaksi_tabungan';
    
    protected $fillable = [
        'id_user',
        'jenis',
        'tanggal_pembayaran',
        'tanggal_rekap',
        'total',
        'keterangan'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
