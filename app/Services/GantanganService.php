<?php

namespace App\Services;

use App\Models\Pendaftaran;
use Illuminate\Support\Facades\DB;

class GantanganService
{
    /**
     * Verifikasi pembayaran peserta dan alokasikan nomor gantangan secara atomic (concurrency-safe).
     */
    public function verifikasiPembayaran(Pendaftaran $pendaftaran): Pendaftaran
    {
        return DB::transaction(function () use ($pendaftaran) {
            // Reload with lock
            $lockedPendaftaran = Pendaftaran::where('id', $pendaftaran->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPendaftaran->status_pembayaran === 'verified' && ! empty($lockedPendaftaran->nomor_gantangan)) {
                return $lockedPendaftaran;
            }

            // Ambil nomor gantangan tertinggi di kategori ini dengan pessimist lock
            $lastGantangan = Pendaftaran::where('kategori_lomba_id', $lockedPendaftaran->kategori_lomba_id)
                ->whereNotNull('nomor_gantangan')
                ->lockForUpdate()
                ->max('nomor_gantangan');

            $nomorBaru = $lastGantangan ? $lastGantangan + 1 : 1;

            $lockedPendaftaran->update([
                'status_pembayaran' => 'verified',
                'nomor_gantangan' => $nomorBaru,
            ]);

            return $lockedPendaftaran;
        });
    }

    /**
     * Tolak pembayaran peserta dan rilis slot gantangan jika ada.
     */
    public function tolakPembayaran(Pendaftaran $pendaftaran): Pendaftaran
    {
        return DB::transaction(function () use ($pendaftaran) {
            $lockedPendaftaran = Pendaftaran::where('id', $pendaftaran->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedPendaftaran->update([
                'status_pembayaran' => 'rejected',
                'nomor_gantangan' => null,
            ]);

            return $lockedPendaftaran;
        });
    }
}
