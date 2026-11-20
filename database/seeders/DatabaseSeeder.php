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
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with complete realistic dummy data:
     * - 2 Admins (ADM-001, ADM-002)
     * - 10 Dewan Juri (JUR-001 .. JUR-010)
     * - 40 Peserta (PST-001 .. PST-040) dengan minimal 2 burung per peserta (~90 burung)
     * - 4 Events (1 Finished, 1 Ongoing Hari Ini, 2 Upcoming)
     * - Pendaftaran, gantangan, penilaian juri, dan skor terkunci
     */
    public function run(): void
    {
        $passwordHash = Hash::make('password');
        $scoringService = new ScoringEngineService;

        // =========================================================================
        // 1. ADMINS (2 User)
        // =========================================================================
        $admin1 = User::firstOrCreate(['email' => 'admin@kicauscore.com'], [
            'kode_id' => 'ADM-001',
            'name' => 'Administrator KicauScore',
            'phone' => '081234567890',
            'password' => $passwordHash,
            'role' => 'admin',
        ]);

        $admin2 = User::firstOrCreate(['email' => 'panitia@kicauscore.com'], [
            'kode_id' => 'ADM-002',
            'name' => 'Panitia & Operator Lapangan',
            'phone' => '081234567891',
            'password' => $passwordHash,
            'role' => 'admin',
        ]);

        // =========================================================================
        // 2. DEWAN JURI (10 User)
        // =========================================================================
        $juriData = [
            ['kode_id' => 'JUR-001', 'name' => 'Budi Santoso', 'email' => 'juri1@kicauscore.com', 'phone' => '081298765431'],
            ['kode_id' => 'JUR-002', 'name' => 'Hendra Wijaya', 'email' => 'juri2@kicauscore.com', 'phone' => '081298765432'],
            ['kode_id' => 'JUR-003', 'name' => 'Agus Pratama', 'email' => 'juri3@kicauscore.com', 'phone' => '081298765433'],
            ['kode_id' => 'JUR-004', 'name' => 'Slamet Riyadi', 'email' => 'juri4@kicauscore.com', 'phone' => '081298765434'],
            ['kode_id' => 'JUR-005', 'name' => 'Joko Susilo', 'email' => 'juri5@kicauscore.com', 'phone' => '081298765435'],
            ['kode_id' => 'JUR-006', 'name' => 'Eko Wahyudi', 'email' => 'juri6@kicauscore.com', 'phone' => '081298765436'],
            ['kode_id' => 'JUR-007', 'name' => 'Dedi Kurniawan', 'email' => 'juri7@kicauscore.com', 'phone' => '081298765437'],
            ['kode_id' => 'JUR-008', 'name' => 'Wahyu Hidayat', 'email' => 'juri8@kicauscore.com', 'phone' => '081298765438'],
            ['kode_id' => 'JUR-009', 'name' => 'Arif Budiman', 'email' => 'juri9@kicauscore.com', 'phone' => '081298765439'],
            ['kode_id' => 'JUR-010', 'name' => 'Bambang Setiawan', 'email' => 'juri10@kicauscore.com', 'phone' => '081298765440'],
        ];

        $juris = [];
        foreach ($juriData as $jd) {
            $juris[] = User::firstOrCreate(['email' => $jd['email']], [
                'kode_id' => $jd['kode_id'],
                'name' => $jd['name'],
                'phone' => $jd['phone'],
                'password' => $passwordHash,
                'role' => 'juri',
            ]);
        }

        // =========================================================================
        // 3. PESERTA (40 User) & BURUNG (Minimal 2 per peserta)
        // =========================================================================
        $pesertaNames = [
            'Ahmad Fauzi', 'Bambang Sutrisno', 'Hendra Gunawan', 'Dimas Prasetyo', 'Rizky Ramadhan',
            'Fajar Nugroho', 'Bayu Setiawan', 'Aditya Pratama', 'Ilham Hidayat', 'Teguh Wibowo',
            'Wahyu Utomo', 'Danang Kurniawan', 'Rian Saputra', 'Arif Rahman', 'Galih Permana',
            'Bagus Pamungkas', 'Indra Lesmana', 'Yoga Pratama', 'Faisal Akbar', 'Doni Prasetya',
            'Sigit Purnomo', 'Gilang Ramadhan', 'Radit Kusuma', 'Irfan Hakim', 'Sandy Firmansyah',
            'Deni Iskandar', 'Aldi Maulana', 'Lukman Hakim', 'Yudi Hermawan', 'Agus Salim',
            'Hendri Syahputra', 'Farhan Fauzan', 'Kevin Sanjaya', 'Tommy Sugiarto', 'Dian Anggoro',
            'Satria Wibisono', 'Reza Pahlevi', 'Aris Munandar', 'Rahmat Basuki', 'Firman Utina',
        ];

        $pesertas = [];
        $allBurungs = [];

        $birdTemplates = [
            ['name' => 'Samurai X', 'jenis' => 'Murai Batu', 'prefix' => 'BNR-SLO', 'desc' => 'Gaya tarung sujud agresif, tembakan cililin & kenari rapat.'],
            ['name' => 'Halilintar', 'jenis' => 'Murai Batu', 'prefix' => 'PBI-JTG', 'desc' => 'Volume tembus kristal, durasi kerja 10 menit stabil.'],
            ['name' => 'Raja Laut', 'jenis' => 'Murai Batu', 'prefix' => 'RGN-JKT', 'desc' => 'Fisik tegap gagah ekor lentur, variasi materi greja tarung.'],
            ['name' => 'Badai Selatan', 'jenis' => 'Murai Batu', 'prefix' => 'ORIQ-BDG', 'desc' => 'Tembakan pedas cililin, kapas tembak, durasi panjang.'],
            ['name' => 'Siluman Kera', 'jenis' => 'Murai Batu', 'prefix' => 'BNR-SBY', 'desc' => 'Gaya ngeplay buka sayap, rol rapat speed rapat.'],
            ['name' => 'Panglima', 'jenis' => 'Murai Batu', 'prefix' => 'PBI-DIY', 'desc' => 'Suara lantang menggelegar, variasi lovebird dan jenggot.'],
            ['name' => 'Dewa Ruci', 'jenis' => 'Murai Batu', 'prefix' => 'RGN-SMG', 'desc' => 'Show mewah sujud sampai bawah tangkringan.'],
            ['name' => 'Black Pearl', 'jenis' => 'Murai Batu', 'prefix' => 'BNR-SMG', 'desc' => 'Mental tarung tangguh tidak pernah kendor.'],
            ['name' => 'Krakatau', 'jenis' => 'Murai Batu', 'prefix' => 'ORIQ-CRB', 'desc' => 'Lagu kenarian kristal disambung suara tembakan tajam.'],
            ['name' => 'Petir', 'jenis' => 'Murai Batu', 'prefix' => 'PBI-JTM', 'desc' => 'Stamina prima di gantangan panas terik.'],

            ['name' => 'Bimasakti', 'jenis' => 'Cucak Ijo', 'prefix' => 'CI-DIY', 'desc' => 'Ngentrok jambul istimewa, rol lagu tajam.'],
            ['name' => 'Green Monster', 'jenis' => 'Cucak Ijo', 'prefix' => 'CI-SLO', 'desc' => 'Bongkar isian tengkek buto dan cucak jenggot.'],
            ['name' => 'Zamrud Khatulistiwa', 'jenis' => 'Cucak Ijo', 'prefix' => 'CI-SBY', 'desc' => 'Gaya hiper getar ekor, durasi nyaris tanpa jeda.'],
            ['name' => 'Hulk Mania', 'jenis' => 'Cucak Ijo', 'prefix' => 'CI-KLT', 'desc' => 'Volume dahsyat, tembakan walang krek rapat.'],
            ['name' => 'Super Green', 'jenis' => 'Cucak Ijo', 'prefix' => 'CI-BDG', 'desc' => 'Irama lagu harmonis, jeda sangat tipis.'],
            ['name' => 'Raja Jamtrok', 'jenis' => 'Cucak Ijo', 'prefix' => 'CI-JKT', 'desc' => 'Ngentrok dari awal digantang sampai bendera koncer.'],

            ['name' => 'Pendekar Hitam', 'jenis' => 'Kacer', 'prefix' => 'KC-SLO', 'desc' => 'Buka ekor ngobra full 360 derajat, roll speed mewah.'],
            ['name' => 'Black Diamond', 'jenis' => 'Kacer', 'prefix' => 'KC-JTG', 'desc' => 'Materi sirpu, prenjak gunung, tembakan tajam menusuk.'],
            ['name' => 'Pangeran Hitam', 'jenis' => 'Kacer', 'prefix' => 'KC-DIY', 'desc' => 'Speed rapat ngobra stabil tangkringan anteng.'],
            ['name' => 'Satria Bergitar', 'jenis' => 'Kacer', 'prefix' => 'KC-SBY', 'desc' => 'Irama lagu mewah, volume terdengar jelas hingga luar arena.'],
            ['name' => 'Singo Edan', 'jenis' => 'Kacer', 'prefix' => 'KC-MLG', 'desc' => 'Tarung ngotot tidak gembung, performa luar biasa.'],

            ['name' => 'Golden Boy', 'jenis' => 'Lovebird', 'prefix' => 'LB-SOLO', 'desc' => 'Ngekek panjang konslet menit awal hingga akhir.'],
            ['name' => 'Ratu Ngepet', 'jenis' => 'Lovebird', 'prefix' => 'LB-JTG', 'desc' => 'Durasi 2 menit up konslet lemes tanpa henti.'],
            ['name' => 'Bella Vista', 'jenis' => 'Lovebird', 'prefix' => 'LB-DIY', 'desc' => 'Ngekek fighter tembus lapangan, volume keras.'],
            ['name' => 'Super Sonic', 'jenis' => 'Lovebird', 'prefix' => 'LB-SBY', 'desc' => 'Jeda rapat 3 detik langsung sambung ngekek.'],

            ['name' => 'Ken Arok', 'jenis' => 'Kenari', 'prefix' => 'KN-KLT', 'desc' => 'Cengkok meliuk mewah, buka paruh lebar spasi rapi.'],
            ['name' => 'Yellow Star', 'jenis' => 'Kenari', 'prefix' => 'KN-SLO', 'desc' => 'Durasi rol 45 detik nonstop, volume kristal.'],
            ['name' => 'Bintang Kejora', 'jenis' => 'Kenari', 'prefix' => 'KN-DIY', 'desc' => 'Gaya wiper goyang kanan kiri nagen.'],

            ['name' => 'Dewa Mabuk', 'jenis' => 'Anis Merah', 'prefix' => 'AM-BALI', 'desc' => 'Teler rebah mentok kiri kanan pules istimewa.'],
            ['name' => 'Red Dragon', 'jenis' => 'Anis Merah', 'prefix' => 'AM-JTG', 'desc' => 'Teler doyong geser tangkringan irama merdu.'],

            ['name' => 'Raja Hutan', 'jenis' => 'Cendet', 'prefix' => 'CD-MDN', 'desc' => 'Gaya tegak angka 1, rol tembak gereja tarung & kunti.'],
            ['name' => 'Alap-Alap', 'jenis' => 'Branjangan', 'prefix' => 'BJ-JTG', 'desc' => 'Hovering kepak sayap sembari bongkar isian.'],
        ];

        $birdTemplateIndex = 0;
        $totalTemplates = count($birdTemplates);

        for ($i = 0; $i < 40; $i++) {
            $num = $i + 1;
            $kodeId = sprintf('PST-%03d', $num);
            $name = $pesertaNames[$i];
            $email = sprintf('peserta%d@kicauscore.com', $num);
            $phone = sprintf('08130000%04d', $num);

            $peserta = User::firstOrCreate(['email' => $email], [
                'kode_id' => $kodeId,
                'name' => $name,
                'phone' => $phone,
                'password' => $passwordHash,
                'role' => 'peserta',
            ]);
            $pesertas[] = $peserta;

            // Setiap peserta memiliki minimal 2 burung (peserta ke-1 s/d 10 punya 3 burung)
            $birdsCount = ($i < 10) ? 3 : 2;

            for ($b = 0; $b < $birdsCount; $b++) {
                $tpl = $birdTemplates[$birdTemplateIndex % $totalTemplates];
                $birdTemplateIndex++;

                $birdName = $tpl['name'].' ('.$peserta->name.')';
                $slug = Str::slug($birdName.'-'.$peserta->id.'-'.$b);
                $ringNo = $tpl['prefix'].'-'.rand(100, 999);

                $burung = Burung::firstOrCreate(['slug' => $slug], [
                    'user_id' => $peserta->id,
                    'nama_burung' => $tpl['name'],
                    'jenis_burung' => $tpl['jenis'],
                    'no_ring' => $ringNo,
                    'catatan' => $tpl['desc'],
                ]);

                $allBurungs[] = [
                    'burung' => $burung,
                    'user' => $peserta,
                    'jenis' => $tpl['jenis'],
                ];
            }
        }

        // =========================================================================
        // 4. EVENTS (4 Master Events)
        // =========================================================================

        // -------------------------------------------------------------------------
        // EVENT 1 (FINISHED): Piala Raja Mangkunegaran 2026
        // -------------------------------------------------------------------------
        $event1 = Event::firstOrCreate(['nama_event' => 'Piala Raja Mangkunegaran 2026'], [
            'tanggal' => now()->subDays(3)->toDateString(),
            'jam_mulai' => '08:00',
            'jam_selesai' => '17:00',
            'lokasi' => 'Taman Balekambang, Surakarta',
            'deskripsi' => 'Kejuaraan Akbar Kicaumania Mangkunegaran • Kompetisi Resmi Berstandar PBI & BnR dengan Hadiah Utama Mobil & Trophy Mahkota Emas.',
            'status' => 'finished',
        ]);

        $ev1KatMurai = KategoriLomba::firstOrCreate([
            'event_id' => $event1->id,
            'slug' => 'murai-batu-ring-presiden',
        ], [
            'nama_kategori' => 'Murai Batu Ring Presiden',
            'jenis_burung' => 'Murai Batu',
            'biaya' => 500000,
            'kuota' => 36,
            'jam_mulai' => '08:30',
            'jam_selesai' => '10:30',
            'status_lomba' => 'finished',
        ]);
        $ev1KatMurai->juris()->sync([$juris[0]->id, $juris[1]->id, $juris[2]->id, $juris[3]->id]);

        $ev1KatCucak = KategoriLomba::firstOrCreate([
            'event_id' => $event1->id,
            'slug' => 'cucak-ijo-mangkunegaran',
        ], [
            'nama_kategori' => 'Cucak Ijo Mangkunegaran',
            'jenis_burung' => 'Cucak Ijo',
            'biaya' => 250000,
            'kuota' => 36,
            'jam_mulai' => '10:30',
            'jam_selesai' => '12:00',
            'status_lomba' => 'finished',
        ]);
        $ev1KatCucak->juris()->sync([$juris[1]->id, $juris[3]->id, $juris[5]->id]);

        $ev1KatKacer = KategoriLomba::firstOrCreate([
            'event_id' => $event1->id,
            'slug' => 'kacer-adipati',
        ], [
            'nama_kategori' => 'Kacer Adipati',
            'jenis_burung' => 'Kacer',
            'biaya' => 200000,
            'kuota' => 36,
            'jam_mulai' => '13:00',
            'jam_selesai' => '14:30',
            'status_lomba' => 'finished',
        ]);
        $ev1KatKacer->juris()->sync([$juris[0]->id, $juris[2]->id, $juris[4]->id]);

        // Isi Pendaftaran & Nilai Terkunci untuk Event 1 Murai Batu
        $muraiBurungs = array_values(array_filter($allBurungs, fn ($b) => $b['jenis'] === 'Murai Batu'));
        $cucakBurungs = array_values(array_filter($allBurungs, fn ($b) => $b['jenis'] === 'Cucak Ijo'));
        $kacerBurungs = array_values(array_filter($allBurungs, fn ($b) => $b['jenis'] === 'Kacer'));

        for ($k = 0; $k < 10; $k++) {
            if (! isset($muraiBurungs[$k])) {
                break;
            }
            $bItem = $muraiBurungs[$k];
            $p = Pendaftaran::firstOrCreate([
                'kategori_lomba_id' => $ev1KatMurai->id,
                'burung_id' => $bItem['burung']->id,
            ], [
                'user_id' => $bItem['user']->id,
                'nomor_gantangan' => $k + 1,
                'metode_pembayaran' => 'transfer',
                'status_pembayaran' => 'verified',
                'bukti_pembayaran' => 'bukti_piala_raja_'.($k + 1).'.jpg',
            ]);

            // Nilai juri 1..4
            $baseVis = 90 - ($k * 1.5) + rand(-10, 10) / 10;
            $baseAud = 92 - ($k * 1.5) + rand(-10, 10) / 10;
            foreach ([$juris[0], $juris[1], $juris[2], $juris[3]] as $juri) {
                $v = max(75, min(99, $baseVis + (rand(-10, 10) / 10)));
                $a = max(75, min(99, $baseAud + (rand(-10, 10) / 10)));
                Penilaian::firstOrCreate(['pendaftaran_id' => $p->id, 'juri_id' => $juri->id], [
                    'skor_visual' => $v,
                    'skor_audio' => $a,
                    'total_skor_juri' => $v + $a,
                    'catatan' => 'Performa stabil di kelas Ring Presiden.',
                    'status_validasi' => 'validated',
                ]);
            }
        }
        $scoringService->validasiDanKunciKategori($ev1KatMurai->id, 'finished');

        // Pendaftaran & Nilai untuk Cucak Ijo Event 1
        for ($k = 0; $k < 8; $k++) {
            if (! isset($cucakBurungs[$k])) {
                break;
            }
            $bItem = $cucakBurungs[$k];
            $p = Pendaftaran::firstOrCreate([
                'kategori_lomba_id' => $ev1KatCucak->id,
                'burung_id' => $bItem['burung']->id,
            ], [
                'user_id' => $bItem['user']->id,
                'nomor_gantangan' => $k + 1,
                'metode_pembayaran' => 'transfer',
                'status_pembayaran' => 'verified',
            ]);
            foreach ([$juris[1], $juris[3], $juris[5]] as $juri) {
                $v = 88 + rand(0, 8);
                $a = 89 + rand(0, 8);
                Penilaian::firstOrCreate(['pendaftaran_id' => $p->id, 'juri_id' => $juri->id], [
                    'skor_visual' => $v,
                    'skor_audio' => $a,
                    'total_skor_juri' => $v + $a,
                    'status_validasi' => 'validated',
                ]);
            }
        }
        $scoringService->validasiDanKunciKategori($ev1KatCucak->id, 'finished');

        // -------------------------------------------------------------------------
        // EVENT 2 (ONGOING HARI INI): Kicau Nusantara Cup 2026
        // -------------------------------------------------------------------------
        $event2 = Event::firstOrCreate(['nama_event' => 'Kicau Nusantara Cup 2026'], [
            'tanggal' => now()->toDateString(),
            'jam_mulai' => '08:30',
            'jam_selesai' => '17:30',
            'lokasi' => 'Arena Gantangan Alun-Alun, Solo',
            'deskripsi' => 'Kejuaraan Nasional Burung Berkicau Tingkat Master • Penilaian Algoritmik Multi-Juri Berstandar PBI & BnR.',
            'status' => 'active',
        ]);

        // Kategori 1: Murai Batu VIP 36G (ONGOING - LIVE PENILAIAN)
        $ev2KatMurai = KategoriLomba::firstOrCreate([
            'event_id' => $event2->id,
            'slug' => 'murai-batu-vip-36g',
        ], [
            'nama_kategori' => 'Murai Batu VIP 36G',
            'jenis_burung' => 'Murai Batu',
            'biaya' => 350000,
            'kuota' => 36,
            'jam_mulai' => '08:30',
            'jam_selesai' => '11:00',
            'status_lomba' => 'ongoing',
        ]);
        $ev2KatMurai->juris()->sync([$juris[0]->id, $juris[1]->id, $juris[2]->id]);

        // Kategori 2: Cucak Ijo Super B (ONGOING)
        $ev2KatCucak = KategoriLomba::firstOrCreate([
            'event_id' => $event2->id,
            'slug' => 'cucak-ijo-super-b',
        ], [
            'nama_kategori' => 'Cucak Ijo Super B',
            'jenis_burung' => 'Cucak Ijo',
            'biaya' => 200000,
            'kuota' => 36,
            'jam_mulai' => '11:00',
            'jam_selesai' => '13:00',
            'status_lomba' => 'ongoing',
        ]);
        $ev2KatCucak->juris()->sync([$juris[3]->id, $juris[4]->id, $juris[5]->id]);

        // Kategori 3: Kacer Bintang (VALIDASI / TERKUNCI)
        $ev2KatKacer = KategoriLomba::firstOrCreate([
            'event_id' => $event2->id,
            'slug' => 'kacer-bintang',
        ], [
            'nama_kategori' => 'Kacer Bintang',
            'jenis_burung' => 'Kacer',
            'biaya' => 175000,
            'kuota' => 36,
            'jam_mulai' => '13:00',
            'jam_selesai' => '14:30',
            'status_lomba' => 'validasi',
        ]);
        $ev2KatKacer->juris()->sync([$juris[0]->id, $juris[2]->id, $juris[4]->id]);

        // Kategori 4: Lovebird Champion A (UPCOMING SORE)
        $ev2KatLovebird = KategoriLomba::firstOrCreate([
            'event_id' => $event2->id,
            'slug' => 'lovebird-champion-a',
        ], [
            'nama_kategori' => 'Lovebird Champion A',
            'jenis_burung' => 'Lovebird',
            'biaya' => 150000,
            'kuota' => 36,
            'jam_mulai' => '15:00',
            'jam_selesai' => '16:30',
            'status_lomba' => 'upcoming',
        ]);
        $ev2KatLovebird->juris()->sync([$juris[6]->id, $juris[7]->id, $juris[8]->id]);

        // Kategori 5: Kenari Reguler (UPCOMING SORE)
        $ev2KatKenari = KategoriLomba::firstOrCreate([
            'event_id' => $event2->id,
            'slug' => 'kenari-reguler',
        ], [
            'nama_kategori' => 'Kenari Reguler',
            'jenis_burung' => 'Kenari',
            'biaya' => 100000,
            'kuota' => 36,
            'jam_mulai' => '16:30',
            'jam_selesai' => '17:30',
            'status_lomba' => 'upcoming',
        ]);
        $ev2KatKenari->juris()->sync([$juris[7]->id, $juris[8]->id, $juris[9]->id]);

        // Pendaftaran & Penilaian Live untuk Event 2 Murai Batu (12 Peserta)
        for ($k = 0; $k < 12; $k++) {
            $idx = ($k + 10) % count($muraiBurungs);
            $bItem = $muraiBurungs[$idx];
            $p = Pendaftaran::firstOrCreate([
                'kategori_lomba_id' => $ev2KatMurai->id,
                'burung_id' => $bItem['burung']->id,
            ], [
                'user_id' => $bItem['user']->id,
                'nomor_gantangan' => $k + 1,
                'metode_pembayaran' => ($k % 2 === 0) ? 'transfer' : 'cash',
                'status_pembayaran' => 'verified',
                'bukti_pembayaran' => 'bukti_gantangan_'.($k + 1).'.jpg',
            ]);

            // Beri nilai juri 1, 2, 3 (live skor)
            foreach ([$juris[0], $juris[1], $juris[2]] as $juri) {
                $vis = 88.0 + rand(0, 9);
                $aud = 90.0 + rand(0, 8);
                Penilaian::firstOrCreate(['pendaftaran_id' => $p->id, 'juri_id' => $juri->id], [
                    'skor_visual' => $vis,
                    'skor_audio' => $aud,
                    'total_skor_juri' => $vis + $aud,
                    'catatan' => 'Sesi live gantangan Alun-Alun.',
                    'status_validasi' => 'draft',
                ]);
            }
        }
        $scoringService->hitungHasilKategori($ev2KatMurai->id);

        // Pendaftaran Kacer Bintang Event 2 (Terkunci / Validasi)
        for ($k = 0; $k < 8; $k++) {
            $idx = $k % count($kacerBurungs);
            $bItem = $kacerBurungs[$idx];
            $p = Pendaftaran::firstOrCreate([
                'kategori_lomba_id' => $ev2KatKacer->id,
                'burung_id' => $bItem['burung']->id,
            ], [
                'user_id' => $bItem['user']->id,
                'nomor_gantangan' => $k + 1,
                'metode_pembayaran' => 'transfer',
                'status_pembayaran' => 'verified',
            ]);
            foreach ([$juris[0], $juris[2], $juris[4]] as $juri) {
                $vis = 87 + rand(0, 8);
                $aud = 88 + rand(0, 8);
                Penilaian::firstOrCreate(['pendaftaran_id' => $p->id, 'juri_id' => $juri->id], [
                    'skor_visual' => $vis,
                    'skor_audio' => $aud,
                    'total_skor_juri' => $vis + $aud,
                    'status_validasi' => 'validated',
                ]);
            }
        }
        $scoringService->validasiDanKunciKategori($ev2KatKacer->id, 'validasi');

        // Pendaftaran Lovebird & Kenari (Upcoming)
        $lovebirdBurungs = array_values(array_filter($allBurungs, fn ($b) => $b['jenis'] === 'Lovebird'));
        for ($k = 0; $k < min(6, count($lovebirdBurungs)); $k++) {
            $bItem = $lovebirdBurungs[$k];
            Pendaftaran::firstOrCreate([
                'kategori_lomba_id' => $ev2KatLovebird->id,
                'burung_id' => $bItem['burung']->id,
            ], [
                'user_id' => $bItem['user']->id,
                'nomor_gantangan' => $k + 1,
                'metode_pembayaran' => 'transfer',
                'status_pembayaran' => 'verified',
            ]);
        }

        // -------------------------------------------------------------------------
        // EVENT 3 (UPCOMING 1): Piala Presiden Kicaumania 2026 (+7 Hari)
        // -------------------------------------------------------------------------
        $event3 = Event::firstOrCreate(['nama_event' => 'Piala Presiden Kicaumania 2026'], [
            'tanggal' => now()->addDays(7)->toDateString(),
            'jam_mulai' => '08:00',
            'jam_selesai' => '17:00',
            'lokasi' => 'Stadion Manahan Open Arena, Solo',
            'deskripsi' => 'Turnamen Akbar Bergengsi Piala Presiden Kicaumania Se-Jawa Bali 2026 • Tiket Pendaftaran Online Terbuka.',
            'status' => 'active',
        ]);

        $ev3KatMurai = KategoriLomba::firstOrCreate([
            'event_id' => $event3->id,
            'slug' => 'murai-batu-presiden-super',
        ], [
            'nama_kategori' => 'Murai Batu Presiden Super',
            'jenis_burung' => 'Murai Batu',
            'biaya' => 500000,
            'kuota' => 36,
            'jam_mulai' => '09:00',
            'jam_selesai' => '11:00',
            'status_lomba' => 'upcoming',
        ]);
        $ev3KatMurai->juris()->sync([$juris[0]->id, $juris[1]->id, $juris[2]->id, $juris[3]->id]);

        $ev3KatCucak = KategoriLomba::firstOrCreate([
            'event_id' => $event3->id,
            'slug' => 'cucak-ijo-manahan',
        ], [
            'nama_kategori' => 'Cucak Ijo Manahan',
            'jenis_burung' => 'Cucak Ijo',
            'biaya' => 250000,
            'kuota' => 36,
            'jam_mulai' => '11:00',
            'jam_selesai' => '12:30',
            'status_lomba' => 'upcoming',
        ]);
        $ev3KatCucak->juris()->sync([$juris[4]->id, $juris[5]->id]);

        $ev3KatKacer = KategoriLomba::firstOrCreate([
            'event_id' => $event3->id,
            'slug' => 'kacer-mataram',
        ], [
            'nama_kategori' => 'Kacer Mataram',
            'jenis_burung' => 'Kacer',
            'biaya' => 200000,
            'kuota' => 36,
            'jam_mulai' => '13:30',
            'jam_selesai' => '15:00',
            'status_lomba' => 'upcoming',
        ]);
        $ev3KatKacer->juris()->sync([$juris[6]->id, $juris[7]->id]);

        $ev3KatLovebird = KategoriLomba::firstOrCreate([
            'event_id' => $event3->id,
            'slug' => 'lovebird-konslet-mania',
        ], [
            'nama_kategori' => 'Lovebird Konslet Mania',
            'jenis_burung' => 'Lovebird',
            'biaya' => 150000,
            'kuota' => 36,
            'jam_mulai' => '15:00',
            'jam_selesai' => '16:30',
            'status_lomba' => 'upcoming',
        ]);
        $ev3KatLovebird->juris()->sync([$juris[8]->id, $juris[9]->id]);

        // Tambah beberapa pendaftaran status PENDING untuk demo antrian verifikasi Admin
        for ($pPending = 0; $pPending < 3; $pPending++) {
            $burungPendingIdx = 22 + $pPending;
            if (isset($muraiBurungs[$burungPendingIdx])) {
                $bItem = $muraiBurungs[$burungPendingIdx];
                Pendaftaran::firstOrCreate([
                    'kategori_lomba_id' => $ev3KatMurai->id,
                    'burung_id' => $bItem['burung']->id,
                ], [
                    'user_id' => $bItem['user']->id,
                    'metode_pembayaran' => 'transfer',
                    'status_pembayaran' => 'pending',
                    'bukti_pembayaran' => 'bukti_transfer_presiden_'.($pPending + 1).'.jpg',
                ]);
            }
        }

        // -------------------------------------------------------------------------
        // EVENT 4 (UPCOMING 2): Grand Prix BnR Indonesia Master 2026 (+14 Hari)
        // -------------------------------------------------------------------------
        $event4 = Event::firstOrCreate(['nama_event' => 'Grand Prix BnR Indonesia Master 2026'], [
            'tanggal' => now()->addDays(14)->toDateString(),
            'jam_mulai' => '08:00',
            'jam_selesai' => '17:30',
            'lokasi' => 'Taman Mini Indonesia Indah, Jakarta',
            'deskripsi' => 'Seri Grand Prix BnR Indonesia Master Road to Asia Bird Championship 2027 • Perebutan Piala Bergilir & Sertifikasi Internasional.',
            'status' => 'active',
        ]);

        $ev4KatMurai = KategoriLomba::firstOrCreate([
            'event_id' => $event4->id,
            'slug' => 'murai-batu-master-asia',
        ], [
            'nama_kategori' => 'Murai Batu Master Asia',
            'jenis_burung' => 'Murai Batu',
            'biaya' => 750000,
            'kuota' => 36,
            'jam_mulai' => '09:00',
            'jam_selesai' => '11:30',
            'status_lomba' => 'upcoming',
        ]);
        $ev4KatMurai->juris()->sync([$juris[0]->id, $juris[1]->id, $juris[2]->id, $juris[3]->id, $juris[4]->id]);

        $ev4KatCucak = KategoriLomba::firstOrCreate([
            'event_id' => $event4->id,
            'slug' => 'cucak-ijo-nasional',
        ], [
            'nama_kategori' => 'Cucak Ijo Nasional',
            'jenis_burung' => 'Cucak Ijo',
            'biaya' => 350000,
            'kuota' => 36,
            'jam_mulai' => '11:30',
            'jam_selesai' => '13:00',
            'status_lomba' => 'upcoming',
        ]);
        $ev4KatCucak->juris()->sync([$juris[5]->id, $juris[6]->id]);

        $ev4KatKacer = KategoriLomba::firstOrCreate([
            'event_id' => $event4->id,
            'slug' => 'kacer-hitam-putih',
        ], [
            'nama_kategori' => 'Kacer Hitam Putih',
            'jenis_burung' => 'Kacer',
            'biaya' => 250000,
            'kuota' => 36,
            'jam_mulai' => '13:30',
            'jam_selesai' => '15:00',
            'status_lomba' => 'upcoming',
        ]);
        $ev4KatKacer->juris()->sync([$juris[7]->id, $juris[8]->id]);

        $ev4KatAnis = KategoriLomba::firstOrCreate([
            'event_id' => $event4->id,
            'slug' => 'anis-merah-teler-mania',
        ], [
            'nama_kategori' => 'Anis Merah Teler Mania',
            'jenis_burung' => 'Anis Merah',
            'biaya' => 200000,
            'kuota' => 36,
            'jam_mulai' => '15:00',
            'jam_selesai' => '16:30',
            'status_lomba' => 'upcoming',
        ]);
        $ev4KatAnis->juris()->sync([$juris[1]->id, $juris[9]->id]);

        $ev4KatBranjangan = KategoriLomba::firstOrCreate([
            'event_id' => $event4->id,
            'slug' => 'branjangan-java-ring',
        ], [
            'nama_kategori' => 'Branjangan Java Ring',
            'jenis_burung' => 'Branjangan',
            'biaya' => 200000,
            'kuota' => 36,
            'jam_mulai' => '16:30',
            'jam_selesai' => '17:30',
            'status_lomba' => 'upcoming',
        ]);
        $ev4KatBranjangan->juris()->sync([$juris[3]->id, $juris[8]->id]);
    }
}
