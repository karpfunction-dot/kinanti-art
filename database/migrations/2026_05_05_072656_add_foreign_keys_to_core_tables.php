<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. CLEANUP: Delete orphans before adding constraints to avoid failure
        DB::statement("DELETE FROM profil_anggota WHERE id_user NOT IN (SELECT id_user FROM users)");
        DB::statement("DELETE FROM absensi WHERE id_user NOT IN (SELECT id_user FROM users)");
        DB::statement("DELETE FROM transaksi_spp WHERE id_user NOT IN (SELECT id_user FROM users)");
        DB::statement("DELETE FROM transaksi_tabungan WHERE id_user NOT IN (SELECT id_user FROM users)");
        DB::statement("DELETE FROM transaksi_lainnya WHERE id_user NOT IN (SELECT id_user FROM users)");

        // 2. ADD CONSTRAINTS
        Schema::table('profil_anggota', function (Blueprint $table) {
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });

        Schema::table('absensi', function (Blueprint $table) {
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });

        Schema::table('transaksi_spp', function (Blueprint $table) {
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('restrict');
        });

        Schema::table('transaksi_tabungan', function (Blueprint $table) {
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('restrict');
        });

        Schema::table('transaksi_lainnya', function (Blueprint $table) {
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profil_anggota', function (Blueprint $table) {
            $table->dropForeign(['id_user']);
        });

        Schema::table('absensi', function (Blueprint $table) {
            $table->dropForeign(['id_user']);
        });

        Schema::table('transaksi_spp', function (Blueprint $table) {
            $table->dropForeign(['id_user']);
        });

        Schema::table('transaksi_tabungan', function (Blueprint $table) {
            $table->dropForeign(['id_user']);
        });

        Schema::table('transaksi_lainnya', function (Blueprint $table) {
            $table->dropForeign(['id_user']);
        });
    }
};
