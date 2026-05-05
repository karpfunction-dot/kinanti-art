<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiLainnya extends Model
{
    protected $table = 'transaksi_lainnya';
    protected $primaryKey = 'id_transaksi_lainnya';
    
    protected $fillable = [
        'id_user',
        'kategori',
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
