<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\KategoriLomba;
use App\Models\Pendaftaran;
use App\Models\User;
use App\Services\GantanganService;
use App\Services\ScoringEngineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(
        protected GantanganService $gantanganService,
        protected ScoringEngineService $scoringService
    ) {}

    public function dashboard(): View
    {
        $events = Event::with(['kategoriLombas' => function ($q) {
            $q->withCount(['pendaftarans as verified_count' => function ($sub) {
                $sub->where('status_pembayaran', 'verified');
            }]);
        }])->latest()->get();

        $activeEvent = $events->firstWhere('status', 'active') ?? $events->first();

        $totalPeserta = Pendaftaran::where('status_pembayaran', 'verified')->count();
        $totalPending = Pendaftaran::where('status_pembayaran', 'pending')->count();
        $totalKategori = KategoriLomba::count();
        $totalJuri = User::where('role', 'juri')->count();

        $recentPending = Pendaftaran::with(['burung', 'kategoriLomba.event', 'user'])
            ->where('status_pembayaran', 'pending')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'events',
            'activeEvent',
            'totalPeserta',
            'totalPending',
            'totalKategori',
            'totalJuri',
            'recentPending'
        ));
    }

    public function eventIndex(): View
    {
        KategoriLomba::syncScheduleStatus();

        $events = Event::with(['kategoriLombas' => function ($q) {
            $q->with('juris')->withCount(['pendaftarans as verified_count' => function ($sub) {
                $sub->where('status_pembayaran', 'verified');
            }]);
        }])->latest()->get();

        $allJuris = User::where('role', 'juri')->orderBy('id')->get();

        return view('admin.events_index', compact('events', 'allJuris'));
    }

    public function eventStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_event' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['nullable', 'string', 'max:10'],
            'jam_selesai' => ['nullable', 'string', 'max:10'],
            'lokasi' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,active,finished'],
        ]);

        Event::create($validated);

        return redirect()->route('admin.events.index')->with('success', 'Master Event berhasil dibuat!');
    }

    public function eventUpdate(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'nama_event' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['nullable', 'string', 'max:10'],
            'jam_selesai' => ['nullable', 'string', 'max:10'],
            'lokasi' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,active,finished'],
        ]);

        $event->update($validated);

        return redirect()->route('admin.events.index')->with('success', "Master Event '{$event->nama_event}' berhasil diperbarui!");
    }

    public function eventDestroy(Event $event): RedirectResponse
    {
        $nama = $event->nama_event;
        $event->delete();

        return redirect()->route('admin.events.index')->with('success', "Master Event '{$nama}' beserta kategorinya berhasil dihapus!");
    }

    public function kategoriStore(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:100'],
            'jenis_burung' => ['nullable', 'string', 'max:100'],
            'biaya' => ['required', 'numeric', 'min:0'],
            'kuota' => ['required', 'integer', 'min:1'],
            'jam_mulai' => ['nullable', 'string', 'max:10'],
            'jam_selesai' => ['nullable', 'string', 'max:10'],
            'status_lomba' => ['required', 'in:upcoming,ongoing,validasi,finished'],
            'juri_ids' => ['nullable', 'array'],
            'juri_ids.*' => ['exists:users,id'],
        ]);

        $slug = Str::slug($validated['nama_kategori']);
        $baseSlug = $slug;
        $counter = 1;
        while (KategoriLomba::where('event_id', $event->id)->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        $kategori = $event->kategoriLombas()->create([
            'nama_kategori' => $validated['nama_kategori'],
            'jenis_burung' => $validated['jenis_burung'] ?? null,
            'slug' => $slug,
            'biaya' => $validated['biaya'],
            'kuota' => $validated['kuota'],
            'jam_mulai' => $validated['jam_mulai'] ?? null,
            'jam_selesai' => $validated['jam_selesai'] ?? null,
            'status_lomba' => $validated['status_lomba'],
        ]);

        if (! empty($validated['juri_ids'])) {
            $kategori->juris()->sync($validated['juri_ids']);
        }

        return back()->with('success', "Kategori '{$validated['nama_kategori']}' berhasil ditambahkan ke event!");
    }

    public function kategoriUpdate(Request $request, KategoriLomba $kategori): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:100'],
            'jenis_burung' => ['nullable', 'string', 'max:100'],
            'biaya' => ['required', 'numeric', 'min:0'],
            'kuota' => ['required', 'integer', 'min:1'],
            'jam_mulai' => ['nullable', 'string', 'max:10'],
            'jam_selesai' => ['nullable', 'string', 'max:10'],
            'status_lomba' => ['required', 'in:upcoming,ongoing,validasi,finished'],
            'juri_ids' => ['nullable', 'array'],
            'juri_ids.*' => ['exists:users,id'],
        ]);

        $statusBaru = $validated['status_lomba'];

        $kategori->update([
            'nama_kategori' => $validated['nama_kategori'],
            'jenis_burung' => $validated['jenis_burung'] ?? null,
            'biaya' => $validated['biaya'],
            'kuota' => $validated['kuota'],
            'jam_mulai' => $validated['jam_mulai'] ?? null,
            'jam_selesai' => $validated['jam_selesai'] ?? null,
            'status_lomba' => $statusBaru,
        ]);

        // Sync juri yang ditugaskan
        $kategori->juris()->sync($validated['juri_ids'] ?? []);

        // Jika status berpindah ke validasi atau finished, otomatis hitung skor & kunci nilai juri
        if (in_array($statusBaru, ['validasi', 'finished'])) {
            $this->scoringService->validasiDanKunciKategori($kategori->id, $statusBaru);
        }

        return back()->with('success', "Kategori '{$kategori->nama_kategori}' berhasil diperbarui secara lengkap!");
    }

    public function kategoriDestroy(KategoriLomba $kategori): RedirectResponse
    {
        $nama = $kategori->nama_kategori;
        $kategori->juris()->detach();
        $kategori->delete();

        return back()->with('success', "Kategori '{$nama}' berhasil dihapus!");
    }

    public function updateKategoriJuris(Request $request, KategoriLomba $kategori): RedirectResponse
    {
        $validated = $request->validate([
            'juri_ids' => ['nullable', 'array'],
            'juri_ids.*' => ['exists:users,id'],
        ]);

        $kategori->juris()->sync($validated['juri_ids'] ?? []);

        return back()->with('success', "Penugasan dewan juri penilai untuk kategori '{$kategori->nama_kategori}' berhasil diperbarui!");
    }

    public function updateKategoriStatus(Request $request, KategoriLomba $kategori): RedirectResponse
    {
        $validated = $request->validate([
            'status_lomba' => ['required', 'in:upcoming,ongoing,validasi,finished'],
        ]);

        $statusLama = $kategori->status_lomba;
        $statusBaru = $validated['status_lomba'];

        // Jika status berpindah ke validasi atau finished, otomatis hitung skor & kunci nilai juri
        if (in_array($statusBaru, ['validasi', 'finished'])) {
            $this->scoringService->validasiDanKunciKategori($kategori->id, $statusBaru);
        } else {
            $kategori->update(['status_lomba' => $statusBaru]);
        }

        return back()->with('success', "Status kategori '{$kategori->nama_kategori}' berhasil diubah dari '{$statusLama}' menjadi '{$statusBaru}'.");
    }

    public function verifikasiQueue(Request $request): View
    {
        $status = $request->query('status', 'pending');

        $query = Pendaftaran::with(['burung', 'kategoriLomba.event', 'user']);

        if ($status !== 'all') {
            $query->where('status_pembayaran', $status);
        }

        $pendaftarans = $query->latest()->paginate(15)->withQueryString();

        $countPending = Pendaftaran::where('status_pembayaran', 'pending')->count();
        $countVerified = Pendaftaran::where('status_pembayaran', 'verified')->count();
        $countRejected = Pendaftaran::where('status_pembayaran', 'rejected')->count();

        return view('admin.verifikasi_queue', compact(
            'pendaftarans',
            'status',
            'countPending',
            'countVerified',
            'countRejected'
        ));
    }

    public function verifikasiAcc(Pendaftaran $pendaftaran): RedirectResponse
    {
        $verified = $this->gantanganService->verifikasiPembayaran($pendaftaran);

        return back()->with('success', "Pembayaran untuk Burung '{$verified->burung->nama_burung}' diverifikasi! Nomor Gantangan otomatis diterbitkan: G-".str_pad($verified->nomor_gantangan, 2, '0', STR_PAD_LEFT));
    }

    public function verifikasiReject(Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->gantanganService->tolakPembayaran($pendaftaran);

        return back()->with('success', "Pendaftaran untuk Burung '{$pendaftaran->burung->nama_burung}' telah ditolak dan slot gantangan dibebaskan.");
    }

    public function userIndex(Request $request): View
    {
        $role = $request->query('role');
        $q = $request->query('q');

        $query = User::query();

        if ($role && in_array($role, ['admin', 'juri', 'peserta'])) {
            $query->where('role', $role);
        }

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('kode_id', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $users = $query->latest()->paginate(20)->withQueryString();
        $totalAdmin = User::where('role', 'admin')->count();
        $totalJuri = User::where('role', 'juri')->count();
        $totalPeserta = User::where('role', 'peserta')->count();

        return view('admin.users_index', compact('users', 'totalAdmin', 'totalJuri', 'totalPeserta', 'role', 'q'));
    }

    public function userStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'role' => ['required', 'in:admin,juri,peserta'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $kodeId = User::generateKodeId($validated['role']);

        User::create([
            'kode_id' => $kodeId,
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', "Akun pengguna '{$validated['name']}' ({$kodeId}) dengan role {$validated['role']} berhasil dibuat!");
    }

    public function userUpdate(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone,'.$user->id],
            'role' => ['required', 'in:admin,juri,peserta'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'role' => $validated['role'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return back()->with('success', "Data pengguna '{$user->name}' berhasil diperbarui!");
    }

    public function userDestroy(Request $request, User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.');
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', "Akun pengguna '{$name}' berhasil dihapus dari sistem.");
    }

    public function createJuri(Request $request): RedirectResponse
    {
        return $this->userStore($request->merge(['role' => 'juri']));
    }
}
