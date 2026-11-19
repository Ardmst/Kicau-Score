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
        Schema::create('pendaftaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_lomba_id')->constrained('kategori_lomba')->cascadeOnDelete();
            $table->foreignId('burung_id')->unique()->constrained('burungs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('nomor_gantangan')->nullable();
            $table->enum('metode_pembayaran', ['qris', 'transfer', 'cash'])->default('qris');
            $table->enum('status_pembayaran', ['pending', 'verified', 'rejected'])->default('pending')->index();
            $table->string('bukti_pembayaran')->nullable();
            $table->decimal('total_skor_akhir', 5, 2)->nullable();
            $table->decimal('rata_audio', 5, 2)->nullable();
            $table->decimal('rata_visual', 5, 2)->nullable();
            $table->unsignedInteger('peringkat')->nullable();
            $table->timestamps();

            $table->unique(['kategori_lomba_id', 'nomor_gantangan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftaran');
    }
};
