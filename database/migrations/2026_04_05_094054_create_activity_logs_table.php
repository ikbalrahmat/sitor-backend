<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            // user_id dibuat nullable karena jika login gagal (email tidak ditemukan), kita belum tahu ID-nya
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('email')->nullable(); // Untuk mencatat percobaan login
            $table->string('event_type'); // Contoh: 'LOGIN_SUCCESS', 'LOGIN_FAILED', 'DATA_MODIFICATION'
            $table->text('description'); // Penjelasan event
            $table->string('ip_address')->nullable(); // Requirement 9: Network address
            $table->text('user_agent')->nullable(); // Requirement 9: Device identity
            $table->timestamps(); // Otomatis memenuhi Requirement 9: Date and Time Stamp
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};