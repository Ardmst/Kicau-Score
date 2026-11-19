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
        Schema::create('kategori_lomba', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('nama_kategori', 100);
            $table->string('jenis_burung', 100)->nullable();
            $table->string('slug', 100);
            $table->decimal('biaya', 10, 2)->default(0);
            $table->unsignedInteger('kuota')->default(36);
            $table->enum('status_lomba', ['upcoming', 'ongoing', 'validasi', 'finished'])->default('upcoming')->index();
            $table->timestamps();

            $table->unique(['event_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_lomba');
    }
};
