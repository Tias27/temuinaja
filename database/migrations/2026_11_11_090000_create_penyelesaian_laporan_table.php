<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyelesaian_laporan', function (Blueprint $table) {
            $table->id('id_penyelesaian');
            $table->foreignId('id_laporan')->unique()->constrained('laporan', 'id_laporan')->cascadeOnDelete();
            $table->string('jenis_penyelesaian');
            $table->string('nama_pihak', 120);
            $table->string('kontak_pihak', 20);
            $table->string('lokasi_ditemukan', 180)->nullable();
            $table->text('catatan_verifikasi')->nullable();
            $table->foreignId('id_admin')->constrained('admin', 'id_admin');
            $table->timestamp('diselesaikan_pada');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyelesaian_laporan');
    }
};
