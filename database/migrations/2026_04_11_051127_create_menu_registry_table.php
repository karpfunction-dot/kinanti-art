<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('menu_registry')) {
            Schema::create('menu_registry', function (Blueprint $table) {
                $table->id('id_menu');
                $table->unsignedBigInteger('id_parent')->nullable();
                $table->string('label', 100);
                $table->string('icon', 50)->nullable();
                $table->string('page', 200)->nullable();
                $table->integer('order_index')->default(0);
                $table->boolean('aktif')->default(true);
                $table->timestamps();
                
                $table->foreign('id_parent')->references('id_menu')->on('menu_registry')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('menu_registry');
    }
};