<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_requirements', function (Blueprint $table) {
            $table->id();
            // Jika audit dihapus, syaratnya ikut terhapus (cascade)
            $table->foreignId('audit_id')->constrained('audits')->onDelete('cascade');
            $table->string('jenis_sertifikat'); // Contoh: "ISO 27001", "CISA"
            $table->integer('jumlah_kebutuhan'); // Contoh: butuh 2 orang
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_requirements');
    }
};