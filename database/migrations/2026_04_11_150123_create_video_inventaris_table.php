<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('video_inventaris', function (Blueprint $table) {
            $table->id('id_video');
            $table->string('judul', 200);
            $table->text('deskripsi')->nullable();
            $table->unsignedBigInteger('id_lagu')->nullable(); // untuk koreografi
            $table->enum('tipe', ['upload', 'youtube', 'vimeo', 'googledrive', 'other'])->default('upload');
            $table->string('url_embed', 500)->nullable(); // URL untuk embed
            $table->string('youtube_id', 50)->nullable(); // ID YouTube (contoh: dQw4w9WgXcQ)
            $table->string('file_path', 500)->nullable(); // untuk upload ke server
            $table->string('thumbnail', 500)->nullable(); // thumbnail
            $table->integer('durasi')->nullable(); // durasi dalam detik
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('video_inventaris');
    }
};