<?php

namespace App\Actions\Dashboard;

use App\Models\Letter;
use App\Models\Position;
use App\Models\User;

class GetDashboardStatsAction
{
    /**
     * Resolve the dashboard payload for the authenticated user.
     *
     * The payload is a discriminated union keyed by `role`: `admin` (Super Admin) reports
     * system health, `manager` (Pengelola) reports letter-processing workload, and every
     * other user falls back to `user` with `stats` null because the page reads the shared
     * inbox counts instead.
     *
     * @return array{role: 'admin', stats: array{activeUsers: int, activePositions: int, totalLetters: int, openLetters: int}}|array{role: 'manager', stats: array{totalIncomingLetters: int, openLetters: int, inProcessLetters: int, completedIncomingLetters: int}}|array{role: 'user', stats: null}
     */
    public function handle(User $user): array
    {
        if ($user->hasRole('Super Admin')) {
            return [
                'role' => 'admin',
                'stats' => [
                    'activeUsers' => User::query()->where('is_active', true)->count(),
                    'activePositions' => Position::query()->where('is_active', true)->count(),
                    'totalLetters' => Letter::query()->count(),
                    'openLetters' => Letter::query()->where('status', 'open')->count(),
                ],
            ];
        }

        if ($user->hasRole('Pengelola')) {
            return [
                'role' => 'manager',
                'stats' => [
                    'totalIncomingLetters' => Letter::query()->where('type', 'in')->count(),
                    'openLetters' => Letter::query()->where('status', 'open')->count(),
                    'inProcessLetters' => Letter::query()
                        ->where('type', 'in')
                        ->where('status', 'open')
                        ->whereHas('recipients')
                        ->whereDoesntHave('dispositions')
                        ->whereDoesntHave('memos')
                        ->count(),
                    'completedIncomingLetters' => Letter::query()
                        ->where('type', 'in')
                        ->where('status', 'close')
                        ->count(),
                ],
            ];
        }

        return [
            'role' => 'user',
            'stats' => null,
        ];
    }
}
