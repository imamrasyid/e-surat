<?php

use App\Events\InboxItemCreated;
use App\Models\Disposition;
use App\Models\Letter;
use App\Models\LetterRecipient;
use App\Models\Memo;
use App\Models\Position;
use App\Models\RefKdKlasifikasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function followUpActor(string $permissionName): User
{
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate($permissionName));

    return $user;
}

function followUpLetter(User $creator): Letter
{
    RefKdKlasifikasi::firstOrCreate(['kode' => 'ND'], ['nama' => 'Nota Dinas', 'is_active' => true]);

    return Letter::create([
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
}

function followUpRecipient(Letter $letter, User $sender, Position $from, Position $to): LetterRecipient
{
    return LetterRecipient::create([
        'letter_id' => $letter->id,
        'from_user_id' => $sender->id,
        'from_position_id' => $from->id,
        'to_position_id' => $to->id,
        'received_at' => now(),
    ]);
}

function followUpMemo(Letter $letter, LetterRecipient $recipient, User $sender, Position $from, Position $to, bool $isCc = false): Memo
{
    return Memo::create([
        'letter_id' => $letter->id,
        'letter_recipient_id' => $recipient->id,
        'is_cc' => $isCc,
        'from_user_id' => $sender->id,
        'from_position_id' => $from->id,
        'to_position_id' => $to->id,
        'received_at' => now(),
    ]);
}

function followUpDisposition(Letter $letter, LetterRecipient $recipient, User $sender, Position $from, Position $to, bool $isCc = false): Disposition
{
    return Disposition::create([
        'letter_id' => $letter->id,
        'letter_recipient_id' => $recipient->id,
        'is_cc' => $isCc,
        'from_user_id' => $sender->id,
        'from_position_id' => $from->id,
        'to_position_id' => $to->id,
        'received_at' => now(),
    ]);
}

function followUpKey(Letter $letter): string
{
    return base64_encode(Str::random(10).$letter->letter_id);
}

it('lets the assigned recipient open the memo create form', function () {
    $creator = User::factory()->create();
    $actor = followUpActor('memo.create');
    $fromPosition = Position::create(['name' => 'Sekretaris', 'is_active' => true]);
    $toPosition = Position::create(['name' => 'Kepala Bagian Umum', 'is_active' => true]);
    $actor->positions()->attach($toPosition->id, ['is_active' => true]);

    $letter = followUpLetter($creator);
    $recipient = followUpRecipient($letter, $creator, $fromPosition, $toPosition);

    $this->actingAs($actor)
        ->get(route('memo.create', ['recipient' => $recipient->id, 'key' => followUpKey($letter)]))
        ->assertOk();
});

it('forbids opening the memo create form through a recipient addressed to another position', function () {
    $creator = User::factory()->create();
    $actor = followUpActor('memo.create');
    $fromPosition = Position::create(['name' => 'Sekretaris', 'is_active' => true]);
    $unrelatedPosition = Position::create(['name' => 'Kepala Bagian Lain', 'is_active' => true]);
    $otherRecipientPosition = Position::create(['name' => 'Kepala Bagian Teknis', 'is_active' => true]);
    $actor->positions()->attach($unrelatedPosition->id, ['is_active' => true]);

    $letter = followUpLetter($creator);
    $otherRecipient = followUpRecipient($letter, $creator, $fromPosition, $otherRecipientPosition);

    $this->actingAs($actor)
        ->get(route('memo.create', ['recipient' => $otherRecipient->id, 'key' => followUpKey($letter)]))
        ->assertForbidden();

    expect(Memo::query()->count())->toBe(0);
});

it('forbids opening the memo create form when the recipient belongs to another letter', function () {
    $creator = User::factory()->create();
    $actor = followUpActor('memo.create');
    $fromPosition = Position::create(['name' => 'Sekretaris', 'is_active' => true]);
    $toPosition = Position::create(['name' => 'Kepala Bagian Umum', 'is_active' => true]);
    $actor->positions()->attach($toPosition->id, ['is_active' => true]);

    $myLetter = followUpLetter($creator);
    followUpRecipient($myLetter, $creator, $fromPosition, $toPosition);

    $otherLetter = followUpLetter($creator);

    $this->actingAs($actor)
        ->get(route('memo.create', ['recipient' => 999999, 'key' => followUpKey($otherLetter)]))
        ->assertForbidden();

    expect(Memo::query()->count())->toBe(0);
});

it('lets the assigned recipient open the disposition create form', function () {
    $creator = User::factory()->create();
    $actor = followUpActor('disposition.create');
    $fromPosition = Position::create(['name' => 'Sekretaris', 'is_active' => true]);
    $toPosition = Position::create(['name' => 'Kepala Bagian Umum', 'is_active' => true]);
    $actor->positions()->attach($toPosition->id, ['is_active' => true]);

    $letter = followUpLetter($creator);
    $recipient = followUpRecipient($letter, $creator, $fromPosition, $toPosition);

    $this->actingAs($actor)
        ->get(route('dispo.create', ['recipient' => $recipient->id, 'key' => followUpKey($letter)]))
        ->assertOk();
});

it('forbids opening the disposition create form through a recipient addressed to another position', function () {
    $creator = User::factory()->create();
    $actor = followUpActor('disposition.create');
    $fromPosition = Position::create(['name' => 'Sekretaris', 'is_active' => true]);
    $unrelatedPosition = Position::create(['name' => 'Kepala Bagian Lain', 'is_active' => true]);
    $otherRecipientPosition = Position::create(['name' => 'Kepala Bagian Teknis', 'is_active' => true]);
    $actor->positions()->attach($unrelatedPosition->id, ['is_active' => true]);

    $letter = followUpLetter($creator);
    $otherRecipient = followUpRecipient($letter, $creator, $fromPosition, $otherRecipientPosition);

    $this->actingAs($actor)
        ->get(route('dispo.create', ['recipient' => $otherRecipient->id, 'key' => followUpKey($letter)]))
        ->assertForbidden();

    expect(Disposition::query()->count())->toBe(0);
});

it('lets a memo recipient open the memo create form', function () {
    $creator = User::factory()->create();
    $actor = followUpActor('memo.create');
    $fromPosition = Position::create(['name' => 'Sekretaris', 'is_active' => true]);
    $recipientPosition = Position::create(['name' => 'Kepala Bagian Umum', 'is_active' => true]);
    $actorPosition = Position::create(['name' => 'Kepala Sub Bagian', 'is_active' => true]);
    $actor->positions()->attach($actorPosition->id, ['is_active' => true]);

    $letter = followUpLetter($creator);
    $recipient = followUpRecipient($letter, $creator, $fromPosition, $recipientPosition);
    followUpMemo($letter, $recipient, $creator, $recipientPosition, $actorPosition);

    $this->actingAs($actor)
        ->get(route('memo.create', ['recipient' => $recipient->id, 'key' => followUpKey($letter)]))
        ->assertOk();
});

it('lets a disposition recipient open the disposition create form', function () {
    $creator = User::factory()->create();
    $actor = followUpActor('disposition.create');
    $fromPosition = Position::create(['name' => 'Sekretaris', 'is_active' => true]);
    $recipientPosition = Position::create(['name' => 'Kepala Bagian Umum', 'is_active' => true]);
    $actorPosition = Position::create(['name' => 'Kepala Sub Bagian', 'is_active' => true]);
    $actor->positions()->attach($actorPosition->id, ['is_active' => true]);

    $letter = followUpLetter($creator);
    $recipient = followUpRecipient($letter, $creator, $fromPosition, $recipientPosition);
    followUpDisposition($letter, $recipient, $creator, $recipientPosition, $actorPosition);

    $this->actingAs($actor)
        ->get(route('dispo.create', ['recipient' => $recipient->id, 'key' => followUpKey($letter)]))
        ->assertOk();
});

it('forbids a cc memo recipient from opening the memo create form', function () {
    $creator = User::factory()->create();
    $actor = followUpActor('memo.create');
    $fromPosition = Position::create(['name' => 'Sekretaris', 'is_active' => true]);
    $recipientPosition = Position::create(['name' => 'Kepala Bagian Umum', 'is_active' => true]);
    $actorPosition = Position::create(['name' => 'Kepala Sub Bagian', 'is_active' => true]);
    $actor->positions()->attach($actorPosition->id, ['is_active' => true]);

    $letter = followUpLetter($creator);
    $recipient = followUpRecipient($letter, $creator, $fromPosition, $recipientPosition);
    followUpMemo($letter, $recipient, $creator, $recipientPosition, $actorPosition, isCc: true);

    $this->actingAs($actor)
        ->get(route('memo.create', ['recipient' => $recipient->id, 'key' => followUpKey($letter)]))
        ->assertForbidden();
});

it('forbids storing a follow-up from a position that never received the letter', function () {
    Event::fake([InboxItemCreated::class]);

    $creator = User::factory()->create();
    $actor = followUpActor('memo.store');
    $unrelatedPosition = Position::create(['name' => 'Kepala Bagian Lain', 'is_active' => true]);
    $actor->positions()->attach($unrelatedPosition->id, ['is_active' => true]);
    $fromPosition = Position::create(['name' => 'Sekretaris', 'is_active' => true]);
    $recipientPosition = Position::create(['name' => 'Kepala Bagian Umum', 'is_active' => true]);

    $letter = followUpLetter($creator);
    $recipient = followUpRecipient($letter, $creator, $fromPosition, $recipientPosition);

    $this->actingAs($actor)
        ->post(route('memo.store'), [
            'type' => 'primary',
            'letter_id_primary' => $letter->id,
            'letter_id' => $letter->letter_id,
            'letter_recipient_id' => $recipient->id,
            'letter_receipt_date' => '2026-10-02',
            'penerima' => [['id' => $recipientPosition->id, 'name' => $recipientPosition->name]],
            'catatan' => 'Tindak lanjut',
        ])
        ->assertForbidden();

    expect(Memo::query()->count())->toBe(0);
});

it('lets a memo recipient store a memo follow-up', function () {
    Event::fake([InboxItemCreated::class]);

    $creator = User::factory()->create();
    $actor = followUpActor('memo.store');
    $fromPosition = Position::create(['name' => 'Sekretaris', 'is_active' => true]);
    $recipientPosition = Position::create(['name' => 'Kepala Bagian Umum', 'is_active' => true]);
    $actorPosition = Position::create(['name' => 'Kepala Sub Bagian', 'is_active' => true]);
    $actor->positions()->attach($actorPosition->id, ['is_active' => true]);

    $letter = followUpLetter($creator);
    $recipient = followUpRecipient($letter, $creator, $fromPosition, $recipientPosition);
    followUpMemo($letter, $recipient, $creator, $recipientPosition, $actorPosition);

    $this->actingAs($actor)
        ->post(route('memo.store'), [
            'type' => 'primary',
            'letter_id_primary' => $letter->id,
            'letter_id' => $letter->letter_id,
            'letter_recipient_id' => $recipient->id,
            'letter_receipt_date' => '2026-10-02',
            'penerima' => [['id' => $recipientPosition->id, 'name' => $recipientPosition->name]],
            'catatan' => 'Tindak lanjut',
        ])
        ->assertRedirect();

    expect(Memo::query()->where('to_position_id', $recipientPosition->id)->count())->toBe(1);
});
