<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiSpp extends Model
{
    protected $table = 'transaksi_spp';
    protected $primaryKey = 'id_transaksi_spp';
    
    protected $fillable = [
        'id_user',
        'periode',
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
