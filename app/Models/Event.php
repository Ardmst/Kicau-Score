<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_event',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'lokasi',
        'deskripsi',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function kategoriLombas(): HasMany
    {
        return $this->hasMany(KategoriLomba::class);
    }

    public function pendaftarans(): HasManyThrough
    {
        return $this->hasManyThrough(Pendaftaran::class, KategoriLomba::class);
    }
}
