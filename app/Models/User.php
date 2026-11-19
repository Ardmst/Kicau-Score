<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'kode_id',
        'name',
        'email',
        'phone',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getKodeIdAttribute(?string $value): string
    {
        if (! empty($value)) {
            return $value;
        }

        $prefix = match ($this->role) {
            'admin' => 'ADM',
            'juri' => 'JUR',
            'peserta' => 'PST',
            default => 'USR',
        };

        return sprintf('%s-%03d', $prefix, $this->id ?? 1);
    }

    public static function generateKodeId(string $role): string
    {
        $prefix = match ($role) {
            'admin' => 'ADM',
            'juri' => 'JUR',
            'peserta' => 'PST',
            default => 'USR',
        };

        $last = self::where('role', $role)->whereNotNull('kode_id')->orderBy('id', 'desc')->first();
        $num = 1;

        if ($last && preg_match('/-(\d+)$/', $last->kode_id, $matches)) {
            $num = (int) $matches[1] + 1;
        } else {
            $count = self::where('role', $role)->count();
            $num = $count + 1;
        }

        return sprintf('%s-%03d', $prefix, $num);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isJuri(): bool
    {
        return $this->role === 'juri';
    }

    public function isPeserta(): bool
    {
        return $this->role === 'peserta';
    }

    public function burungs(): HasMany
    {
        return $this->hasMany(Burung::class);
    }

    public function pendaftarans(): HasMany
    {
        return $this->hasMany(Pendaftaran::class);
    }

    public function penilaians(): HasMany
    {
        return $this->hasMany(Penilaian::class, 'juri_id');
    }

    public function kategoriPenilaians(): BelongsToMany
    {
        return $this->belongsToMany(KategoriLomba::class, 'kategori_juri', 'user_id', 'kategori_lomba_id')
            ->withTimestamps();
    }
}
