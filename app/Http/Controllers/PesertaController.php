<?php

namespace App\Http\Controllers;

use App\Models\Burung;
use App\Models\Event;
use App\Models\KategoriLomba;
use App\Models\Pendaftaran;
use App\Services\GantanganService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PesertaController extends Controller
{
    public function __construct(
        protected GantanganService $gantanganService
    ) {}

    public function dashboard(): View
    {
        $user = Auth::user();

        $burungs = $user->burungs()->withCount('pendaftarans')->latest()->get();

        $pendaftarans = $user->pendaftarans()
            ->with(['kategoriLomba.event', 'burung'])
            ->latest()
            ->get();

        return view('peserta.dashboard', compact('user', 'burungs', 'pendaftarans'));
    }

    public function burungIndex(): View
    {
        $user = Auth::user();
        $burungs = $user->burungs()->withCount('pendaftarans')->latest()->get();

        return view('peserta.burung_index', compact('user', 'burungs'));
    }

    public function burungStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_burung' => ['required', 'string', 'max:100'],
            'jenis_burung' => ['required', 'string', 'max:100'],
            'no_ring' => ['nullable', 'string', 'max:100'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('burung_photos', 'public');
        }

        Auth::user()->burungs()->create([
            'nama_burung' => $validated['nama_burung'],
            'jenis_burung' => $validated['jenis_burung'],
            'no_ring' => $validated['no_ring'] ?? null,
            'catatan' => $validated['catatan'] ?? null,
            'foto' => $fotoPath,
        ]);

        return redirect()->route('peserta.burung.index')->with('success', 'Burung berhasil ditambahkan ke koleksi Anda!');
    }

    public function burungUpdate(Request $request, Burung $burung): RedirectResponse
    {
        if ($burung->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'nama_burung' => ['required', 'string', 'max:100'],
            'jenis_burung' => ['required', 'string', 'max:100'],
            'no_ring' => ['nullable', 'string', 'max:100'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $fotoPath = $burung->foto;
        if ($request->hasFile('foto')) {
            if ($fotoPath && Storage::disk('public')->exists($fotoPath)) {
                Storage::disk('public')->delete($fotoPath);
            }
            $fotoPath = $request->file('foto')->store('burung_photos', 'public');
        }

        $burung->update([
            'nama_burung' => $validated['nama_burung'],
            'jenis_burung' => $validated['jenis_burung'],
            'no_ring' => $validated['no_ring'] ?? null,
            'catatan' => $validated['catatan'] ?? null,
            'foto' => $fotoPath,
        ]);

        return redirect()->route('peserta.burung.index')->with('success', 'Data burung berhasil diperbarui!');
    }

    public function burungDestroy(Burung $burung): RedirectResponse
    {
        if ($burung->user_id !== Auth::id()) {
            abort(403);
        }

        if ($burung->foto && Storage::disk('public')->exists($burung->foto)) {
            Storage::disk('public')->delete($burung->foto);
        }

        $burung->delete();

        return redirect()->route('peserta.burung.index')->with('success', 'Burung berhasil dihapus dari koleksi Anda.');
    }

    public function showDaftar(Request $request): View
    {
        $user = Auth::user();
        $burungs = $user->burungs()->get();

        $activeEvents = Event::with(['kategoriLombas' => function ($q) {
            $q->whereIn('status_lomba', ['upcoming', 'ongoing'])
                ->withCount(['pendaftarans as total_terdaftar' => function ($sub) {
                    $sub->where('status_pembayaran', 'verified');
                }]);
        }])
            ->where('status', '!=', 'finished')
            ->get();

        $selectedKategoriId = $request->query('kategori_id');

        return view('peserta.daftar', compact('user', 'burungs', 'activeEvents', 'selectedKategoriId'));
    }

    public function storeDaftar(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'kategori_lomba_id' => ['required', 'exists:kategori_lomba,id'],
            'burung_id' => ['required', 'exists:burungs,id'],
            'metode_pembayaran' => ['required', 'in:qris,transfer,cash'],
            'bukti_pembayaran' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
        ]);

        // Verifikasi kepemilikan burung
        $burung = Burung::where('id', $validated['burung_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Constraint: Single-Category Bird Binding (1 Burung hanya bisa didaftarkan pada 1 kategori)
        $alreadyRegistered = Pendaftaran::where('burung_id', $burung->id)->exists();
        if ($alreadyRegistered) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Burung '{$burung->nama_burung}' sudah pernah terdaftar pada salah satu kategori lomba (Single-Category Binding).",
                ], 422);
            }

            return back()->withErrors([
                'burung_id' => "Burung '{$burung->nama_burung}' sudah pernah terdaftar pada salah satu kategori lomba (Single-Category Binding).",
            ])->withInput();
        }

        // Cek kuota kategori
        $kategori = KategoriLomba::withCount(['pendaftarans as total_terdaftar' => function ($q) {
            $q->where('status_pembayaran', 'verified');
        }])->findOrFail($validated['kategori_lomba_id']);

        // Constraint 2: Bird Species Matching Category (Kategori Murai Batu khusus untuk Murai Batu, dsb)
        if (! $kategori->matchesBurung($burung)) {
            $expected = $kategori->expected_jenis_burung ?? 'jenis yang ditentukan';
            $msg = "Burung '{$burung->nama_burung}' berjenis '{$burung->jenis_burung}', sedangkan kategori lomba '{$kategori->nama_kategori}' khusus diperuntukkan untuk burung {$expected}.";

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $msg,
                ], 422);
            }

            return back()->withErrors([
                'burung_id' => $msg,
            ])->withInput();
        }

        if ($kategori->total_terdaftar >= $kategori->kuota) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Maaf, kuota nomor gantangan untuk kategori ini sudah penuh!',
                ], 422);
            }

            return back()->withErrors([
                'kategori_lomba_id' => 'Maaf, kuota nomor gantangan untuk kategori ini sudah penuh!',
            ])->withInput();
        }

        $buktiPath = null;
        if ($request->hasFile('bukti_pembayaran')) {
            $buktiPath = $request->file('bukti_pembayaran')->store('bukti_transfer', 'public');
        }

        $pendaftaran = Pendaftaran::create([
            'kategori_lomba_id' => $kategori->id,
            'burung_id' => $burung->id,
            'user_id' => Auth::id(),
            'metode_pembayaran' => $validated['metode_pembayaran'],
            'status_pembayaran' => 'pending',
            'bukti_pembayaran' => $buktiPath,
        ]);

        // Simulasi Pembayaran Otomatis (QRIS / Transfer Auto-Settlement)
        if ($request->boolean('auto_verify')) {
            $pendaftaran = $this->gantanganService->verifikasiPembayaran($pendaftaran);

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Pembayaran berhasil diverifikasi secara instan!',
                    'nomor_gantangan' => $pendaftaran->nomor_gantangan,
                    'nomor_gantangan_badge' => 'G-'.str_pad($pendaftaran->nomor_gantangan, 2, '0', STR_PAD_LEFT),
                    'nama_burung' => $burung->nama_burung,
                    'kategori' => $kategori->nama_kategori,
                    'tiket_url' => route('peserta.tiket', $pendaftaran->id),
                    'dashboard_url' => route('peserta.dashboard'),
                ]);
            }

            return redirect()->route('peserta.tiket', $pendaftaran->id)
                ->with('success', "Pembayaran berhasil! Nomor gantangan resmi Anda: G-{$pendaftaran->nomor_gantangan}");
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Pendaftaran berhasil dikirim! Menunggu verifikasi pembayaran.',
                'tiket_url' => route('peserta.tiket', $pendaftaran->id),
                'dashboard_url' => route('peserta.dashboard'),
            ]);
        }

        return redirect()->route('peserta.dashboard')->with('success', 'Pendaftaran berhasil dikirim! Menunggu verifikasi pembayaran oleh Panitia untuk penerbitan nomor gantangan.');
    }

    public function tiket(Pendaftaran $pendaftaran): View
    {
        if ($pendaftaran->user_id !== Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403);
        }

        $pendaftaran->load(['burung', 'kategoriLomba.event', 'user']);

        return view('peserta.tiket', compact('pendaftaran'));
    }
}
