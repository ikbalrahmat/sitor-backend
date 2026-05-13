<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_topic_id')->constrained('audit_topics')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('Perlu Penguatan'); // 'Layak Ditugaskan', 'Perlu Penguatan', 'Tidak Direkomendasikan'
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            // Ensures a user only has one evaluation per topic
            $table->unique(['audit_topic_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_evaluations');
    }
};
