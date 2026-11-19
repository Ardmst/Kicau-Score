<?php

namespace App\Policies;

use App\Models\Penilaian;
use App\Models\User;

class PenilaianPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isJuri() || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Penilaian $penilaian): bool
    {
        // Admin always has control or validation authority
        if ($user->isAdmin()) {
            return true;
        }

        // Juri can only edit their own score if draft and category is ongoing
        $kategoriStatus = $penilaian->pendaftaran->kategoriLomba->status_lomba ?? 'upcoming';

        return $user->id === $penilaian->juri_id
            && $penilaian->status_validasi === 'draft'
            && $kategoriStatus === 'ongoing';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Penilaian $penilaian): bool
    {
        return $user->isAdmin() || $user->id === $penilaian->juri_id || $user->id === $penilaian->pendaftaran->user_id;
    }
}
