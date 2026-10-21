<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InboxCountsCache
{
    /**
     * TTL badge inbox pada sidebar (detik). Sama dengan HandleInertiaRequests.
     */
    public const TTL_SECONDS = 30;

    /**
     * Kunci cache badge inbox per user dan jabatan aktifnya.
     */
    public static function key(int $userId, int $positionId): string
    {
        return "inbox_counts_user_{$userId}_pos_{$positionId}";
    }

    /**
     * Hapus badge inbox user untuk jabatan tertentu (default: jabatan aktifnya).
     */
    public static function forget(User $user, ?int $positionId = null): void
    {
        $positionId ??= (int) $user->currentPosition()->value('positions.id');

        if ($positionId > 0) {
            Cache::forget(self::key($user->id, $positionId));
        }
    }

    /**
     * Hapus badge inbox seluruh user yang jabatan aktifnya termasuk dalam $positionIds.
     *
     * @param  iterable<int|string>  $positionIds
     */
    public static function forgetPositions(iterable $positionIds): void
    {
        $positionIds = collect($positionIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->filter(fn ($id) => $id > 0)
            ->values();

        if ($positionIds->isEmpty()) {
            return;
        }

        // User yang punya pivot jabatan aktif pada posisi terkait
        $pivotTable = (new User)->positions()->getTable();

        $userIds = DB::table($pivotTable)
            ->where('model_type', (new User)->getMorphClass())
            ->whereIn('position_id', $positionIds->all())
            ->where('is_active', 1)
            ->pluck('model_id')
            ->map(fn ($id) => (int) $id);

        if ($userIds->isEmpty()) {
            return;
        }

        $affectedPositionIds = $positionIds->all();

        // Semantik sama persis dengan HandleInertiaRequests::share():
        // badge memakai jabatan aktif pertama user
        User::whereIn('id', $userIds->all())
            ->get()
            ->each(function (User $user) use ($affectedPositionIds): void {
                $currentPositionId = $user->currentPosition()->value('positions.id');

                if ($currentPositionId !== null && in_array((int) $currentPositionId, $affectedPositionIds, true)) {
                    Cache::forget(self::key($user->id, (int) $currentPositionId));
                }
            });
    }
}
