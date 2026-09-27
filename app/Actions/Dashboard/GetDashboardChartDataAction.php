<?php

namespace App\Actions\Dashboard;

use App\Models\Letter;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class GetDashboardChartDataAction
{
    /**
     * Get incoming letter chart trends for the specified number of days.
     *
     * @return array<int, array{date: string, surat_masuk: int}>
     */
    public function handle(int $days = 90): array
    {
        $startDate = Carbon::today()->subDays($days - 1);
        $endDate = Carbon::today();

        $letters = Letter::query()
            ->where('type', 'in')
            ->whereBetween('receipt_date', [$startDate, $endDate])
            ->selectRaw('DATE(receipt_date) as date, COUNT(*) as total')
            ->groupByRaw('DATE(receipt_date)')
            ->pluck('total', 'date')
            ->toArray();

        $period = CarbonPeriod::create($startDate, $endDate);
        $data = [];

        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $data[] = [
                'date' => $key,
                'surat_masuk' => $letters[$key] ?? 0,
            ];
        }

        return $data;
    }
}
