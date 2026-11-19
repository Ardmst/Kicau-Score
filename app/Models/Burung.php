<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Burung extends Model
{
    use HasFactory;

    protected $table = 'burungs';

    protected $fillable = [
        'user_id',
        'nama_burung',
        'jenis_burung',
        'no_ring',
        'foto',
        'catatan',
        'slug',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::creating(function (Burung $burung) {
            if (empty($burung->slug)) {
                $baseSlug = Str::slug($burung->nama_burung);
                $slug = $baseSlug;
                $counter = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }
                $burung->slug = $slug;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pendaftarans(): HasMany
    {
        return $this->hasMany(Pendaftaran::class);
    }
}
