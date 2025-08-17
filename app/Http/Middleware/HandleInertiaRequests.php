<?php

namespace App\Http\Middleware;

use App\Models\Disposition;
use App\Models\Letter;
use App\Models\Memo;
use App\Models\Position;
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
        $disposisi = 0;
        $memo = 0;

        if ($request->user()) {
            $currentPositionId = $request->user()->currentPosition()->pluck('positions.id')->first();

            if ($currentPositionId) {
                $counts = Cache::remember(
                    "inbox_counts_user_{$request->user()->id}_pos_{$currentPositionId}",
                    now()->addSeconds(30), // TTL cache
                    function () use ($currentPositionId) {
                        $position = Position::where('id', $currentPositionId)
                            ->withCount([
                                'recipients as primary_count' => function ($q) {
                                    $q->whereNull('read_at')
                                    ->whereHas('letter', function ($q2) {
                                        $q2->where('type', 'in')
                                            ->where('category', 1);
                                    });
                                },
                                'recipients as undangan_count' => function ($q) {
                                    $q->whereNull('read_at')
                                    ->whereHas('letter', function ($q2) {
                                        $q2->where('type', 'in')
                                            ->where('category', 2);
                                    });
                                },
                                'dispositions as disposisi_count' => function ($q) {
                                    $q->whereNull('read_at');
                                },
                                'memos as memo_count' => function ($q) {
                                    $q->whereNull('read_at');
                                },
                            ])
                            ->first();

                        return [
                            'primary'   => $position->primary_count ?? 0,
                            'undangan'  => $position->undangan_count ?? 0,
                            'disposisi' => $position->disposisi_count ?? 0,
                            'memo'      => $position->memo_count ?? 0,
                        ];
                    }
                );
                
                $primary   = $counts['primary'];
                $undangan  = $counts['undangan'];
                $disposisi = $counts['disposisi'];
                $memo      = $counts['memo'];
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
                'disposisi' => fn() => $disposisi,
                'memo' => fn() => $memo,
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
