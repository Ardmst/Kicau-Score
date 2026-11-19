<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenilaianLog extends Model
{
    use HasFactory;

    protected $table = 'penilaian_logs';

    protected $fillable = [
        'penilaian_id',
        'juri_id',
        'skor_visual_lama',
        'skor_visual_baru',
        'skor_audio_lama',
        'skor_audio_baru',
        'alasan',
    ];

    protected function casts(): array
    {
        return [
            'skor_visual_lama' => 'decimal:2',
            'skor_visual_baru' => 'decimal:2',
            'skor_audio_lama' => 'decimal:2',
            'skor_audio_baru' => 'decimal:2',
        ];
    }

    public function penilaian(): BelongsTo
    {
        return $this->belongsTo(Penilaian::class, 'penilaian_id');
    }

    public function juri(): BelongsTo
    {
        return $this->belongsTo(User::class, 'juri_id');
    }
}
