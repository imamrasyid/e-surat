<?php

namespace App\Http\Middleware;

use App\Models\Position;
use App\Services\CompanyFeature;
use App\Services\InboxCountsCache;
use App\Services\NotificationCenter;
use App\Services\PermissionsCache;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;
use Spatie\Permission\Models\Permission;
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
            $currentPositionId = $request->user()->currentPosition()->value('positions.id');

            if ($currentPositionId) {
                $counts = Cache::remember(
                    InboxCountsCache::key($request->user()->id, (int) $currentPositionId),
                    now()->addSeconds(InboxCountsCache::TTL_SECONDS),
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
                            'primary' => $position->primary_count ?? 0,
                            'undangan' => $position->undangan_count ?? 0,
                            'disposisi' => $position->disposisi_count ?? 0,
                            'memo' => $position->memo_count ?? 0,
                        ];
                    }
                );

                $primary = $counts['primary'];
                $undangan = $counts['undangan'];
                $disposisi = $counts['disposisi'];
                $memo = $counts['memo'];
            }
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(),
                'can' => function () use ($request) {
                    if (! $request->user()) {
                        return [];
                    }

                    if ($request->user()->hasRole('Super Admin')) {
                        return Permission::pluck('name')->toArray();
                    }

                    return Cache::remember(
                        PermissionsCache::key($request->user()->id),
                        now()->addMinutes(PermissionsCache::TTL_MINUTES),
                        fn () => $request->user()
                            ->getPermissionsViaRoles()
                            ->pluck('name')
                            ->toArray()
                    );
                },
            ],
            'inbox' => [
                'primary' => fn () => $primary,
                'undangan' => fn () => $undangan,
                'disposisi' => fn () => $disposisi,
                'memo' => fn () => $memo,
            ],
            // Status feature untuk UX saja; enforcement tetap di route,
            // controller/Action, dan notification.
            'features' => fn (): array => app(CompanyFeature::class)->known(),
            'notificationCenter' => function () use ($request): array {
                if (! $request->user()) {
                    return [
                        'unread_count' => 0,
                        'latest' => [],
                        'web_push' => ['enabled' => false, 'public_key' => null],
                    ];
                }

                $center = app(NotificationCenter::class);

                return [
                    ...$center->summary($request->user()),
                    'web_push' => $center->webPushProps(),
                ];
            },
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
