<?php

namespace App\Services;

use App\Models\Letter;
use App\Models\User;

/**
 * Otorisasi baca surat untuk lampiran privat.
 *
 * Dipakai endpoint unduhan karena route model binding tidak bisa menyatakan
 * "pembuat, penerima jabatan aktif, atau pemegang izin baca surat terkirim".
 */
class LetterAccess
{
    public function allows(User $user, Letter $letter): bool
    {
        if ((int) $letter->created_by === (int) $user->id) {
            return true;
        }

        // Izin `sent.show` dan `arsip.show` berarti boleh membaca surat
        // perusahaan/arsip; Super Admin melewati Gate::before pada
        // AppServiceProvider.
        if ($user->can('sent.show') || $user->can('arsip.show')) {
            return true;
        }

        $positionIds = $user->positions()
            ->wherePivot('is_active', true)
            ->pluck('positions.id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        if ($positionIds === []) {
            return false;
        }

        return $letter->recipients()->whereIn('to_position_id', $positionIds)->exists()
            || $letter->recipients()->whereIn('from_position_id', $positionIds)->exists()
            || $letter->dispositions()->whereIn('to_position_id', $positionIds)->exists()
            || $letter->memos()->whereIn('to_position_id', $positionIds)->exists();
    }
}
