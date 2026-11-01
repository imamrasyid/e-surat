<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\GetDashboardChartDataAction;
use App\Actions\Dashboard\GetDashboardStatsAction;
use App\Actions\Dashboard\GetUserDashboardChartDataAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(
        Request $request,
        GetDashboardStatsAction $getStats,
        GetDashboardChartDataAction $getChartData,
        GetUserDashboardChartDataAction $getUserChartData
    ): Response {
        $dashboard = $getStats->handle($request->user());

        return Inertia::render('Dashboard', [
            'dashboard' => $dashboard,
            'chartData' => $dashboard['role'] === 'manager' ? $getChartData->handle(90) : [],
            'userChartData' => $dashboard['role'] === 'user' ? $getUserChartData->handle($request->user(), 90) : null,
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

    public function userChartData(Request $request, GetUserDashboardChartDataAction $getUserChartData): JsonResponse
    {
        $validated = $request->validate([
            'days' => ['sometimes', 'integer', 'in:7,30,90'],
        ]);

        $days = (int) ($validated['days'] ?? 90);

        return response()->json($getUserChartData->handle($request->user(), $days));
    }
}
