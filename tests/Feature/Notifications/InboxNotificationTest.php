<?php

use App\Actions\Disposition\CreateDispositionAction;
use App\Actions\Letter\CreateComposeLetterAction;
use App\Actions\Letter\UpdateSentRecipientsAction;
use App\Actions\Memo\CloseMemoAction;
use App\Actions\Memo\CreateMemoAction;
use App\Models\Disposition;
use App\Models\Letter;
use App\Models\LetterRecipient;
use App\Models\Memo;
use App\Models\Position;
use App\Models\RefKdKlasifikasi;
use App\Models\User;
use App\Notifications\Channels\TelegramChannel;
use App\Notifications\InboxItemNotification;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function inboxUserOn(Position $position, array $attributes = []): User
{
    $user = User::factory()->create($attributes);

    $user->positions()->attach($position->id, ['is_active' => true]);

    return $user;
}

function inboxLetter(User $creator, string $category = '1'): Letter
{
    return Letter::create([
        'letter_id' => (string) Str::ulid(),
        'sender' => 'Dinas Pendidikan Provinsi',
        'number' => '005/1234/2026',
        'date' => '2026-10-01',
        'receipt_date' => '2026-10-02',
        'subject' => 'Undangan Rapat Koordinasi',
        'category' => $category,
        'agenda' => '1/ND/2026',
        'created_by' => $creator->id,
    ]);
}

function inboxRecipient(Letter $letter, User $sender, Position $from, Position $to): LetterRecipient
{
    return LetterRecipient::create([
        'letter_id' => $letter->id,
        'from_user_id' => $sender->id,
        'from_position_id' => $from->id,
        'to_position_id' => $to->id,
        'received_at' => now(),
    ]);
}

/**
 * @param  array<int, Position>  $recipients
 */
function inboxComposeLetter(User $sender, Position $senderPosition, array $recipients, string $category = '1'): Letter
{
    RefKdKlasifikasi::firstOrCreate(['kode' => 'ND'], ['nama' => 'Nota Dinas']);

    return app(CreateComposeLetterAction::class)->handle($sender, $senderPosition, [
        'pengirim' => 'Dinas Pendidikan Provinsi',
        'nomor' => '005/1234/2026',
        'tglSuratStr' => '2026-10-01',
        'tglDiterimaStr' => '2026-10-02',
        'perihal' => 'Undangan Rapat Koordinasi',
        'kategori' => $category,
        'agenda' => 'ND',
        'agendaTahun' => 2026,
        'penerima' => array_map(fn (Position $position) => [
            'id' => $position->id,
            'name' => $position->name,
        ], $recipients),
        'catatan' => 'Mohon ditindaklanjuti',
    ]);
}

it('notifies every active user holding the target position of a primary letter', function () {
    Notification::fake();

    $sender = User::factory()->create();
    $senderPosition = Position::create(['name' => 'Sekretaris']);

    $target = Position::create(['name' => 'Kepala Bagian Umum']);
    $firstTargetUser = inboxUserOn($target);
    $secondTargetUser = inboxUserOn($target);
    $inactiveTargetUser = inboxUserOn($target, ['is_active' => false]);

    $otherPosition = Position::create(['name' => 'Kepala Bagian Lain']);
    $otherUser = inboxUserOn($otherPosition);

    $letter = inboxComposeLetter($sender, $senderPosition, [$target], '1');

    Notification::assertSentTo($firstTargetUser, InboxItemNotification::class, function (InboxItemNotification $notification) use ($firstTargetUser, $target, $letter) {
        $item = $notification->item;

        return $item instanceof LetterRecipient
            && $item->to_position_id === $target->id
            && $notification->toMail($firstTargetUser)->subject === 'Surat masuk primary baru: '.$letter->subject
            && str_contains(
                $notification->toTelegram($firstTargetUser),
                route('inbox.primary.show', ['recipient' => $item->id, 'letter_id' => $letter->letter_id])
            );
    });

    Notification::assertSentTo($secondTargetUser, InboxItemNotification::class);

    Notification::assertNotSentTo([$inactiveTargetUser, $otherUser], InboxItemNotification::class);
});

it('uses the invitation label and route for category 2 letters', function () {
    Notification::fake();

    $sender = User::factory()->create();
    $senderPosition = Position::create(['name' => 'Sekretaris']);
    $target = Position::create(['name' => 'Kepala Bagian Umum']);

    $user = inboxUserOn($target, ['telegram_chat_id' => '100200300']);

    $letter = inboxComposeLetter($sender, $senderPosition, [$target], '2');

    Notification::assertSentTo($user, InboxItemNotification::class, function (InboxItemNotification $notification, array $channels) use ($user, $letter) {
        $mail = $notification->toMail($user);
        $telegram = $notification->toTelegram($user);
        $url = route('inbox.undangan.show', [
            'recipient' => $notification->item->id,
            'letter_id' => $letter->letter_id,
        ]);

        return $channels === ['mail', TelegramChannel::class]
            && $mail->subject === 'Surat undangan baru: '.$letter->subject
            && str_contains($telegram, 'Surat undangan baru')
            && str_contains($telegram, $url);
    });
});

it('only includes the channels whose contact is available', function () {
    Notification::fake();

    $sender = User::factory()->create();
    $senderPosition = Position::create(['name' => 'Sekretaris']);
    $target = Position::create(['name' => 'Kepala Bagian Umum']);

    $emailOnly = inboxUserOn($target);
    $withTelegram = inboxUserOn($target, ['telegram_chat_id' => '445566778']);

    inboxComposeLetter($sender, $senderPosition, [$target]);

    Notification::assertSentTo($emailOnly, InboxItemNotification::class, fn (InboxItemNotification $notification, array $channels) => $channels === ['mail']);
    Notification::assertSentTo($withTelegram, InboxItemNotification::class, fn (InboxItemNotification $notification, array $channels) => $channels === ['mail', TelegramChannel::class]);
});

it('notifies primary and cc recipients of a new disposition', function () {
    Notification::fake();

    $sender = User::factory()->create();
    $senderPosition = Position::create(['name' => 'Sekretaris']);
    $letterOwnerPosition = Position::create(['name' => 'Kepala Bagian Umum']);
    $primaryPosition = Position::create(['name' => 'Kepala Sub Bagian A']);
    $ccPosition = Position::create(['name' => 'Kepala Sub Bagian B']);

    $primaryUser = inboxUserOn($primaryPosition);
    $ccUser = inboxUserOn($ccPosition);

    $letter = inboxLetter($sender);
    $recipient = inboxRecipient($letter, $sender, $senderPosition, $letterOwnerPosition);

    app(CreateDispositionAction::class)->handle($sender, $senderPosition, [
        'letter_id_primary' => $letter->id,
        'letter_recipient_id' => $recipient->id,
        'letter_receipt_date' => '2026-10-02',
        'penerima' => [['id' => $primaryPosition->id]],
        'penerima_cc' => [['id' => $ccPosition->id]],
        'catatan' => 'Mohon ditindaklanjuti',
    ]);

    Notification::assertSentTo($primaryUser, InboxItemNotification::class, fn (InboxItemNotification $notification) => $notification->item instanceof Disposition
        && $notification->item->to_position_id === $primaryPosition->id
        && ! $notification->item->is_cc);

    Notification::assertSentTo($ccUser, InboxItemNotification::class, fn (InboxItemNotification $notification) => $notification->item instanceof Disposition
        && $notification->item->to_position_id === $ccPosition->id
        && (bool) $notification->item->is_cc);
});

it('notifies primary and cc recipients of a new memo', function () {
    Notification::fake();

    $sender = User::factory()->create();
    $senderPosition = Position::create(['name' => 'Sekretaris']);
    $letterOwnerPosition = Position::create(['name' => 'Kepala Bagian Umum']);
    $primaryPosition = Position::create(['name' => 'Kepala Sub Bagian A']);
    $ccPosition = Position::create(['name' => 'Kepala Sub Bagian B']);

    $primaryUser = inboxUserOn($primaryPosition);
    $ccUser = inboxUserOn($ccPosition);

    $letter = inboxLetter($sender);
    $recipient = inboxRecipient($letter, $sender, $senderPosition, $letterOwnerPosition);

    app(CreateMemoAction::class)->handle($sender, $senderPosition, [
        'letter_id_primary' => $letter->id,
        'letter_recipient_id' => $recipient->id,
        'letter_receipt_date' => '2026-10-02',
        'penerima' => [['id' => $primaryPosition->id]],
        'penerima_cc' => [['id' => $ccPosition->id]],
        'catatan' => 'Mohon ditindaklanjuti',
    ]);

    Notification::assertSentTo($primaryUser, InboxItemNotification::class, fn (InboxItemNotification $notification) => $notification->item instanceof Memo
        && $notification->item->to_position_id === $primaryPosition->id
        && ! $notification->item->is_cc);

    Notification::assertSentTo($ccUser, InboxItemNotification::class, fn (InboxItemNotification $notification) => $notification->item instanceof Memo
        && $notification->item->to_position_id === $ccPosition->id
        && (bool) $notification->item->is_cc);
});

it('notifies the recipient of a closing memo', function () {
    Notification::fake();

    $sender = User::factory()->create();
    $senderPosition = Position::create(['name' => 'Sekretaris']);
    $targetPosition = Position::create(['name' => 'Kepala Bagian Umum']);

    $targetUser = inboxUserOn($targetPosition);

    $letter = inboxLetter($sender);
    $recipient = inboxRecipient($letter, $sender, $senderPosition, $targetPosition);

    app(CloseMemoAction::class)->handle($sender, $senderPosition, [
        'letter_id_primary' => $letter->id,
        'letter_recipient_id' => $recipient->id,
        'to_position_id' => $targetPosition->id,
        'catatan' => 'Surat ditutup',
    ]);

    Notification::assertSentTo($targetUser, InboxItemNotification::class, fn (InboxItemNotification $notification) => $notification->item instanceof Memo
        && $notification->item->to_position_id === $targetPosition->id);

    expect($letter->refresh()->status)->toBe('close');
});

it('only notifies new and reassigned recipients when sent recipients are updated', function () {
    Notification::fake();

    $sender = User::factory()->create();
    $senderPosition = Position::create(['name' => 'Sekretaris']);

    $stayPosition = Position::create(['name' => 'Kepala Bagian Tetap']);
    $movedFromPosition = Position::create(['name' => 'Kepala Bagian Lama']);
    $movedToPosition = Position::create(['name' => 'Kepala Bagian Baru']);
    $addedPosition = Position::create(['name' => 'Kepala Bagian Tambahan']);

    $stayUser = inboxUserOn($stayPosition);
    $movedFromUser = inboxUserOn($movedFromPosition);
    $movedToUser = inboxUserOn($movedToPosition);
    $addedUser = inboxUserOn($addedPosition);

    $letter = inboxLetter($sender);

    $stayRecipient = inboxRecipient($letter, $sender, $senderPosition, $stayPosition);

    $movedRecipient = inboxRecipient($letter, $sender, $senderPosition, $movedFromPosition);
    $movedRecipient->update(['read_at' => now()]);

    $this->actingAs($sender);

    app(UpdateSentRecipientsAction::class)->handle($letter, $sender, $senderPosition, [
        ['id' => $stayRecipient->id, 'to_position_id' => $stayPosition->id, 'instruction' => 'Tetap'],
        ['id' => $movedRecipient->id, 'to_position_id' => $movedToPosition->id, 'instruction' => 'Pindah'],
        ['id' => null, 'to_position_id' => $addedPosition->id, 'instruction' => 'Baru'],
    ]);

    Notification::assertNotSentTo([$stayUser, $movedFromUser], InboxItemNotification::class);

    Notification::assertSentTo($movedToUser, InboxItemNotification::class, fn (InboxItemNotification $notification) => $notification->item instanceof LetterRecipient
        && $notification->item->to_position_id === $movedToPosition->id);

    Notification::assertSentTo($addedUser, InboxItemNotification::class, fn (InboxItemNotification $notification) => $notification->item instanceof LetterRecipient
        && $notification->item->to_position_id === $addedPosition->id);

    $movedRecipient->refresh();

    expect($movedRecipient->to_position_id)->toBe($movedToPosition->id)
        ->and($movedRecipient->read_at)->toBeNull()
        ->and($movedRecipient->received_at)->not->toBeNull();
});

it('does not notify when the creating transaction rolls back', function () {
    Notification::fake();

    $sender = User::factory()->create();
    $senderPosition = Position::create(['name' => 'Sekretaris']);
    $letterOwnerPosition = Position::create(['name' => 'Kepala Bagian Umum']);
    $targetPosition = Position::create(['name' => 'Kepala Sub Bagian A']);

    inboxUserOn($targetPosition);

    $letter = inboxLetter($sender);
    $recipient = inboxRecipient($letter, $sender, $senderPosition, $letterOwnerPosition);

    // Penerima cc dengan jabatan tidak dikenal memicu pelanggaran foreign key
    // setelah penerima utama tersimpan, sehingga transaksi di-rollback.
    expect(fn () => app(CreateDispositionAction::class)->handle($sender, $senderPosition, [
        'letter_id_primary' => $letter->id,
        'letter_recipient_id' => $recipient->id,
        'letter_receipt_date' => '2026-10-02',
        'penerima' => [['id' => $targetPosition->id]],
        'penerima_cc' => [['id' => 999999]],
    ]))->toThrow(QueryException::class);

    expect(Disposition::where('letter_id', $letter->id)->count())->toBe(0);

    Notification::assertNothingSent();
});
