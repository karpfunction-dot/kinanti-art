<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('accounting_koreografi')) {
            Schema::create('accounting_koreografi', function (Blueprint $table) {
                $table->id('id_koreografi');
                $table->string('tahun_bulan', 7);
                $table->unsignedBigInteger('id_lagu');
                $table->unsignedBigInteger('id_pelatih');
                $table->decimal('percent_koreo', 5, 2)->default(2.5);
                $table->text('note')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                
                $table->unique(['tahun_bulan', 'id_lagu']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('accounting_koreografi');
    }
};