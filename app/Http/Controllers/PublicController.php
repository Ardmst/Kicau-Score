<?php

namespace App\Http\Controllers;

use App\Models\Burung;
use App\Models\Event;
use App\Models\KategoriLomba;
use App\Services\ScoringEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function __construct(
        protected ScoringEngineService $scoringService
    ) {}

    public function index(Request $request): View
    {
        $event = Event::with(['kategoriLombas' => function ($q) {
            $q->withCount(['pendaftarans as total_terdaftar' => function ($sub) {
                $sub->where('status_pembayaran', 'verified');
            }]);
        }])
            ->where('status', '!=', 'draft')
            ->latest('tanggal')
            ->first() ?? Event::with('kategoriLombas')->latest()->first();

        $activeKategori = null;
        $leaderboard = collect();

        if ($event && $event->kategoriLombas->isNotEmpty()) {
            $kategoriId = $request->query('kategori_id');
            if ($kategoriId) {
                $activeKategori = $event->kategoriLombas->firstWhere('id', $kategoriId);
            }

            if (! $activeKategori) {
                // Prioritize ongoing, then validasi, then finished, then upcoming
                $activeKategori = $event->kategoriLombas->firstWhere('status_lomba', 'ongoing')
                    ?? $event->kategoriLombas->firstWhere('status_lomba', 'validasi')
                    ?? $event->kategoriLombas->firstWhere('status_lomba', 'finished')
                    ?? $event->kategoriLombas->first();
            }

            if ($activeKategori) {
                $leaderboard = $this->scoringService->getKlasemenPublic($activeKategori->id);
            }
        }

        return view('public.index', compact('event', 'activeKategori', 'leaderboard'));
    }

    public function scoreboard(Request $request, ?KategoriLomba $kategori = null): View
    {
        $event = $kategori ? $kategori->event : Event::where('status', 'active')->latest()->first();
        $categories = $event ? $event->kategoriLombas : KategoriLomba::all();

        $selectedKategori = $kategori ?? $categories->firstWhere('status_lomba', 'ongoing')
            ?? $categories->firstWhere('status_lomba', 'validasi')
            ?? $categories->firstWhere('status_lomba', 'finished')
            ?? $categories->first();

        $leaderboard = $selectedKategori
            ? $this->scoringService->getKlasemenPublic($selectedKategori->id)
            : collect();

        return view('public.scoreboard', compact('event', 'categories', 'selectedKategori', 'leaderboard'));
    }

    public function apiScoreboard(Request $request, ?KategoriLomba $kategori = null): JsonResponse
    {
        $selectedKategori = $kategori;
        if (! $selectedKategori) {
            $kategoriId = $request->query('kategori_id');
            if ($kategoriId) {
                $selectedKategori = KategoriLomba::find($kategoriId);
            }
        }

        if (! $selectedKategori) {
            $selectedKategori = KategoriLomba::where('status_lomba', 'ongoing')->first()
                ?? KategoriLomba::where('status_lomba', 'validasi')->first()
                ?? KategoriLomba::where('status_lomba', 'finished')->first()
                ?? KategoriLomba::first();
        }

        if (! $selectedKategori) {
            return response()->json([
                'status' => 'empty',
                'leaderboard' => [],
                'updated_at' => now()->format('H:i:s').' WIB',
            ]);
        }

        $leaderboard = $this->scoringService->getKlasemenPublic($selectedKategori->id);

        $data = $leaderboard->map(function ($item, $index) {
            $rank = $item->peringkat ?? ($index + 1);

            return [
                'peringkat' => $rank,
                'is_top3' => $rank <= 3,
                'nomor_gantangan' => 'G-'.str_pad($item->nomor_gantangan, 2, '0', STR_PAD_LEFT),
                'nama_burung' => $item->burung->nama_burung ?? '-',
                'slug_burung' => $item->burung->slug ?? '',
                'jenis_burung' => $item->burung->jenis_burung ?? 'Murai Batu',
                'no_ring' => $item->burung->no_ring ?? '-',
                'pemilik' => $item->user->name ?? '-',
                'pemilik_id' => $item->user->id ?? 0,
                'rata_visual' => number_format((float) ($item->rata_visual ?? 0), 2),
                'rata_audio' => number_format((float) ($item->rata_audio ?? 0), 2),
                'total_skor_akhir' => number_format((float) ($item->total_skor_akhir ?? 0), 2),
                'profile_url' => route('burung.profile', $item->burung->slug ?? ''),
            ];
        });

        return response()->json([
            'status' => 'success',
            'kategori_id' => $selectedKategori->id,
            'kategori_nama' => $selectedKategori->nama_kategori,
            'status_lomba' => $selectedKategori->status_lomba,
            'leaderboard' => $data,
            'updated_at' => now()->format('H:i:s').' WIB',
        ]);
    }

    public function burung(Request $request, Burung $burung): View|JsonResponse
    {
        $burung->load(['user', 'pendaftarans' => function ($q) {
            $q->with(['kategoriLomba.event', 'penilaians.juri'])
                ->orderByDesc('created_at');
        }]);

        // Support standard API response according to KicauScore.md specification
        if ($request->wantsJson() || $request->is('api/*')) {
            $history = $burung->pendaftarans->map(function ($p) {
                return [
                    'nama_kategori' => $p->kategoriLomba->nama_kategori ?? '-',
                    'nomor_gantangan' => $p->nomor_gantangan,
                    'total_skor' => (float) ($p->total_skor_akhir ?? 0),
                    'rata_audio' => (float) ($p->rata_audio ?? 0),
                    'rata_visual' => (float) ($p->rata_visual ?? 0),
                    'peringkat' => $p->peringkat,
                    'status_lomba' => $p->kategoriLomba->status_lomba ?? 'upcoming',
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'nama_burung' => $burung->nama_burung,
                    'slug' => $burung->slug,
                    'pemilik' => $burung->user->name ?? '-',
                    'riwayat_lomba' => $history,
                ],
            ]);
        }

        return view('public.burung_profile', compact('burung'));
    }
}
