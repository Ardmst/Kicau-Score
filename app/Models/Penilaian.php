<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penilaian extends Model
{
    use HasFactory;

    protected $table = 'penilaian';

    protected $fillable = [
        'pendaftaran_id',
        'juri_id',
        'skor_visual',
        'skor_audio',
        'total_skor_juri',
        'catatan',
        'status_validasi',
    ];

    protected function casts(): array
    {
        return [
            'skor_visual' => 'decimal:2',
            'skor_audio' => 'decimal:2',
            'total_skor_juri' => 'decimal:2',
        ];
    }

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }

    public function juri(): BelongsTo
    {
        return $this->belongsTo(User::class, 'juri_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PenilaianLog::class, 'penilaian_id');
    }
}
