<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom kode_id ke tabel users
        if (! Schema::hasColumn('users', 'kode_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('kode_id', 20)->nullable()->after('id')->index();
            });
        }

        // 2. Generate kode_id terstruktur untuk seluruh user eksisting dan bersihkan embel-embel (Juri 01)
        $users = DB::table('users')->orderBy('id')->get();
        $counterAdmin = 1;
        $counterJuri = 1;
        $counterPeserta = 1;

        foreach ($users as $user) {
            $prefix = match ($user->role) {
                'admin' => 'ADM',
                'juri' => 'JUR',
                'peserta' => 'PST',
                default => 'USR',
            };

            $num = match ($user->role) {
                'admin' => $counterAdmin++,
                'juri' => $counterJuri++,
                'peserta' => $counterPeserta++,
                default => $user->id,
            };

            $cleanName = preg_replace('/\s*\(Juri\s*\d+\)/i', '', $user->name);

            DB::table('users')->where('id', $user->id)->update([
                'kode_id' => sprintf('%s-%03d', $prefix, $num),
                'name' => trim($cleanName),
            ]);
        }

        // 3. Buat pivot table penugasan juri ke kategori lomba
        if (! Schema::hasTable('kategori_juri')) {
            Schema::create('kategori_juri', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kategori_lomba_id')->constrained('kategori_lomba')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['kategori_lomba_id', 'user_id']);
            });

            // Daftarkan juri eksisting ke kategori lomba yang sudah ada
            $allJuriIds = DB::table('users')->where('role', 'juri')->pluck('id');
            $allKategoriIds = DB::table('kategori_lomba')->pluck('id');

            foreach ($allKategoriIds as $kategoriId) {
                foreach ($allJuriIds as $juriId) {
                    DB::table('kategori_juri')->insertOrIgnore([
                        'kategori_lomba_id' => $kategoriId,
                        'user_id' => $juriId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_juri');

        if (Schema::hasColumn('users', 'kode_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('kode_id');
            });
        }
    }
};
