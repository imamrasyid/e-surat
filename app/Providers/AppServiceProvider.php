<?php

namespace App\Providers;

use App\Listeners\ClearUserPermissionsCache;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Events\PermissionAttached;
use Spatie\Permission\Events\PermissionDetached;
use Spatie\Permission\Events\RoleAttached;
use Spatie\Permission\Events\RoleDetached;

class AppServiceProvider extends ServiceProvider
{
    protected $listen = [
        RoleAttached::class => [
            ClearUserPermissionsCache::class,
        ],
        RoleDetached::class => [
            ClearUserPermissionsCache::class,
        ],
        PermissionAttached::class => [
            ClearUserPermissionsCache::class,
        ],
        PermissionDetached::class => [
            ClearUserPermissionsCache::class,
        ],
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
