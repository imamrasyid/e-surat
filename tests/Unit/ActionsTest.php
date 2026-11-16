<?php

use App\Actions\Dashboard\GetDashboardChartDataAction;
use App\Actions\Dashboard\GetDashboardStatsAction;
use App\Actions\Dashboard\GetUserDashboardChartDataAction;
use App\Actions\Disposition\CreateDispositionAction;
use App\Actions\MasterData\Followup\CreateFollowupAction;
use App\Actions\MasterData\Followup\UpdateFollowupAction;
use App\Actions\Memo\CloseMemoAction;
use App\Models\Disposition;
use App\Models\Followup;
use App\Models\Letter;
use App\Models\LetterDoc;
use App\Models\Memo;
use App\Models\Position;
use App\Models\User;
use App\Services\LetterDocStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

test('CloseMemoAction creates the closing memo and closes the letter', function () {
    $this->actingAs(User::factory()->create());

    $creator = User::factory()->create();
    $fromPosition = Position::create(['name' => 'Sekretaris', 'is_active' => true]);
    $toPosition = Position::create(['name' => 'Kepala Bagian Umum', 'is_active' => true]);
    $letter = makeDashboardLetter($creator->id, ['status' => 'open']);
    $recipient = $letter->recipients()->create([
        'from_user_id' => $creator->id,
        'from_position_id' => $fromPosition->id,
        'to_position_id' => $toPosition->id,
        'received_at' => now(),
    ]);

    $memo = (new CloseMemoAction)->handle($creator, $fromPosition, [
        'letter_id_primary' => $letter->id,
        'letter_recipient_id' => $recipient->id,
        'to_position_id' => $toPosition->id,
        'catatan' => 'Surat ditindaklanjuti sampai selesai.',
    ]);

    expect($memo)->toBeInstanceOf(Memo::class)
        ->and($memo->letter_id)->toBe($letter->id)
        ->and($memo->to_position_id)->toBe($toPosition->id)
        ->and($letter->fresh()->status)->toBe('close');
});

test('CreateDispositionAction writes followup text and attachment rows', function () {
    $this->actingAs(User::factory()->create());

    Storage::fake('local');
    $creator = User::factory()->create();
    $fromPosition = Position::create(['name' => 'Sekretaris', 'is_active' => true]);
    $toPosition = Position::create(['name' => 'Kepala Bagian Umum', 'is_active' => true]);
    $ccPosition = Position::create(['name' => 'Kepala Bagian Teknis', 'is_active' => true]);
    $letter = makeDashboardLetter($creator->id, ['status' => 'open']);
    $recipient = $letter->recipients()->create([
        'from_user_id' => $creator->id,
        'from_position_id' => $fromPosition->id,
        'to_position_id' => $toPosition->id,
        'received_at' => now(),
    ]);

    (new CreateDispositionAction(app(LetterDocStorage::class)))->handle($creator, $fromPosition, [
        'letter_id_primary' => $letter->id,
        'letter_recipient_id' => $recipient->id,
        'penerima' => [
            ['id' => $toPosition->id, 'name' => $toPosition->name],
        ],
        'penerima_cc' => [
            ['id' => $ccPosition->id, 'name' => $ccPosition->name],
        ],
        'catatan' => 'Mohon ditindaklanjuti',
        'followup' => [
            ['id' => 1, 'name' => 'Segera'],
            ['id' => 2, 'name' => 'Tinjau Ulang'],
        ],
    ], [
        UploadedFile::fake()->createWithContent(' disposisi.pdf', '%PDF-1.4 uji'),
    ]);

    $dispositions = Disposition::query()->orderBy('id')->get();

    expect($dispositions)->toHaveCount(2)
        ->and($dispositions[0]->to_position_id)->toBe($toPosition->id)
        ->and($dispositions[0]->followup)->toBe('Segera; Tinjau Ulang')
        ->and((bool) $dispositions[1]->is_cc)->toBeTrue()
        ->and(LetterDoc::query()->where('letter_id', $letter->id)->count())->toBe(1)
        ->and(LetterDoc::query()->where('letter_id', $letter->id)->sole()->name)->toBe(' disposisi.pdf');
});
