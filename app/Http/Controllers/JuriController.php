<?php

namespace App\Http\Controllers;

use App\Models\KategoriLomba;
use App\Models\Pendaftaran;
use App\Models\Penilaian;
use App\Models\PenilaianLog;
use App\Services\ScoringEngineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class JuriController extends Controller
{
    public function __construct(
        protected ScoringEngineService $scoringService
    ) {}

    public function dashboard(Request $request): View
    {
        $juri = Auth::user();

        // Cari kategori yang relevan untuk juri ini
        $kategoriQuery = KategoriLomba::with(['event', 'juris'])
            ->whereIn('status_lomba', ['ongoing', 'upcoming', 'validasi', 'finished']);

        if (! $juri->isAdmin()) {
            $kategoriQuery->where(function ($q) use ($juri) {
                $q->whereDoesntHave('juris')
                    ->orWhereHas('juris', function ($sub) use ($juri) {
                        $sub->where('users.id', $juri->id);
                    });
            });
        }

        $kategoriList = $kategoriQuery->get();

        $selectedKategoriId = $request->query('kategori_id');
        $activeKategori = null;

        if ($selectedKategoriId) {
            $activeKategori = $kategoriList->firstWhere('id', $selectedKategoriId);
        }

        if (! $activeKategori) {
            $activeKategori = $kategoriList->firstWhere('status_lomba', 'ongoing')
                ?? $kategoriList->first();
        }

        $pendaftarans = collect();
        $penilaianByPendaftaran = collect();

        if ($activeKategori) {
            $pendaftarans = Pendaftaran::with('burung')
                ->where('kategori_lomba_id', $activeKategori->id)
                ->where('status_pembayaran', 'verified')
                ->whereNotNull('nomor_gantangan')
                ->orderBy('nomor_gantangan')
                ->get();

            $pendaftaranIds = $pendaftarans->pluck('id');
            $penilaianByPendaftaran = Penilaian::whereIn('pendaftaran_id', $pendaftaranIds)
                ->where('juri_id', $juri->id)
                ->get()
                ->keyBy('pendaftaran_id');
        }

        return view('juri.dashboard', compact(
            'juri',
            'kategoriList',
            'activeKategori',
            'pendaftarans',
            'penilaianByPendaftaran'
        ));
    }

    public function scoringForm(Pendaftaran $pendaftaran): View|RedirectResponse
    {
        $juri = Auth::user();
        $pendaftaran->load(['burung', 'kategoriLomba.event', 'kategoriLomba.juris']);

        if (! $juri->isAdmin() && $pendaftaran->kategoriLomba->juris->isNotEmpty() && ! $pendaftaran->kategoriLomba->juris->contains('id', $juri->id)) {
            return redirect()->route('juri.dashboard')
                ->with('error', "Anda tidak terdaftar sebagai dewan juri penilai untuk kategori '{$pendaftaran->kategoriLomba->nama_kategori}'.");
        }

        $penilaian = Penilaian::where('pendaftaran_id', $pendaftaran->id)
            ->where('juri_id', $juri->id)
            ->first();

        // Cari nomor gantangan berikutnya untuk fast-switch
        $nextPendaftaran = Pendaftaran::where('kategori_lomba_id', $pendaftaran->kategori_lomba_id)
            ->where('status_pembayaran', 'verified')
            ->where('nomor_gantangan', '>', $pendaftaran->nomor_gantangan)
            ->orderBy('nomor_gantangan')
            ->first();

        $prevPendaftaran = Pendaftaran::where('kategori_lomba_id', $pendaftaran->kategori_lomba_id)
            ->where('status_pembayaran', 'verified')
            ->where('nomor_gantangan', '<', $pendaftaran->nomor_gantangan)
            ->orderByDesc('nomor_gantangan')
            ->first();

        $canEdit = true;
        if ($penilaian && $penilaian->status_validasi === 'validated') {
            $canEdit = false;
        }
        if ($pendaftaran->kategoriLomba->status_lomba !== 'ongoing' && ! $juri->isAdmin()) {
            $canEdit = false;
        }

        return view('juri.scoring_form', compact(
            'juri',
            'pendaftaran',
            'penilaian',
            'nextPendaftaran',
            'prevPendaftaran',
            'canEdit'
        ));
    }

    public function storeOrUpdateScore(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $juri = Auth::user();

        $validated = $request->validate([
            'skor_visual' => ['required', 'numeric', 'between:1.00,100.00'],
            'skor_audio' => ['required', 'numeric', 'between:1.00,100.00'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'alasan_revisi' => ['nullable', 'string', 'max:255'],
        ]);

        $penilaian = Penilaian::where('pendaftaran_id', $pendaftaran->id)
            ->where('juri_id', $juri->id)
            ->first();

        $kategoriStatus = $pendaftaran->kategoriLomba->status_lomba ?? 'upcoming';

        if ($kategoriStatus !== 'ongoing' && ! $juri->isAdmin()) {
            return back()->with('error', 'Sesi penilaian untuk kategori ini tidak sedang berlangsung (ongoing).');
        }

        $pendaftaran->load('kategoriLomba.juris');
        if (! $juri->isAdmin() && $pendaftaran->kategoriLomba->juris->isNotEmpty() && ! $pendaftaran->kategoriLomba->juris->contains('id', $juri->id)) {
            return back()->with('error', "Anda tidak memiliki wewenang penilaian untuk kategori '{$pendaftaran->kategoriLomba->nama_kategori}'.");
        }

        if ($penilaian) {
            // Cek policy locking state
            if ($penilaian->status_validasi === 'validated' && ! $juri->isAdmin()) {
                return back()->with('error', 'Nilai sudah divalidasi dan dikunci oleh Panitia/Admin.');
            }

            // Catat audit trail perubahan skor jika nilai berubah
            if (
                (float) $penilaian->skor_visual !== (float) $validated['skor_visual'] ||
                (float) $penilaian->skor_audio !== (float) $validated['skor_audio']
            ) {
                PenilaianLog::create([
                    'penilaian_id' => $penilaian->id,
                    'juri_id' => $juri->id,
                    'skor_visual_lama' => $penilaian->skor_visual,
                    'skor_visual_baru' => $validated['skor_visual'],
                    'skor_audio_lama' => $penilaian->skor_audio,
                    'skor_audio_baru' => $validated['skor_audio'],
                    'alasan' => $validated['alasan_revisi'] ?? 'Revisi penilaian juri di lapangan.',
                ]);
            }

            $penilaian->update([
                'skor_visual' => $validated['skor_visual'],
                'skor_audio' => $validated['skor_audio'],
                'total_skor_juri' => (float) $validated['skor_visual'] + (float) $validated['skor_audio'],
                'catatan' => $validated['catatan'] ?? null,
            ]);
        } else {
            $penilaian = Penilaian::create([
                'pendaftaran_id' => $pendaftaran->id,
                'juri_id' => $juri->id,
                'skor_visual' => $validated['skor_visual'],
                'skor_audio' => $validated['skor_audio'],
                'total_skor_juri' => (float) $validated['skor_visual'] + (float) $validated['skor_audio'],
                'catatan' => $validated['catatan'] ?? null,
                'status_validasi' => 'draft',
            ]);
        }

        // Jalankan kalkulasi rata-rata realtime
        $this->scoringService->hitungHasilKategori($pendaftaran->kategori_lomba_id);

        $nextPendaftaran = Pendaftaran::where('kategori_lomba_id', $pendaftaran->kategori_lomba_id)
            ->where('status_pembayaran', 'verified')
            ->where('nomor_gantangan', '>', $pendaftaran->nomor_gantangan)
            ->orderBy('nomor_gantangan')
            ->first();

        if ($request->has('simpan_lanjut') && $nextPendaftaran) {
            return redirect()->route('juri.penilaian.form', $nextPendaftaran->id)
                ->with('success', "Nilai Gantangan G-{$pendaftaran->nomor_gantangan} tersimpan. Lanjut ke G-{$nextPendaftaran->nomor_gantangan}.");
        }

        return redirect()->route('juri.dashboard', ['kategori_id' => $pendaftaran->kategori_lomba_id])
            ->with('success', "Nilai Gantangan G-{$pendaftaran->nomor_gantangan} berhasil disimpan!");
    }
}
