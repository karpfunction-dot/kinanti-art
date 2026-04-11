<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('transaksi_spp')) {
            Schema::create('transaksi_spp', function (Blueprint $table) {
                $table->id('id_transaksi_spp');
                $table->unsignedBigInteger('id_user');
                $table->string('periode', 20);
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
        Schema::dropIfExists('transaksi_spp');
    }
};