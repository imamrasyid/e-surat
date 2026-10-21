<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\PermissionsCache;
use Spatie\Permission\Models\Role;

class ClearUserPermissionsCache
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $model = $event->model ?? null;

        if ($model instanceof Role) {
            // Permission berubah pada role: bersihkan cache seluruh pemegang role
            PermissionsCache::forgetRole($model);
        } elseif ($model instanceof User) {
            // Role berubah pada user: bersihkan cache user tersebut
            PermissionsCache::forget($model);
        }
    }
}
