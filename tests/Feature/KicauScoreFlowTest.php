<?php

use App\Models\Burung;
use App\Models\Event;
use App\Models\KategoriLomba;
use App\Models\Pendaftaran;
use App\Models\Penilaian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::create([
        'name' => 'Admin Kicau',
        'email' => 'admin@test.com',
        'phone' => '081200000001',
        'password' => Hash::make('password'),
        'role' => 'admin',
    ]);

    $this->juri = User::create([
        'name' => 'Juri Lapangan',
        'email' => 'juri@test.com',
        'phone' => '081200000002',
        'password' => Hash::make('password'),
        'role' => 'juri',
    ]);

    $this->peserta = User::create([
        'name' => 'Peserta Test',
        'email' => 'peserta@test.com',
        'phone' => '081200000003',
        'password' => Hash::make('password'),
        'role' => 'peserta',
    ]);

    $this->event = Event::create([
        'nama_event' => 'Piala Nusantara',
        'tanggal' => now()->toDateString(),
        'lokasi' => 'Solo',
        'status' => 'active',
    ]);

    $this->kategori = KategoriLomba::create([
        'event_id' => $this->event->id,
        'nama_kategori' => 'Murai Batu VIP',
        'slug' => 'murai-batu-vip',
        'biaya' => 250000,
        'kuota' => 36,
        'status_lomba' => 'ongoing',
    ]);

    $this->burung = Burung::create([
        'user_id' => $this->peserta->id,
        'nama_burung' => 'Garuda Sakti',
        'jenis_burung' => 'Murai Batu',
        'slug' => 'garuda-sakti',
    ]);
});

test('public pages and json api response for burung slug work correctly', function () {
    // Landing page
    $response = $this->get(route('home'));
    $response->assertStatus(200);
    $response->assertSee('Piala Nusantara');

    // Scoreboard
    $response = $this->get(route('scoreboard'));
    $response->assertStatus(200);

    // Burung Web Profile
    $response = $this->get(route('burung.profile', 'garuda-sakti'));
    $response->assertStatus(200);
    $response->assertSee('Garuda Sakti');

    // Burung JSON API matching specification
    $apiResponse = $this->getJson(route('api.burung.profile', 'garuda-sakti'));
    $apiResponse->assertStatus(200);
    $apiResponse->assertJsonStructure([
        'status',
        'data' => [
            'nama_burung',
            'slug',
            'pemilik',
            'riwayat_lomba',
        ],
    ]);
    $apiResponse->assertJson([
        'status' => 'success',
        'data' => [
            'nama_burung' => 'Garuda Sakti',
            'slug' => 'garuda-sakti',
            'pemilik' => 'Peserta Test',
        ],
    ]);
});

test('peserta registration enforces single-category bird binding', function () {
    $this->actingAs($this->peserta);

    // Pendaftaran Pertama
    $response = $this->post(route('peserta.daftar.store'), [
        'kategori_lomba_id' => $this->kategori->id,
        'burung_id' => $this->burung->id,
        'metode_pembayaran' => 'transfer',
    ]);

    $response->assertRedirect(route('peserta.dashboard'));
    expect(Pendaftaran::count())->toBe(1);

    // Pendaftaran Kedua dengan burung yang sama (Single-Category Bird Binding constraint)
    $kategori2 = KategoriLomba::create([
        'event_id' => $this->event->id,
        'nama_kategori' => 'Murai Batu Umum',
        'slug' => 'murai-batu-umum',
        'biaya' => 200000,
        'kuota' => 36,
        'status_lomba' => 'upcoming',
    ]);

    $response2 = $this->post(route('peserta.daftar.store'), [
        'kategori_lomba_id' => $kategori2->id,
        'burung_id' => $this->burung->id,
        'metode_pembayaran' => 'cash',
    ]);

    $response2->assertSessionHasErrors('burung_id');
    expect(Pendaftaran::count())->toBe(1); // Tetap 1, pendaftaran ganda ditolak
});

test('peserta cannot register bird with mismatched species for category', function () {
    $this->actingAs($this->peserta);

    $lovebird = Burung::create([
        'user_id' => $this->peserta->id,
        'nama_burung' => 'Sweet Candy',
        'jenis_burung' => 'Lovebird',
        'slug' => 'sweet-candy',
    ]);

    // Coba daftarkan burung Lovebird ke kategori Murai Batu VIP
    $response = $this->post(route('peserta.daftar.store'), [
        'kategori_lomba_id' => $this->kategori->id, // Murai Batu VIP
        'burung_id' => $lovebird->id,
        'metode_pembayaran' => 'qris',
    ]);

    $response->assertSessionHasErrors('burung_id');
    expect(Pendaftaran::count())->toBe(0);
});

test('admin can verify payment and assign gantangan number safely', function () {
    $pendaftaran = Pendaftaran::create([
        'kategori_lomba_id' => $this->kategori->id,
        'burung_id' => $this->burung->id,
        'user_id' => $this->peserta->id,
        'metode_pembayaran' => 'transfer',
        'status_pembayaran' => 'pending',
    ]);

    $this->actingAs($this->admin);

    $response = $this->post(route('admin.verifikasi.acc', $pendaftaran->id));
    $response->assertSessionHas('success');

    $pendaftaran->refresh();
    expect($pendaftaran->status_pembayaran)->toBe('verified')
        ->and($pendaftaran->nomor_gantangan)->toBe(1);
});

test('juri can input score and controlled revision records audit log', function () {
    $pendaftaran = Pendaftaran::create([
        'kategori_lomba_id' => $this->kategori->id,
        'burung_id' => $this->burung->id,
        'user_id' => $this->peserta->id,
        'nomor_gantangan' => 1,
        'status_pembayaran' => 'verified',
    ]);

    $this->actingAs($this->juri);

    // Simpan nilai pertama
    $response = $this->post(route('juri.penilaian.store', $pendaftaran->id), [
        'skor_visual' => 90.00,
        'skor_audio' => 92.00,
        'catatan' => 'Materi suara bagus',
    ]);

    $response->assertSessionHas('success');

    $penilaian = Penilaian::where('pendaftaran_id', $pendaftaran->id)->first();
    expect((float) $penilaian->total_skor_juri)->toBe(182.00)
        ->and($penilaian->logs()->count())->toBe(0);

    // Revisi nilai (mencatat audit trail)
    $responseRev = $this->post(route('juri.penilaian.store', $pendaftaran->id), [
        'skor_visual' => 92.00,
        'skor_audio' => 94.00,
        'alasan_revisi' => 'Koreksi durasi kerja',
    ]);

    $responseRev->assertSessionHas('success');

    $penilaian->refresh();
    expect((float) $penilaian->total_skor_juri)->toBe(186.00)
        ->and($penilaian->logs()->count())->toBe(1);

    $log = $penilaian->logs()->first();
    expect((float) $log->skor_visual_lama)->toBe(90.00)
        ->and((float) $log->skor_visual_baru)->toBe(92.00)
        ->and($log->alasan)->toBe('Koreksi durasi kerja');
});

test('user can register with only name, phone and password without email', function () {
    $response = $this->post(route('register'), [
        'name' => 'Fauzi Rahman',
        'phone' => '087899887766',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();

    $user = User::where('phone', '087899887766')->first();
    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Fauzi Rahman')
        ->and($user->email)->toBeNull()
        ->and($user->role)->toBe('peserta');
});

test('user can login using phone number', function () {
    User::create([
        'name' => 'Dwi Prasetyo',
        'phone' => '085600001122',
        'password' => Hash::make('mypassword'),
        'role' => 'peserta',
    ]);

    $response = $this->post(route('login'), [
        'login' => '085600001122',
        'password' => 'mypassword',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
});

test('peserta automated payment flow via QRIS allocates gantangan immediately', function () {
    $this->actingAs($this->peserta);

    $response = $this->postJson(route('peserta.daftar.store'), [
        'kategori_lomba_id' => $this->kategori->id,
        'burung_id' => $this->burung->id,
        'metode_pembayaran' => 'qris',
        'auto_verify' => 1,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'status' => 'success',
        'nomor_gantangan' => 1,
        'nomor_gantangan_badge' => 'G-01',
    ]);

    $pendaftaran = Pendaftaran::where('burung_id', $this->burung->id)->first();
    expect($pendaftaran->status_pembayaran)->toBe('verified')
        ->and($pendaftaran->nomor_gantangan)->toBe(1)
        ->and($pendaftaran->metode_pembayaran)->toBe('qris');
});

test('admin can update category with unified settings and assign juries', function () {
    $this->actingAs($this->admin);

    $response = $this->put(route('admin.kategori.update', $this->kategori), [
        'nama_kategori' => 'Murai Batu VIP Updated',
        'jenis_burung' => 'Murai Batu',
        'biaya' => 300000,
        'kuota' => 24,
        'jam_mulai' => '09:30',
        'jam_selesai' => '11:00',
        'status_lomba' => 'ongoing',
        'juri_ids' => [$this->juri->id],
    ]);

    $response->assertRedirect();
    $this->kategori->refresh();
    expect($this->kategori->nama_kategori)->toBe('Murai Batu VIP Updated')
        ->and((int) $this->kategori->biaya)->toBe(300000)
        ->and($this->kategori->kuota)->toBe(24)
        ->and($this->kategori->status_lomba)->toBe('ongoing')
        ->and($this->kategori->juris->pluck('id')->toArray())->toContain($this->juri->id);
});

test('admin can delete event along with categories', function () {
    $this->actingAs($this->admin);

    $response = $this->delete(route('admin.events.destroy', $this->event));

    $response->assertRedirect(route('admin.events.index'));
    expect(Event::find($this->event->id))->toBeNull();
});
