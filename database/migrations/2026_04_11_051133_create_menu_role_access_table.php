<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('menu_role_access')) {
            Schema::create('menu_role_access', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_menu');
                $table->unsignedBigInteger('id_role');
                $table->timestamps();
                
                $table->foreign('id_menu')->references('id_menu')->on('menu_registry')->onDelete('cascade');
                $table->foreign('id_role')->references('id_role')->on('roles')->onDelete('cascade');
                $table->unique(['id_menu', 'id_role']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('menu_role_access');
    }
};