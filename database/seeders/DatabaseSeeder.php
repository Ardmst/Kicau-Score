<?php

namespace Database\Seeders;

use App\Models\Burung;
use App\Models\Event;
use App\Models\KategoriLomba;
use App\Models\Pendaftaran;
use App\Models\Penilaian;
use App\Models\User;
use App\Services\ScoringEngineService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Users (Admin, Juri, Peserta)
        $admin = User::firstOrCreate(['email' => 'admin@kicauscore.com'], [
            'name' => 'Administrator KicauScore',
            'phone' => '081234567890',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $juri1 = User::firstOrCreate(['email' => 'bambang@kicauscore.com'], [
            'name' => 'Bambang Sutrisno (Juri 01)',
            'phone' => '081298765431',
            'password' => Hash::make('password'),
            'role' => 'juri',
        ]);

        $juri2 = User::firstOrCreate(['email' => 'joko@kicauscore.com'], [
            'name' => 'Joko Ansori (Juri 02)',
            'phone' => '081298765432',
            'password' => Hash::make('password'),
            'role' => 'juri',
        ]);

        $juri3 = User::firstOrCreate(['email' => 'tri@kicauscore.com'], [
            'name' => 'Tri Wibowo (Juri 03)',
            'phone' => '081298765433',
            'password' => Hash::make('password'),
            'role' => 'juri',
        ]);

        $peserta1 = User::firstOrCreate(['phone' => '081311223344'], [
            'name' => 'Ahmad Fauzi',
            'email' => 'fauzi@kicauscore.com',
            'phone' => '081311223344',
            'password' => Hash::make('password'),
            'role' => 'peserta',
        ]);

        $peserta2 = User::firstOrCreate(['email' => 'budi@kicauscore.com'], [
            'name' => 'Budi Santoso',
            'phone' => '081322334455',
            'password' => Hash::make('password'),
            'role' => 'peserta',
        ]);

        $peserta3 = User::firstOrCreate(['email' => 'hendra@kicauscore.com'], [
            'name' => 'Hendra Wijaya',
            'phone' => '081333445566',
            'password' => Hash::make('password'),
            'role' => 'peserta',
        ]);

        // 2. Events & Categories
        $event = Event::firstOrCreate(['nama_event' => 'Kicau Nusantara Cup 2026'], [
            'tanggal' => now()->addDays(7)->toDateString(),
            'lokasi' => 'Gantangan Alun-Alun, Solo',
            'deskripsi' => 'Kejuaraan Nasional Burung Berkicau Tingkat Master • Penilaian Algoritmik Multi-Juri Berstandar PBI & BNR',
            'status' => 'active',
        ]);

        $katMurai = KategoriLomba::firstOrCreate([
            'event_id' => $event->id,
            'slug' => 'murai-batu-vip-36g',
        ], [
            'nama_kategori' => 'Murai Batu VIP 36G',
            'jenis_burung' => 'Murai Batu',
            'biaya' => 350000,
            'kuota' => 36,
            'status_lomba' => 'ongoing',
        ]);

        $katLovebird = KategoriLomba::firstOrCreate([
            'event_id' => $event->id,
            'slug' => 'lovebird-champion-a',
        ], [
            'nama_kategori' => 'Lovebird Champion A',
            'jenis_burung' => 'Lovebird',
            'biaya' => 150000,
            'kuota' => 36,
            'status_lomba' => 'upcoming',
        ]);

        $katCucak = KategoriLomba::firstOrCreate([
            'event_id' => $event->id,
            'slug' => 'cucak-ijo-super-b',
        ], [
            'nama_kategori' => 'Cucak Ijo Super B',
            'jenis_burung' => 'Cucak Ijo',
            'biaya' => 200000,
            'kuota' => 36,
            'status_lomba' => 'upcoming',
        ]);

        $katKacer = KategoriLomba::firstOrCreate([
            'event_id' => $event->id,
            'slug' => 'kacer-bintang',
        ], [
            'nama_kategori' => 'Kacer Bintang',
            'jenis_burung' => 'Kacer',
            'biaya' => 175000,
            'kuota' => 36,
            'status_lomba' => 'finished',
        ]);

        // 3. Burung Milik Peserta
        $burung1 = Burung::firstOrCreate(['slug' => 'samurai-x'], [
            'user_id' => $peserta1->id,
            'nama_burung' => 'Samurai X',
            'jenis_burung' => 'Murai Batu',
            'no_ring' => 'BNR-SOLO-0921',
            'catatan' => 'Gaya tarung sujud agresif, tembakan cililin & kenari rapat.',
        ]);

        $burung2 = Burung::firstOrCreate(['slug' => 'halilintar'], [
            'user_id' => $peserta2->id,
            'nama_burung' => 'Halilintar',
            'jenis_burung' => 'Murai Batu',
            'no_ring' => 'PBI-JTG-1102',
            'catatan' => 'Volume tembus kristal, durasi kerja 10 menit stabil.',
        ]);

        $burung3 = Burung::firstOrCreate(['slug' => 'raja-laut'], [
            'user_id' => $peserta3->id,
            'nama_burung' => 'Raja Laut',
            'jenis_burung' => 'Murai Batu',
            'no_ring' => 'RGN-JKT-3341',
            'catatan' => 'Fisik tegap gagah ekor lentur, variasi materi greja tarung.',
        ]);

        $burung4 = Burung::firstOrCreate(['slug' => 'golden-boy'], [
            'user_id' => $peserta1->id,
            'nama_burung' => 'Golden Boy',
            'jenis_burung' => 'Lovebird',
            'no_ring' => 'LB-SOLO-4412',
            'catatan' => 'Ngekek panjang konslet menit awal hingga akhir.',
        ]);

        $burung5 = Burung::firstOrCreate(['slug' => 'bimasakti'], [
            'user_id' => $peserta2->id,
            'nama_burung' => 'Bimasakti',
            'jenis_burung' => 'Cucak Ijo',
            'no_ring' => 'CI-DIY-7721',
            'catatan' => 'Ngentrok jambul istimewa, rol lagu tajam.',
        ]);

        // 4. Pendaftaran di Kategori Murai Batu VIP 36G
        $p1 = Pendaftaran::firstOrCreate([
            'kategori_lomba_id' => $katMurai->id,
            'burung_id' => $burung1->id,
        ], [
            'user_id' => $peserta1->id,
            'nomor_gantangan' => 12,
            'metode_pembayaran' => 'transfer',
            'status_pembayaran' => 'verified',
            'bukti_pembayaran' => 'bukti_sample_1.jpg',
        ]);

        $p2 = Pendaftaran::firstOrCreate([
            'kategori_lomba_id' => $katMurai->id,
            'burung_id' => $burung2->id,
        ], [
            'user_id' => $peserta2->id,
            'nomor_gantangan' => 6,
            'metode_pembayaran' => 'transfer',
            'status_pembayaran' => 'verified',
            'bukti_pembayaran' => 'bukti_sample_2.jpg',
        ]);

        $p3 = Pendaftaran::firstOrCreate([
            'kategori_lomba_id' => $katMurai->id,
            'burung_id' => $burung3->id,
        ], [
            'user_id' => $peserta3->id,
            'nomor_gantangan' => 1,
            'metode_pembayaran' => 'cash',
            'status_pembayaran' => 'verified',
        ]);

        // Pendaftaran pending untuk testing verifikasi admin
        $pPending = Pendaftaran::firstOrCreate([
            'kategori_lomba_id' => $katLovebird->id,
            'burung_id' => $burung4->id,
        ], [
            'user_id' => $peserta1->id,
            'metode_pembayaran' => 'transfer',
            'status_pembayaran' => 'pending',
            'bukti_pembayaran' => 'bukti_sample_pending.jpg',
        ]);

        // 5. Penilaian oleh Juri 1, 2, 3 untuk p1 (Samurai X), p2 (Halilintar), p3 (Raja Laut)
        // Penilaian P1:
        Penilaian::firstOrCreate(['pendaftaran_id' => $p1->id, 'juri_id' => $juri1->id], [
            'skor_visual' => 92.50,
            'skor_audio' => 95.00,
            'total_skor_juri' => 187.50,
            'catatan' => 'Sujud agresif, irama lagu sangat variatif.',
            'status_validasi' => 'draft',
        ]);
        Penilaian::firstOrCreate(['pendaftaran_id' => $p1->id, 'juri_id' => $juri2->id], [
            'skor_visual' => 91.00,
            'skor_audio' => 94.50,
            'total_skor_juri' => 185.50,
            'catatan' => 'Tembakan kenari dan cililin rapat.',
            'status_validasi' => 'draft',
        ]);
        Penilaian::firstOrCreate(['pendaftaran_id' => $p1->id, 'juri_id' => $juri3->id], [
            'skor_visual' => 93.00,
            'skor_audio' => 96.00,
            'total_skor_juri' => 189.00,
            'catatan' => 'Performa sangat dominan di gantangan.',
            'status_validasi' => 'draft',
        ]);

        // Penilaian P2:
        Penilaian::firstOrCreate(['pendaftaran_id' => $p2->id, 'juri_id' => $juri1->id], [
            'skor_visual' => 90.00,
            'skor_audio' => 91.50,
            'total_skor_juri' => 181.50,
            'status_validasi' => 'draft',
        ]);
        Penilaian::firstOrCreate(['pendaftaran_id' => $p2->id, 'juri_id' => $juri2->id], [
            'skor_visual' => 89.50,
            'skor_audio' => 92.00,
            'total_skor_juri' => 181.50,
            'status_validasi' => 'draft',
        ]);
        Penilaian::firstOrCreate(['pendaftaran_id' => $p2->id, 'juri_id' => $juri3->id], [
            'skor_visual' => 91.00,
            'skor_audio' => 91.00,
            'total_skor_juri' => 182.00,
            'status_validasi' => 'draft',
        ]);

        // Penilaian P3:
        Penilaian::firstOrCreate(['pendaftaran_id' => $p3->id, 'juri_id' => $juri1->id], [
            'skor_visual' => 88.00,
            'skor_audio' => 89.00,
            'total_skor_juri' => 177.00,
            'status_validasi' => 'draft',
        ]);
        Penilaian::firstOrCreate(['pendaftaran_id' => $p3->id, 'juri_id' => $juri2->id], [
            'skor_visual' => 87.50,
            'skor_audio' => 90.00,
            'total_skor_juri' => 177.50,
            'status_validasi' => 'draft',
        ]);
        Penilaian::firstOrCreate(['pendaftaran_id' => $p3->id, 'juri_id' => $juri3->id], [
            'skor_visual' => 89.00,
            'skor_audio' => 88.50,
            'total_skor_juri' => 177.50,
            'status_validasi' => 'draft',
        ]);

        // 6. Jalankan ScoringEngineService untuk menghitung agregasi rata-rata & klasemen awal
        $scoringService = new ScoringEngineService;
        $scoringService->hitungHasilKategori($katMurai->id);
    }
}
