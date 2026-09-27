<?php

use App\Actions\Dashboard\GetDashboardChartDataAction;
use App\Actions\Dashboard\GetDashboardStatsAction;
use App\Actions\MasterData\Followup\CreateFollowupAction;
use App\Actions\MasterData\Followup\UpdateFollowupAction;
use App\Models\Followup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('GetDashboardStatsAction returns array with expected keys', function () {
    $action = new GetDashboardStatsAction;
    $stats = $action->handle();

    expect($stats)->toBeArray()
        ->toHaveKeys(['totalLetters', 'inProcessLetters', 'completedLetters']);
});

test('GetDashboardChartDataAction returns continuous date series', function () {
    $action = new GetDashboardChartDataAction;
    $data = $action->handle(7);

    expect($data)->toHaveCount(7)
        ->and($data[0])->toHaveKeys(['date', 'surat_masuk']);
});

test('CreateFollowupAction and UpdateFollowupAction manipulate followup model', function () {
    $createAction = new CreateFollowupAction;
    $followup = $createAction->handle(['name' => 'Tindak Lanjut Segera']);

    expect($followup)->toBeInstanceOf(Followup::class)
        ->and($followup->name)->toBe('Tindak Lanjut Segera');

    $updateAction = new UpdateFollowupAction;
    $updateAction->handle($followup, ['name' => 'Tindak Lanjut Revisi', 'is_active' => true]);

    expect($followup->fresh()->name)->toBe('Tindak Lanjut Revisi');
});
