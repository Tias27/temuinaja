<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin', function (Blueprint $t) {
            $t->id('id_admin');
            $t->string('nama_admin');
            $t->string('nama_pengguna')->unique();
            $t->string('kata_sandi_hash');
            $t->string('peran')->default('petugas');
            $t->timestamps();
        });
        Schema::create('laporan', function (Blueprint $t) {
            $t->id('id_laporan');
            $t->string('kode_laporan')->unique();
            $t->enum('jenis_laporan', ['ditemukan', 'hilang'])->index();
            $t->string('nama_pelapor');
            $t->json('foto_barang');
            $t->text('deskripsi');
            $t->string('lokasi');
            $t->date('tanggal_kejadian')->index();
            $t->string('status_laporan')->default('menunggu_pemeriksaan')->index();
            $t->text('catatan_admin')->nullable();
            $t->foreignId('id_admin_pemeriksa')
                ->nullable()
                ->constrained('admin', 'id_admin')
                ->nullOnDelete();
            $t->timestamp('dipublikasikan_pada')->nullable()->index();
            $t->timestamps();
        });
        Schema::create('penyerahan_barang', function (Blueprint $t) {
            $t->id('id_penyerahan');
            $t->foreignId('id_laporan')->constrained('laporan', 'id_laporan')->cascadeOnDelete();
            $t->enum('jenis_penyerahan', ['ke_akademik', 'ke_pemilik']);
            $t->string('nama_penyerah');
            $t->string('nama_penerima');
            $t->foreignId('id_admin')->constrained('admin', 'id_admin');
            $t->dateTime('tanggal_penyerahan');
            $t->text('catatan')->nullable();
            $t->timestamps();
        });
        Schema::create('klaim_barang', function (Blueprint $t) {
            $t->id('id_klaim');
            $t->foreignId('id_laporan')->constrained('laporan', 'id_laporan')->cascadeOnDelete();
            $t->string('nama_pengklaim');
            $t->string('kontak_pengklaim')->nullable();
            $t->text('bukti_kepemilikan');
            $t->enum('status_klaim', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $t->foreignId('id_admin_pemeriksa')
                ->nullable()
                ->constrained('admin', 'id_admin')
                ->nullOnDelete();
            $t->text('catatan_admin')->nullable();
            $t->timestamp('diverifikasi_pada')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klaim_barang');
        Schema::dropIfExists('penyerahan_barang');
        Schema::dropIfExists('laporan');
        Schema::dropIfExists('admin');
    }
};
