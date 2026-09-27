<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\GetDashboardChartDataAction;
use App\Actions\Dashboard\GetDashboardStatsAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(
        GetDashboardStatsAction $getStats,
        GetDashboardChartDataAction $getChartData
    ): Response {
        $stats = $getStats->handle();
        $chartData = $getChartData->handle(90);

        return Inertia::render('Dashboard', [
            ...$stats,
            'chartData' => $chartData,
        ]);
    }

    public function chartData(Request $request, GetDashboardChartDataAction $getChartData): JsonResponse
    {
        $validated = $request->validate([
            'days' => ['sometimes', 'integer', 'in:7,30,90'],
        ]);

        $days = (int) ($validated['days'] ?? 90);

        return response()->json($getChartData->handle($days));
    }
}
