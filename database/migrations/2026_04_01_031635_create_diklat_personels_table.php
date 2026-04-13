<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diklat_personels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Relasi ke tabel users
            $table->string('tahun');
            $table->string('jenis'); // Diklat, Workshop, Seminar, dll
            
            // Rencana
            $table->string('rencana_diklat');
            $table->string('rencana_penyelenggara');
            $table->string('rencana_jadwal');
            
            // Realisasi (Bisa kosong di awal)
            $table->string('realisasi_diklat')->nullable();
            $table->string('realisasi_penyelenggara')->nullable();
            $table->string('realisasi_jadwal')->nullable();
            
            // Sertifikat & Penilaian
            $table->string('sertifikat_path')->nullable(); // URL File Sertifikat
            $table->string('nomor_sertifikat')->nullable();
            $table->date('tanggal_sertifikat')->nullable();
            $table->date('tanggal_expired')->nullable();
            $table->integer('nilai_cpe')->nullable();
            $table->decimal('biaya', 15, 2)->nullable();
            $table->string('kualifikasi')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diklat_personels');
    }
};