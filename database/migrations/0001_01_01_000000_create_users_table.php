<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Users (Sudah Ditambah Kolom Security)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('jabatan')->nullable();
            $table->string('instansi')->nullable();
            $table->string('unit_kerja')->nullable();
            $table->string('np')->nullable();
            $table->string('status_kepegawaian')->nullable();
            $table->boolean('status_keaktifan')->default(true);
            $table->string('role')->default('User');
            $table->json('preferences')->nullable();
            
            // --- KOLOM SECURITY BARU ---
            $table->boolean('is_first_login')->default(true); // Wajib ganti password kalau true
            $table->timestamp('password_changed_at')->nullable(); // Untuk cek masa berlaku 90 hari
            $table->integer('failed_login_attempts')->default(0); // Hitung salah password (maks 3)
            $table->boolean('is_locked')->default(false); // Status akun diblokir atau tidak
            // ---------------------------

            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. Tabel Password Histories (Untuk simpan 3 password terakhir)
        Schema::create('password_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('password'); // Password yang pernah dipakai (dihash)
            $table->timestamps();
        });

        // 3. Tabel Password Reset & Sessions (Bawaan Laravel)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_histories');
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};