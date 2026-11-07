<?php

namespace App\Http\Middleware;

use App\Services\CompanyFeature;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompanyFeatureMiddleware
{
    public function __construct(private readonly CompanyFeature $features) {}

    /**
     * Tolak route yang feature-nya tidak aktif pada server ini.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        abort_unless($this->features->enabled($feature), 404);

        return $next($request);
    }
}
