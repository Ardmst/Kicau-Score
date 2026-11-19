<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pendaftaran extends Model
{
    use HasFactory;

    protected $table = 'pendaftaran';

    protected $fillable = [
        'kategori_lomba_id',
        'burung_id',
        'user_id',
        'nomor_gantangan',
        'metode_pembayaran',
        'status_pembayaran',
        'bukti_pembayaran',
        'total_skor_akhir',
        'rata_audio',
        'rata_visual',
        'peringkat',
    ];

    protected function casts(): array
    {
        return [
            'nomor_gantangan' => 'integer',
            'total_skor_akhir' => 'decimal:2',
            'rata_audio' => 'decimal:2',
            'rata_visual' => 'decimal:2',
            'peringkat' => 'integer',
        ];
    }

    public function kategoriLomba(): BelongsTo
    {
        return $this->belongsTo(KategoriLomba::class, 'kategori_lomba_id');
    }

    public function burung(): BelongsTo
    {
        return $this->belongsTo(Burung::class, 'burung_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function penilaians(): HasMany
    {
        return $this->hasMany(Penilaian::class, 'pendaftaran_id');
    }
}
