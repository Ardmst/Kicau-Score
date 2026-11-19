<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriLomba extends Model
{
    use HasFactory;

    protected $table = 'kategori_lomba';

    public const JENIS_BURUNG_LIST = [
        'Murai Batu',
        'Cucak Ijo',
        'Kacer',
        'Lovebird',
        'Kenari',
        'Cendet',
        'Anis Merah',
        'Anis Kembang',
        'Branjangan',
    ];

    protected $fillable = [
        'event_id',
        'nama_kategori',
        'jenis_burung',
        'slug',
        'biaya',
        'kuota',
        'jam_mulai',
        'jam_selesai',
        'status_lomba',
    ];

    protected function casts(): array
    {
        return [
            'biaya' => 'decimal:2',
            'kuota' => 'integer',
        ];
    }

    public function getExpectedJenisBurungAttribute(): ?string
    {
        if (! empty($this->attributes['jenis_burung'])) {
            return $this->attributes['jenis_burung'];
        }

        $nama = strtolower($this->nama_kategori);
        if (str_contains($nama, 'murai')) {
            return 'Murai Batu';
        }
        if (str_contains($nama, 'lovebird')) {
            return 'Lovebird';
        }
        if (str_contains($nama, 'cucak')) {
            return 'Cucak Ijo';
        }
        if (str_contains($nama, 'kacer')) {
            return 'Kacer';
        }
        if (str_contains($nama, 'kenari')) {
            return 'Kenari';
        }
        if (str_contains($nama, 'anis')) {
            return 'Anis Merah';
        }

        return null;
    }

    public function matchesBurung(Burung $burung): bool
    {
        $expected = strtolower(trim($this->expected_jenis_burung ?? ''));
        $actual = strtolower(trim($burung->jenis_burung ?? ''));

        if (! $expected || ! $actual) {
            return true;
        }

        return str_contains($expected, $actual) || str_contains($actual, $expected);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function pendaftarans(): HasMany
    {
        return $this->hasMany(Pendaftaran::class);
    }

    public function juris(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'kategori_juri', 'kategori_lomba_id', 'user_id')
            ->withTimestamps();
    }
}
