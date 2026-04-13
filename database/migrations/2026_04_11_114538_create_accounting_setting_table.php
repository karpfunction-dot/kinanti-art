<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('accounting_setting')) {
            Schema::create('accounting_setting', function (Blueprint $table) {
                $table->id('id_setting');
                $table->string('tahun_bulan', 7);
                $table->decimal('omset_manual', 15, 2)->default(0);
                $table->decimal('operasional_manual', 15, 2)->default(0);
                $table->decimal('pelatih_percent', 5, 2)->default(10.0);
                $table->decimal('admin_percent', 5, 2)->default(10.0);
                $table->decimal('manajemen_keuangan_percent', 5, 2)->default(10.0);
                $table->decimal('manajemen_sapras_percent', 5, 2)->default(10.0);
                $table->decimal('koreo_default_percent', 5, 2)->default(2.5);
                $table->decimal('transport_nominal', 15, 2)->default(0);
                $table->integer('max_pertemuan')->default(9);
                $table->timestamps();
                
                $table->unique('tahun_bulan');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('accounting_setting');
    }
};