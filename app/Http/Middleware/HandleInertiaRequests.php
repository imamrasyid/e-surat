<?php

namespace App\Http\Middleware;

use App\Models\Letter;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $primary = 0;
        $undangan = 0;

        if ($request->user()) {
            $currentPositionId = $request->user()->currentPosition()->pluck('positions.id')->first();

            if ($currentPositionId) {
                // Gunakan cache per user + posisi
                $cacheKey = "inbox_counts_user_{$request->user()->id}_pos_{$currentPositionId}";

                [$primary, $undangan] = Cache::remember($cacheKey, now()->addSeconds(60), function () use ($currentPositionId) {
                    $baseQuery = Letter::query()
                        ->leftJoin('letter_recipients', 'letters.id', '=', 'letter_recipients.letter_id')
                        ->where('type', 'in')
                        ->where('letter_recipients.to_position_id', $currentPositionId)
                        ->whereNull('letter_recipients.read_at');

                    $primary = (clone $baseQuery)->where('category', '1')->count();
                    $undangan = (clone $baseQuery)->where('category', '2')->count();

                    return [$primary, $undangan];
                });
            }
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(),
            ],
            'inbox' => [
                'primary' => fn() => $primary,
                'undangan' => fn() => $undangan,
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
