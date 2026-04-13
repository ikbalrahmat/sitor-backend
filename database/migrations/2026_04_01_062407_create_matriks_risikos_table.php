<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matriks_risikos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Relasi ke tabel Users
            
            // Level Risiko (Rendah, Sedang, Tinggi)
            $table->string('ops_ti')->default('Rendah');
            $table->string('keuangan_fraud')->default('Rendah');
            $table->string('kepatuhan')->default('Rendah');
            
            // Catatan Manual
            $table->text('catatan_ops_ti')->nullable();
            $table->text('catatan_keuangan_fraud')->nullable();
            $table->text('catatan_kepatuhan')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matriks_risikos');
    }
};