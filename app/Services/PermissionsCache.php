<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

class PermissionsCache
{
    /**
     * TTL daftar permission user pada sidebar (menit). Sama dengan HandleInertiaRequests.
     */
    public const TTL_MINUTES = 15;

    /**
     * Kunci cache daftar permission per user.
     */
    public static function key(int $userId): string
    {
        return "user_permissions:{$userId}";
    }

    /**
     * Hapus cache permission seorang user.
     */
    public static function forget(User $user): void
    {
        Cache::forget(self::key($user->id));
    }

    /**
     * Hapus cache permission seluruh user yang memiliki role terkait.
     */
    public static function forgetRole(Role $role): void
    {
        $role->users()->pluck('users.id')->each(function ($userId): void {
            Cache::forget(self::key((int) $userId));
        });
    }
}
