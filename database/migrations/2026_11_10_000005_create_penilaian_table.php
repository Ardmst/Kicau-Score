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
        Schema::create('penilaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran')->cascadeOnDelete();
            $table->foreignId('juri_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('skor_visual', 5, 2);
            $table->decimal('skor_audio', 5, 2);
            $table->decimal('total_skor_juri', 5, 2);
            $table->text('catatan')->nullable();
            $table->enum('status_validasi', ['draft', 'validated'])->default('draft')->index();
            $table->timestamps();

            $table->unique(['pendaftaran_id', 'juri_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian');
    }
};
