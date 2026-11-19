<?php

use App\Models\Burung;
use App\Models\Event;
use App\Models\KategoriLomba;
use App\Models\Pendaftaran;
use App\Models\Penilaian;
use App\Models\User;
use App\Policies\PenilaianPolicy;
use App\Services\GantanganService;
use App\Services\ScoringEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::create([
        'name' => 'Admin Test',
        'email' => 'admin_test@kicauscore.com',
        'password' => Hash::make('password'),
        'role' => 'admin',
    ]);

    $this->juri1 = User::create([
        'name' => 'Juri 1',
        'email' => 'juri1_test@kicauscore.com',
        'password' => Hash::make('password'),
        'role' => 'juri',
    ]);

    $this->juri2 = User::create([
        'name' => 'Juri 2',
        'email' => 'juri2_test@kicauscore.com',
        'password' => Hash::make('password'),
        'role' => 'juri',
    ]);

    $this->peserta = User::create([
        'name' => 'Peserta Test',
        'email' => 'peserta_test@kicauscore.com',
        'password' => Hash::make('password'),
        'role' => 'peserta',
    ]);

    $this->event = Event::create([
        'nama_event' => 'Piala Raja 2026',
        'tanggal' => now()->toDateString(),
        'lokasi' => 'Yogyakarta',
        'status' => 'active',
    ]);

    $this->kategori = KategoriLomba::create([
        'event_id' => $this->event->id,
        'nama_kategori' => 'Murai Batu Ring VIP',
        'slug' => 'murai-batu-ring-vip',
        'biaya' => 300000,
        'kuota' => 36,
        'status_lomba' => 'ongoing',
    ]);
});

test('auto gantangan allocation assigns consecutive numbers concurrency-safe', function () {
    $burung1 = Burung::create([
        'user_id' => $this->peserta->id,
        'nama_burung' => 'Burung A',
    ]);

    $burung2 = Burung::create([
        'user_id' => $this->peserta->id,
        'nama_burung' => 'Burung B',
    ]);

    $pendaftaran1 = Pendaftaran::create([
        'kategori_lomba_id' => $this->kategori->id,
        'burung_id' => $burung1->id,
        'user_id' => $this->peserta->id,
        'metode_pembayaran' => 'transfer',
        'status_pembayaran' => 'pending',
    ]);

    $pendaftaran2 = Pendaftaran::create([
        'kategori_lomba_id' => $this->kategori->id,
        'burung_id' => $burung2->id,
        'user_id' => $this->peserta->id,
        'metode_pembayaran' => 'cash',
        'status_pembayaran' => 'pending',
    ]);

    $gantanganService = new GantanganService;

    $verified1 = $gantanganService->verifikasiPembayaran($pendaftaran1);
    expect($verified1->status_pembayaran)->toBe('verified')
        ->and($verified1->nomor_gantangan)->toBe(1);

    $verified2 = $gantanganService->verifikasiPembayaran($pendaftaran2);
    expect($verified2->status_pembayaran)->toBe('verified')
        ->and($verified2->nomor_gantangan)->toBe(2);
});

test('scoring engine calculates average score and applies tie-breaker correctly', function () {
    $burung1 = Burung::create(['user_id' => $this->peserta->id, 'nama_burung' => 'Burung Juara']);
    $burung2 = Burung::create(['user_id' => $this->peserta->id, 'nama_burung' => 'Burung Seri Audio Tinggi']);
    $burung3 = Burung::create(['user_id' => $this->peserta->id, 'nama_burung' => 'Burung Seri Audio Rendah']);

    $p1 = Pendaftaran::create([
        'kategori_lomba_id' => $this->kategori->id,
        'burung_id' => $burung1->id,
        'user_id' => $this->peserta->id,
        'nomor_gantangan' => 5,
        'status_pembayaran' => 'verified',
    ]);

    $p2 = Pendaftaran::create([
        'kategori_lomba_id' => $this->kategori->id,
        'burung_id' => $burung2->id,
        'user_id' => $this->peserta->id,
        'nomor_gantangan' => 10,
        'status_pembayaran' => 'verified',
    ]);

    $p3 = Pendaftaran::create([
        'kategori_lomba_id' => $this->kategori->id,
        'burung_id' => $burung3->id,
        'user_id' => $this->peserta->id,
        'nomor_gantangan' => 2,
        'status_pembayaran' => 'verified',
    ]);

    // P1: Nilai Tertinggi Mutlak
    Penilaian::create([
        'pendaftaran_id' => $p1->id,
        'juri_id' => $this->juri1->id,
        'skor_visual' => 95.00,
        'skor_audio' => 95.00,
        'total_skor_juri' => 190.00,
    ]);

    // P2 & P3: Total Skor Sama (180.00), tetapi P2 punya rata-rata Audio lebih tinggi (95 vs 85) -> Tie-Breaker 1
    Penilaian::create([
        'pendaftaran_id' => $p2->id,
        'juri_id' => $this->juri1->id,
        'skor_visual' => 85.00,
        'skor_audio' => 95.00,
        'total_skor_juri' => 180.00,
    ]);

    Penilaian::create([
        'pendaftaran_id' => $p3->id,
        'juri_id' => $this->juri1->id,
        'skor_visual' => 95.00,
        'skor_audio' => 85.00,
        'total_skor_juri' => 180.00,
    ]);

    $engine = new ScoringEngineService;
    $engine->hitungHasilKategori($this->kategori->id);

    $p1->refresh();
    $p2->refresh();
    $p3->refresh();

    expect((float) $p1->total_skor_akhir)->toBe(190.00)
        ->and($p1->peringkat)->toBe(1)
        ->and((float) $p2->total_skor_akhir)->toBe(180.00)
        ->and($p2->peringkat)->toBe(2)
        ->and((float) $p3->total_skor_akhir)->toBe(180.00)
        ->and($p3->peringkat)->toBe(3);
});

test('admin locking state locks all scores to validated and prevents juri editing', function () {
    $burung = Burung::create(['user_id' => $this->peserta->id, 'nama_burung' => 'Burung Lock']);

    $p = Pendaftaran::create([
        'kategori_lomba_id' => $this->kategori->id,
        'burung_id' => $burung->id,
        'user_id' => $this->peserta->id,
        'nomor_gantangan' => 1,
        'status_pembayaran' => 'verified',
    ]);

    $penilaian = Penilaian::create([
        'pendaftaran_id' => $p->id,
        'juri_id' => $this->juri1->id,
        'skor_visual' => 90.00,
        'skor_audio' => 90.00,
        'total_skor_juri' => 180.00,
        'status_validasi' => 'draft',
    ]);

    // Check policy before lock
    $policy = new PenilaianPolicy;
    expect($policy->update($this->juri1, $penilaian))->toBeTrue();

    // Admin locks category
    $engine = new ScoringEngineService;
    $engine->validasiDanKunciKategori($this->kategori->id, 'validasi');

    $penilaian->refresh();
    expect($penilaian->status_validasi)->toBe('validated');

    // Juri cannot update after lock
    expect($policy->update($this->juri1, $penilaian))->toBeFalse();
});

test('user model formats role-based kode_id and generates next sequence', function () {
    $adminCode = User::generateKodeId('admin');
    $juriCode = User::generateKodeId('juri');
    $pesertaCode = User::generateKodeId('peserta');

    expect($adminCode)->toStartWith('ADM-')
        ->and($juriCode)->toStartWith('JUR-')
        ->and($pesertaCode)->toStartWith('PST-');

    $newUser = User::create([
        'kode_id' => $juriCode,
        'name' => 'Juri Spesialis Kenari',
        'phone' => '089988776655',
        'password' => bcrypt('password'),
        'role' => 'juri',
    ]);

    expect($newUser->kode_id)->toBe($juriCode);
});

test('kategori lomba assigns dewan juri panel and verifies relationship', function () {
    $this->kategori->juris()->sync([$this->juri1->id]);

    expect($this->kategori->juris)->toHaveCount(1)
        ->and($this->kategori->juris->first()->id)->toBe($this->juri1->id);
});
