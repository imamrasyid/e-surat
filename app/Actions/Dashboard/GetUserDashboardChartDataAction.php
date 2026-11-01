<?php

namespace App\Actions\Dashboard;

use App\Models\Disposition;
use App\Models\LetterRecipient;
use App\Models\Memo;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;

class GetUserDashboardChartDataAction
{
    /**
     * Daily incoming counts per category for the user's active position, plus the
     * all-time totals used by the distribution chart.
     *
     * Categories: primary (letter category 1), undangan (letter category 2),
     * disposisi and nota dinas. `totalReceived` equals primary + undangan, i.e. every
     * incoming letter addressed to the active position.
     *
     * @return array{
     *     series: array<int, array{date: string, primary: int, undangan: int, disposisi: int, memo: int}>,
     *     totals: array{primary: int, undangan: int, disposisi: int, memo: int},
     *     totalReceived: int
     * }
     */
    public function handle(User $user, int $days = 90): array
    {
        $positionId = $user->currentPosition()->value('positions.id');

        $startDate = Carbon::today()->subDays($days - 1);
        $endDate = Carbon::today();

        $primary = $this->recipientCounts($positionId, '1', $startDate, $endDate);
        $undangan = $this->recipientCounts($positionId, '2', $startDate, $endDate);
        $disposisi = $this->dailyCounts(
            Disposition::query()->where('to_position_id', $positionId),
            $startDate,
            $endDate,
        );
        $memo = $this->dailyCounts(
            Memo::query()->where('to_position_id', $positionId),
            $startDate,
            $endDate,
        );

        $series = [];
        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            $key = $date->format('Y-m-d');
            $series[] = [
                'date' => $key,
                'primary' => $primary[$key] ?? 0,
                'undangan' => $undangan[$key] ?? 0,
                'disposisi' => $disposisi[$key] ?? 0,
                'memo' => $memo[$key] ?? 0,
            ];
        }

        $totals = [
            'primary' => $this->recipientTotal($positionId, '1'),
            'undangan' => $this->recipientTotal($positionId, '2'),
            'disposisi' => $positionId === null ? 0 : Disposition::query()->where('to_position_id', $positionId)->count(),
            'memo' => $positionId === null ? 0 : Memo::query()->where('to_position_id', $positionId)->count(),
        ];

        return [
            'series' => $series,
            'totals' => $totals,
            'totalReceived' => $totals['primary'] + $totals['undangan'],
        ];
    }

    /**
     * Daily counts of incoming letters addressed to the position for one category.
     *
     * @return array<string, int>
     */
    private function recipientCounts(?int $positionId, string $category, Carbon $startDate, Carbon $endDate): array
    {
        if ($positionId === null) {
            return [];
        }

        return $this->dailyCounts(
            LetterRecipient::query()
                ->where('to_position_id', $positionId)
                ->whereHas('letter', function (Builder $query) use ($category): void {
                    $query->where('type', 'in')->where('category', $category);
                }),
            $startDate,
            $endDate,
        );
    }

    /**
     * All-time count of incoming letters addressed to the position for one category.
     */
    private function recipientTotal(?int $positionId, string $category): int
    {
        if ($positionId === null) {
            return 0;
        }

        return LetterRecipient::query()
            ->where('to_position_id', $positionId)
            ->whereHas('letter', function (Builder $query) use ($category): void {
                $query->where('type', 'in')->where('category', $category);
            })
            ->count();
    }

    /**
     * Group the query rows by day, using the received timestamp with a created fallback.
     *
     * @param  Builder<LetterRecipient>|Builder<Disposition>|Builder<Memo>  $query
     * @return array<string, int>
     */
    private function dailyCounts(Builder $query, Carbon $startDate, Carbon $endDate): array
    {
        $rows = $query
            ->whereRaw('DATE(COALESCE(received_at, created_at)) BETWEEN ? AND ?', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->selectRaw('DATE(COALESCE(received_at, created_at)) as day, COUNT(*) as total')
            ->groupByRaw('DATE(COALESCE(received_at, created_at))')
            ->pluck('total', 'day');

        $counts = [];
        foreach ($rows as $day => $total) {
            $counts[(string) $day] = (int) $total;
        }

        return $counts;
    }
}
