<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WildcardPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthorized');
        }

        if ($user->hasRole('Super Admin')) {
            return $next($request);
        }

        // bisa terima multiple permission, pisahkan dengan "|"
        $permissionList = explode('|', $permissions);

        $userPermissions = $user->getAllPermissions()->pluck('name');

        $hasPermission = collect($permissionList)->some(function ($permission) use ($user, $userPermissions) {
            // kalau wildcard (misal: master.*)
            if (str_ends_with($permission, '.*')) {
                $prefix = rtrim($permission, '.*');

                return $userPermissions->contains(fn ($p) => str_starts_with($p, $prefix.'.'));
            }

            // kalau exact
            return $user->can($permission);
        });

        if (! $hasPermission) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
