<?php

namespace App\Services;

use App\Models\KategoriLomba;
use App\Models\Pendaftaran;
use App\Models\Penilaian;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ScoringEngineService
{
    /**
     * Hitung rata-rata akumulatif nilai dari seluruh juri untuk setiap burung di kategori.
     */
    public function hitungHasilKategori(int $kategoriId): void
    {
        DB::transaction(function () use ($kategoriId) {
            $daftarPendaftaran = Pendaftaran::where('kategori_lomba_id', $kategoriId)
                ->where('status_pembayaran', 'verified')
                ->get();

            foreach ($daftarPendaftaran as $pendaftaran) {
                // Ambil rata-rata dari seluruh juri yang menilai pendaftaran ini
                $stats = Penilaian::where('pendaftaran_id', $pendaftaran->id)
                    ->selectRaw('
                        AVG(total_skor_juri) as final_score,
                        AVG(skor_audio) as avg_audio,
                        AVG(skor_visual) as avg_visual
                    ')
                    ->first();

                $pendaftaran->update([
                    'total_skor_akhir' => $stats?->final_score !== null ? round((float) $stats->final_score, 2) : null,
                    'rata_audio' => $stats?->avg_audio !== null ? round((float) $stats->avg_audio, 2) : null,
                    'rata_visual' => $stats?->avg_visual !== null ? round((float) $stats->avg_visual, 2) : null,
                ]);
            }

            // Setelah agregasi rata-rata, tentukan peringkat berdasarkan Tie-Breaker
            $this->kalkulasiPeringkat($kategoriId);
        });
    }

    /**
     * Tentukan urutan juara secara berjenjang (4-Tier Tie-Breaker):
     * 1. total_skor_akhir (DESC)
     * 2. rata_audio (DESC) - Tie-Breaker 1
     * 3. rata_visual (DESC) - Tie-Breaker 2
     * 4. nomor_gantangan (ASC) - Fallback
     */
    public function kalkulasiPeringkat(int $kategoriId): void
    {
        $klasemen = Pendaftaran::where('kategori_lomba_id', $kategoriId)
            ->where('status_pembayaran', 'verified')
            ->whereNotNull('total_skor_akhir')
            ->orderByDesc('total_skor_akhir')
            ->orderByDesc('rata_audio')
            ->orderByDesc('rata_visual')
            ->orderBy('nomor_gantangan')
            ->get();

        $rank = 1;
        foreach ($klasemen as $item) {
            $item->update(['peringkat' => $rank++]);
        }
    }

    /**
     * Kunci seluruh nilai juri dan ubah status kategori ke 'validasi' atau 'finished'.
     */
    public function validasiDanKunciKategori(int $kategoriId, string $statusTarget = 'validasi'): void
    {
        DB::transaction(function () use ($kategoriId, $statusTarget) {
            // Hitung skor akhir & peringkat sebelum penguncian
            $this->hitungHasilKategori($kategoriId);

            // Lock seluruh penilaian milik kategori ini
            Penilaian::whereHas('pendaftaran', function ($q) use ($kategoriId) {
                $q->where('kategori_lomba_id', $kategoriId);
            })->update(['status_validasi' => 'validated']);

            // Update status lomba kategori
            KategoriLomba::where('id', $kategoriId)->update([
                'status_lomba' => $statusTarget,
            ]);
        });
    }

    /**
     * Ambil data klasemen leaderboard publik sesuai hirarki penentu juara.
     */
    public function getKlasemenPublic(int $kategoriId): Collection
    {
        return Pendaftaran::with(['burung.user', 'user', 'kategoriLomba.event', 'penilaians.juri'])
            ->where('kategori_lomba_id', $kategoriId)
            ->where('status_pembayaran', 'verified')
            ->whereNotNull('total_skor_akhir')
            ->orderByDesc('total_skor_akhir')
            ->orderByDesc('rata_audio')
            ->orderByDesc('rata_visual')
            ->orderBy('nomor_gantangan')
            ->get();
    }
}
