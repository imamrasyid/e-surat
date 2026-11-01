<?php

use App\Models\Letter;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

/**
 * Surat masuk beserta penerima pada jabatan aktif user, dengan satu nota dinas
 * yang lebih dulu dibuat dan satu disposisi setelahnya.
 *
 * @return array{0: User, 1: Letter}
 */
function inboxTimelineFixture(string $category): array
{
    $user = User::factory()->create();
    $position = Position::create(['name' => 'Staf Umum', 'is_active' => true]);
    $user->positions()->sync([$position->id => ['is_active' => true]]);

    $creator = User::factory()->create();
    $letter = Letter::create([
        'letter_id' => (string) Str::ulid(),
        'sender' => 'Instansi Pengirim',
        'number' => '010/ST/2026',
        'date' => now()->toDateString(),
        'receipt_date' => now()->toDateString(),
        'subject' => 'Surat Uji Tindak Lanjut',
        'category' => $category,
        'agenda' => '010',
        'type' => 'in',
        'status' => 'open',
        'created_by' => $creator->id,
    ]);

    $recipient = $letter->recipients()->create([
        'from_user_id' => $creator->id,
        'from_position_id' => $position->id,
        'to_position_id' => $position->id,
        'received_at' => now()->subDay(),
    ]);

    $activityPayload = [
        'letter_recipient_id' => $recipient->id,
        'from_user_id' => $creator->id,
        'from_position_id' => $position->id,
        'to_position_id' => $position->id,
    ];

    $letter->memos()->create([...$activityPayload, 'received_at' => now()->subHours(2)]);
    $letter->dispositions()->create([...$activityPayload, 'received_at' => now()->subHour()]);

    return [$user, $letter];
}

test('primary index sends activities merged by time', function () {
    [$user] = inboxTimelineFixture('1');
    $user->givePermissionTo(Permission::findOrCreate('primary.index'));

    $props = $this->actingAs($user)->get('/primary')->assertOk()->viewData('page')['props'];
    $row = $props['primary']['data'][0];

    expect(array_column($row['activities'], 'type'))->toBe(['nota_dinas', 'disposisi'])
        ->and($row)->not->toHaveKey('activity_dispo')
        ->and($row)->not->toHaveKey('activity_memo');
});

test('undangan index sends activities merged by time', function () {
    [$user] = inboxTimelineFixture('2');
    $user->givePermissionTo(Permission::findOrCreate('undangan.index'));

    $props = $this->actingAs($user)->get('/invitation-letter')->assertOk()->viewData('page')['props'];
    $row = $props['undangan']['data'][0];

    expect(array_column($row['activities'], 'type'))->toBe(['nota_dinas', 'disposisi'])
        ->and($row)->not->toHaveKey('activity_dispo')
        ->and($row)->not->toHaveKey('activity_memo');
});
