<?php

use App\Actions\Dashboard\GetDashboardChartDataAction;
use App\Actions\Dashboard\GetDashboardStatsAction;
use App\Actions\Dashboard\GetUserDashboardChartDataAction;
use App\Actions\MasterData\Followup\CreateFollowupAction;
use App\Actions\MasterData\Followup\UpdateFollowupAction;
use App\Models\Followup;
use App\Models\Letter;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function makeDashboardLetter(int $createdBy, array $attributes = []): Letter
{
    return Letter::create([
        'letter_id' => (string) Str::ulid(),
        'sender' => 'Instansi Pengirim',
        'number' => '001/ST/2026',
        'date' => now()->toDateString(),
        'receipt_date' => now()->toDateString(),
        'subject' => 'Surat Uji Dashboard',
        'category' => '1',
        'agenda' => '001',
        'type' => 'in',
        'status' => 'open',
        'created_by' => $createdBy,
        ...$attributes,
    ]);
}

test('GetDashboardStatsAction returns admin system health for Super Admin', function () {
    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole(Role::findOrCreate('Super Admin'));

    $activeUser = User::factory()->create(['is_active' => true]);
    User::factory()->create(['is_active' => false]);

    Position::create(['name' => 'Sekretaris', 'is_active' => true]);
    Position::create(['name' => 'Kepala Bagian', 'is_active' => true]);
    Position::create(['name' => 'Jabatan Nonaktif', 'is_active' => false]);

    makeDashboardLetter($activeUser->id, ['type' => 'in', 'status' => 'open']);
    makeDashboardLetter($activeUser->id, ['type' => 'in', 'status' => 'close']);
    makeDashboardLetter($activeUser->id, ['type' => 'out', 'status' => 'open']);

    $payload = (new GetDashboardStatsAction)->handle($admin);

    expect($payload['role'])->toBe('admin')
        ->and($payload['stats'])->toBe([
            'activeUsers' => 2,
            'activePositions' => 2,
            'totalLetters' => 3,
            'openLetters' => 2,
        ]);
});

test('GetDashboardStatsAction returns manager letter processing for Pengelola', function () {
    $manager = User::factory()->create();
    $manager->assignRole(Role::findOrCreate('Pengelola'));

    $creator = User::factory()->create();
    $position = Position::create(['name' => 'Sekretaris', 'is_active' => true]);

    $recipientPayload = [
        'from_user_id' => $creator->id,
        'from_position_id' => $position->id,
        'to_position_id' => $position->id,
        'received_at' => now(),
    ];

    $inProcess = makeDashboardLetter($creator->id, ['type' => 'in', 'status' => 'open']);
    $inProcess->recipients()->create($recipientPayload);

    $completed = makeDashboardLetter($creator->id, ['type' => 'in', 'status' => 'close']);
    $completed->recipients()->create($recipientPayload);

    $dispositioned = makeDashboardLetter($creator->id, ['type' => 'in', 'status' => 'open']);
    $recipient = $dispositioned->recipients()->create($recipientPayload);
    $dispositioned->dispositions()->create([
        'letter_recipient_id' => $recipient->id,
        'from_user_id' => $creator->id,
        'from_position_id' => $position->id,
        'to_position_id' => $position->id,
    ]);

    makeDashboardLetter($creator->id, ['type' => 'in', 'status' => 'open']);
    makeDashboardLetter($creator->id, ['type' => 'out', 'status' => 'open']);
    makeDashboardLetter($creator->id, ['type' => 'out', 'status' => 'close']);

    $payload = (new GetDashboardStatsAction)->handle($manager);

    expect($payload['role'])->toBe('manager')
        ->and($payload['stats'])->toBe([
            'totalIncomingLetters' => 4,
            'openLetters' => 4,
            'inProcessLetters' => 1,
            'completedIncomingLetters' => 1,
        ]);
});

test('GetDashboardStatsAction falls back to the user role without stats', function () {
    $user = User::factory()->create();

    $payload = (new GetDashboardStatsAction)->handle($user);

    expect($payload)->toBe(['role' => 'user', 'stats' => null]);
});

test('GetDashboardChartDataAction returns continuous date series', function () {
    $action = new GetDashboardChartDataAction;
    $data = $action->handle(7);

    expect($data)->toHaveCount(7)
        ->and($data[0])->toHaveKeys(['date', 'surat_masuk']);
});

test('GetUserDashboardChartDataAction splits daily counts per category for the active position', function () {
    $user = User::factory()->create();
    $position = Position::create(['name' => 'Staf Umum', 'is_active' => true]);
    $otherPosition = Position::create(['name' => 'Kepala Bagian', 'is_active' => true]);
    $user->positions()->sync([$position->id => ['is_active' => true]]);

    $creator = User::factory()->create();
    $recipientPayload = [
        'from_user_id' => $creator->id,
        'from_position_id' => $otherPosition->id,
        'received_at' => now(),
    ];

    $primaryLetter = makeDashboardLetter($creator->id, ['type' => 'in', 'category' => '1']);
    $primaryLetter->recipients()->create($recipientPayload + ['to_position_id' => $position->id]);

    $undanganLetter = makeDashboardLetter($creator->id, ['type' => 'in', 'category' => '2']);
    $undanganLetter->recipients()->create([
        ...$recipientPayload,
        'to_position_id' => $position->id,
        'received_at' => now()->subDay(),
    ]);

    $sourceLetter = makeDashboardLetter($creator->id, ['type' => 'in', 'category' => '1']);
    $sourceRecipient = $sourceLetter->recipients()->create($recipientPayload + ['to_position_id' => $otherPosition->id]);

    $sourceLetter->dispositions()->create([
        ...$recipientPayload,
        'letter_recipient_id' => $sourceRecipient->id,
        'to_position_id' => $position->id,
    ]);

    $sourceLetter->memos()->create([
        ...$recipientPayload,
        'letter_recipient_id' => $sourceRecipient->id,
        'to_position_id' => $position->id,
    ]);

    $payload = (new GetUserDashboardChartDataAction)->handle($user, 7);

    expect($payload['series'])->toHaveCount(7)
        ->and($payload['series'][6])->toBe([
            'date' => now()->toDateString(),
            'primary' => 1,
            'undangan' => 0,
            'disposisi' => 1,
            'memo' => 1,
        ])
        ->and($payload['series'][5])->toMatchArray([
            'date' => now()->subDay()->toDateString(),
            'primary' => 0,
            'undangan' => 1,
        ])
        ->and($payload['totals'])->toBe([
            'primary' => 1,
            'undangan' => 1,
            'disposisi' => 1,
            'memo' => 1,
        ])
        ->and($payload['totalReceived'])->toBe(2);
});

test('GetUserDashboardChartDataAction returns zeros when the user has no active position', function () {
    $user = User::factory()->create();

    $payload = (new GetUserDashboardChartDataAction)->handle($user, 3);

    expect($payload['series'])->toHaveCount(3)
        ->and($payload['totals'])->toBe(['primary' => 0, 'undangan' => 0, 'disposisi' => 0, 'memo' => 0])
        ->and($payload['totalReceived'])->toBe(0);
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
