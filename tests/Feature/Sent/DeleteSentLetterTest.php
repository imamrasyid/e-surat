<?php

use App\Models\Disposition;
use App\Models\Letter;
use App\Models\LetterDoc;
use App\Models\LetterRecipient;
use App\Models\Memo;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

/**
 * @return array{
 *     letter: Letter,
 *     recipient: LetterRecipient,
 *     disposition: Disposition,
 *     memo: Memo,
 *     activeDoc: LetterDoc,
 *     inactiveDoc: LetterDoc
 * }
 */
function sentLetterForDeletion(): array
{
    $creator = User::factory()->create();
    $fromPosition = Position::create(['name' => 'Sekretaris', 'is_active' => true]);
    $toPosition = Position::create(['name' => 'Kepala Bagian Umum', 'is_active' => true]);

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

    $recipient = LetterRecipient::create([
        'letter_id' => $letter->id,
        'from_user_id' => $creator->id,
        'from_position_id' => $fromPosition->id,
        'to_position_id' => $toPosition->id,
        'received_at' => now(),
    ]);

    $disposition = Disposition::create([
        'letter_id' => $letter->id,
        'letter_recipient_id' => $recipient->id,
        'from_user_id' => $creator->id,
        'from_position_id' => $fromPosition->id,
        'to_position_id' => $toPosition->id,
        'received_at' => now(),
    ]);

    $memo = Memo::create([
        'letter_id' => $letter->id,
        'letter_recipient_id' => $recipient->id,
        'from_user_id' => $creator->id,
        'from_position_id' => $fromPosition->id,
        'to_position_id' => $toPosition->id,
        'received_at' => now(),
    ]);

    $activeDoc = LetterDoc::create([
        'letter_id' => $letter->id,
        'name' => 'undangan.pdf',
        'path' => 'letter-docs/2026/10/undangan.pdf',
        'is_active' => true,
    ]);

    $inactiveDoc = LetterDoc::create([
        'letter_id' => $letter->id,
        'name' => 'undangan-lama.pdf',
        'path' => 'letter-docs/2026/10/undangan-lama.pdf',
        'is_active' => false,
    ]);

    return compact('letter', 'recipient', 'disposition', 'memo', 'activeDoc', 'inactiveDoc');
}

function userWithSentUpdatePermission(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('sent.update'));

    return $user;
}

it('soft deletes the letter and all of its relations', function () {
    ['letter' => $letter, 'recipient' => $recipient, 'disposition' => $disposition, 'memo' => $memo, 'activeDoc' => $activeDoc, 'inactiveDoc' => $inactiveDoc] = sentLetterForDeletion();

    $this->actingAs(userWithSentUpdatePermission())
        ->delete(route('sent.destroy', $letter->letter_id))
        ->assertRedirect(route('sent.index'));

    $this->assertSoftDeleted('letters', ['id' => $letter->id]);
    $this->assertSoftDeleted('letter_recipients', ['id' => $recipient->id]);
    $this->assertSoftDeleted('dispositions', ['id' => $disposition->id]);
    $this->assertSoftDeleted('memos', ['id' => $memo->id]);
    $this->assertSoftDeleted('letter_docs', ['id' => $activeDoc->id]);
    $this->assertSoftDeleted('letter_docs', ['id' => $inactiveDoc->id]);

    expect(LetterDoc::withTrashed()->where('letter_id', $letter->id)->count())->toBe(2);
});

it('forbids deletion without the sent.update permission', function () {
    ['letter' => $letter, 'recipient' => $recipient] = sentLetterForDeletion();

    $this->actingAs(User::factory()->create())
        ->delete(route('sent.destroy', $letter->letter_id))
        ->assertForbidden();

    $this->assertNotSoftDeleted('letters', ['id' => $letter->id]);
    $this->assertNotSoftDeleted('letter_recipients', ['id' => $recipient->id]);
});

it('returns not found for letters outside the sent module', function () {
    ['letter' => $letter] = sentLetterForDeletion();
    $letter->update(['type' => 'out']);

    $this->actingAs(userWithSentUpdatePermission())
        ->delete(route('sent.destroy', $letter->letter_id))
        ->assertNotFound();

    $this->assertNotSoftDeleted('letters', ['id' => $letter->id]);
});
