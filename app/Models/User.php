<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
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
}
