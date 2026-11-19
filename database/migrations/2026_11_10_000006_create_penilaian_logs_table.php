<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('penilaian_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penilaian_id')->constrained('penilaian')->cascadeOnDelete();
            $table->foreignId('juri_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('skor_visual_lama', 5, 2)->nullable();
            $table->decimal('skor_visual_baru', 5, 2);
            $table->decimal('skor_audio_lama', 5, 2)->nullable();
            $table->decimal('skor_audio_baru', 5, 2);
            $table->string('alasan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian_logs');
    }
};
