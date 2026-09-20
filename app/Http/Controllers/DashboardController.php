<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $totalLetters = Letter::where('type', 'in')->count();

        $inProcessLetters = Letter::where('type', 'in')
            ->where('status', 'open')
            ->whereHas('recipients')
            ->whereDoesntHave('dispositions')
            ->whereDoesntHave('memos')
            ->count();

        $completedLetters = Letter::where('type', 'in')
            ->where('status', 'close')
            ->count();

        $chartData = $this->getChartData(90);

        return inertia('Dashboard', [
            'totalLetters' => $totalLetters,
            'inProcessLetters' => $inProcessLetters,
            'completedLetters' => $completedLetters,
            'chartData' => $chartData,
        ]);
    }

    public function chartData(Request $request): JsonResponse
    {
        $days = (int) $request->input('days', 90);

        if (! in_array($days, [7, 30, 90])) {
            $days = 90;
        }

        return response()->json($this->getChartData($days));
    }

    private function getChartData(int $days): array
    {
        $startDate = Carbon::today()->subDays($days - 1);
        $endDate = Carbon::today();

        $letters = Letter::where('type', 'in')
            ->whereBetween('receipt_date', [$startDate, $endDate])
            ->select(DB::raw('DATE(receipt_date) as date'), DB::raw('COUNT(*) as total'))
            ->groupBy(DB::raw('DATE(receipt_date)'))
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