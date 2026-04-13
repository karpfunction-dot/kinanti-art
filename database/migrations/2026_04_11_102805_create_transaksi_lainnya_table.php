<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('transaksi_lainnya')) {
            Schema::create('transaksi_lainnya', function (Blueprint $table) {
                $table->id('id_transaksi_lainnya');
                $table->unsignedBigInteger('id_user');
                $table->string('kategori', 100);
                $table->date('tanggal_pembayaran');
                $table->date('tanggal_rekap')->nullable();
                $table->decimal('total', 15, 2);
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('transaksi_lainnya');
    }
};