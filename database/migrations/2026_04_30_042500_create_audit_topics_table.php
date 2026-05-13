<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_topics', function (Blueprint $table) {
            $table->id();
            $table->string('unit_kerja');
            $table->string('name');
            $table->text('kriteria')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_topics');
    }
};
