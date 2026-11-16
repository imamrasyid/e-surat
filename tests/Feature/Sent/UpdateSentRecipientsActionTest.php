<?php

use App\Actions\Letter\UpdateSentRecipientsAction;
use App\Models\Letter;
use App\Models\LetterActivity;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function recipientsLetter(User $creator, Position $fromPosition): array
{
    $letter = Letter::create([
        'letter_id' => (string) Str::ulid(),
        'sender' => 'Dinas Pendidikan Provinsi',
        'number' => '005/1234/2026',
        'date' => '2026-10-01',
        'receipt_date' => '2026-10-02',
        'subject' => 'Undangan Rapat Koordinasi',
        'category' => '1',
        'agenda' => '1/ND/2026',
        'type' => 'in',
        'status' => 'open',
        'created_by' => $creator->id,
    ]);

    $first = $letter->recipients()->create([
        'from_user_id' => $creator->id,
        'from_position_id' => $fromPosition->id,
        'to_position_id' => $fromPosition->id,
        'received_at' => now(),
        'read_at' => now(),
    ]);

    $second = $letter->recipients()->create([
        'from_user_id' => $creator->id,
        'from_position_id' => $fromPosition->id,
        'to_position_id' => Position::create(['name' => 'Kepala Bagian Umum', 'is_active' => true])->id,
        'received_at' => now(),
        'read_at' => now(),
    ]);

    return [$letter, $first, $second];
}

it('resets read status and keeps the same row when a recipient is reassigned', function () {
    $creator = User::factory()->create();
    $this->actingAs($creator);
    $fromPosition = Position::create(['name' => 'Sekretaris', 'is_active' => true]);
    [$letter, $first] = recipientsLetter($creator, $fromPosition);

    $newPosition = Position::create(['name' => 'Kepala Bagian Teknis', 'is_active' => true]);

    app(UpdateSentRecipientsAction::class)->handle($letter, $creator, $fromPosition, [
        ['id' => $first->id, 'to_position_id' => $newPosition->id, 'instruction' => null],
    ]);

    expect($first->refresh()->to_position_id)->toBe($newPosition->id)
        ->and($first->read_at)->toBeNull()
        ->and($first->received_at)->not->toBeNull()
        ->and($first->trashed())->toBeFalse();
});

it('soft deletes recipients missing from the request and keeps the rest', function () {
    $creator = User::factory()->create();
    $this->actingAs($creator);
    $fromPosition = Position::create(['name' => 'Sekretaris', 'is_active' => true]);
    [$letter, $first, $second] = recipientsLetter($creator, $fromPosition);

    app(UpdateSentRecipientsAction::class)->handle($letter, $creator, $fromPosition, [
        ['id' => $first->id, 'to_position_id' => $first->to_position_id, 'instruction' => null],
    ]);

    expect($first->refresh()->trashed())->toBeFalse()
        ->and($second->refresh()->trashed())->toBeTrue();
});

it('adds a new recipient without touching the read status of existing ones', function () {
    $creator = User::factory()->create();
    $this->actingAs($creator);
    $fromPosition = Position::create(['name' => 'Sekretaris', 'is_active' => true]);
    [$letter, $first] = recipientsLetter($creator, $fromPosition);

    $readBefore = $first->read_at;
    $newPosition = Position::create(['name' => 'Kepala Bagian Teknis', 'is_active' => true]);

    app(UpdateSentRecipientsAction::class)->handle($letter, $creator, $fromPosition, [
        ['id' => $first->id, 'to_position_id' => $first->to_position_id, 'instruction' => null],
        ['to_position_id' => $newPosition->id, 'instruction' => 'Segera koordinasi'],
    ]);

    $added = $letter->recipients()->where('to_position_id', $newPosition->id)->first();

    expect($added)->not->toBeNull()
        ->and($added->instruction)->toBe('Segera koordinasi')
        ->and($first->refresh()->read_at)->toEqual($readBefore);
});

it('logs one activity entry when recipients change', function () {
    $creator = User::factory()->create();
    $this->actingAs($creator);
    $fromPosition = Position::create(['name' => 'Sekretaris', 'is_active' => true]);
    [$letter, $first] = recipientsLetter($creator, $fromPosition);

    $newPosition = Position::create(['name' => 'Kepala Bagian Teknis', 'is_active' => true]);

    app(UpdateSentRecipientsAction::class)->handle($letter, $creator, $fromPosition, [
        ['id' => $first->id, 'to_position_id' => $newPosition->id, 'instruction' => null],
    ]);

    expect(LetterActivity::query()->where('letter_id', $letter->id)->where('action', 'update_recipients')->count())->toBe(1)
        ->and(LetterActivity::query()->where('letter_id', $letter->id)->latest('id')->first()->changes)->toHaveKey('removed');
});
