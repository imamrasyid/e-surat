<?php

namespace App\Listeners;

use App\Events\InboxItemCreated;
use App\Models\User;
use App\Notifications\InboxItemNotification;
use Illuminate\Support\Facades\Notification;

class SendInboxItemNotification
{
    /**
     * Kirim notifikasi inbox ke seluruh user aktif yang memegang jabatan tujuan.
     */
    public function handle(InboxItemCreated $event): void
    {
        $positionId = (int) $event->item->to_position_id;
        $pivotTable = (new User)->positions()->getTable();

        $users = User::query()
            ->where('is_active', true)
            ->whereHas('positions', function ($query) use ($positionId, $pivotTable): void {
                $query->where('positions.id', $positionId)
                    ->where($pivotTable.'.is_active', true);
            })
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new InboxItemNotification($event->item));
    }
}
