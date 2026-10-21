<?php

namespace App\Actions\MasterData\User;

use App\Models\Position;
use App\Models\User;
use App\Services\InboxCountsCache;
use Illuminate\Support\Facades\DB;

class UpdateUserPositionAction
{
    /**
     * Update the user's active position in transaction by closing previous position and attaching new one.
     */
    public function handle(User $user, int|string $positionId): void
    {
        $oldPositionId = $user->currentPosition()->value('positions.id');

        DB::transaction(function () use ($user, $positionId): void {
            /** @var Position|null $currentPosition */
            $currentPosition = $user->currentPosition()->first();

            if ($currentPosition) {
                $user->positions()->updateExistingPivot($currentPosition->id, [
                    'end_date' => now(),
                    'is_active' => false,
                ]);
            }

            $user->positions()->attach($positionId, [
                'start_date' => now(),
                'is_active' => true,
            ]);
        });

        // Jabatan aktif berubah: hapus badge inbox pada jabatan lama
        if ($oldPositionId !== null) {
            InboxCountsCache::forget($user, (int) $oldPositionId);
        }
    }
}
