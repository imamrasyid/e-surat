<?php

namespace App\Actions\Dashboard;

use App\Models\Letter;

class GetDashboardStatsAction
{
    /**
     * Get aggregate statistics for dashboard letters.
     *
     * @return array{totalLetters: int, inProcessLetters: int, completedLetters: int}
     */
    public function handle(): array
    {
        $totalLetters = Letter::query()->where('type', 'in')->count();

        $inProcessLetters = Letter::query()
            ->where('type', 'in')
            ->where('status', 'open')
            ->whereHas('recipients')
            ->whereDoesntHave('dispositions')
            ->whereDoesntHave('memos')
            ->count();

        $completedLetters = Letter::query()
            ->where('type', 'in')
            ->where('status', 'close')
            ->count();

        return [
            'totalLetters' => $totalLetters,
            'inProcessLetters' => $inProcessLetters,
            'completedLetters' => $completedLetters,
        ];
    }
}
