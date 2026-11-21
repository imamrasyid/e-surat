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
use App\Services\NotificationCenter;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

// Skenario lama mengasumsikan kanal email dan Telegram aktif; perilaku
// fail-closed diuji di CompanyFeatureGateTest.
beforeEach(fn () => config(['company.features' => ['email', 'telegram']]));

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

/**
 * @return array{0: LetterRecipient|Disposition|Memo, 1: string}
 */
function inboxNotificationItem(string $type, User $sender, Position $senderPosition, Position $ownerPosition, Position $targetPosition): array
{
    if ($type === 'primary') {
        $letter = inboxComposeLetter($sender, $senderPosition, [$targetPosition]);
        $recipient = LetterRecipient::where('letter_id', $letter->id)->sole();

        return [$recipient, route('inbox.primary.show', [
            'recipient' => $recipient->id,
            'letter_id' => $letter->letter_id,
        ])];
    }

    $letter = inboxLetter($sender);
    $recipient = inboxRecipient($letter, $sender, $senderPosition, $ownerPosition);

    if ($type === 'disposition') {
        app(CreateDispositionAction::class)->handle($sender, $senderPosition, [
            'letter_id_primary' => $letter->id,
            'letter_recipient_id' => $recipient->id,
            'penerima' => [['id' => $targetPosition->id]],
        ]);

        $item = Disposition::where('letter_id', $letter->id)->sole();

        return [$item, route('dispo.show', [
            'recipient' => $item->id,
            'letter_id' => $letter->letter_id,
        ])];
    }

    app(CreateMemoAction::class)->handle($sender, $senderPosition, [
        'letter_id_primary' => $letter->id,
        'letter_recipient_id' => $recipient->id,
        'penerima' => [['id' => $targetPosition->id]],
    ]);

    $item = Memo::where('letter_id', $letter->id)->sole();

    return [$item, route('memo.show', [
        'recipient' => $item->id,
        'letter_id' => $letter->letter_id,
    ])];
}

/**
 * @return array{0: User, 1: Position, 2: Position, 3: Position, 4: User}
 */
function inboxNotificationScenario(): array
{
    $sender = User::factory()->create();
    $senderPosition = Position::create(['name' => 'Sekretaris']);
    $ownerPosition = Position::create(['name' => 'Kepala Bagian Umum']);
    $targetPosition = Position::create(['name' => 'Kepala Sub Bagian A']);
    $user = inboxUserOn($targetPosition);

    $user->givePermissionTo([
        Permission::findOrCreate('primary.show'),
        Permission::findOrCreate('undangan.show'),
        Permission::findOrCreate('disposition.show'),
        Permission::findOrCreate('memo.show'),
    ]);

    return [$sender, $senderPosition, $ownerPosition, $targetPosition, $user];
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
            && $notification->toMail($firstTargetUser)->subject === 'Surat Masuk Utama: '.$letter->subject
            && str_contains(
                $notification->toTelegram($firstTargetUser),
                filled($notification->id)
                    ? route('notifications.open', $notification->id)
                    : route('inbox.primary.show', ['recipient' => $item->id, 'letter_id' => $letter->letter_id])
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
        $url = filled($notification->id)
            ? route('notifications.open', $notification->id)
            : route('inbox.undangan.show', [
                'recipient' => $notification->item->id,
                'letter_id' => $letter->letter_id,
            ]);

        return $channels === ['database', 'mail', TelegramChannel::class]
            && $notification->toDatabase($user)['item_type'] === 'invitation'
            && $mail->subject === 'Surat Undangan: '.$letter->subject
            && str_contains($telegram, 'Surat Undangan')
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

    Notification::assertSentTo($emailOnly, InboxItemNotification::class, fn (InboxItemNotification $notification, array $channels) => $channels === ['database', 'mail']);
    Notification::assertSentTo($withTelegram, InboxItemNotification::class, fn (InboxItemNotification $notification, array $channels) => $channels === ['database', 'mail', TelegramChannel::class]);
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

it('carries instructions and followups in disposition and memo notifications', function () {
    $sender = User::factory()->create();
    $senderPosition = Position::create(['name' => 'Sekretaris']);
    $letterOwnerPosition = Position::create(['name' => 'Kepala Bagian Umum']);
    $targetPosition = Position::create(['name' => 'Kepala Sub Bagian A']);
    $user = inboxUserOn($targetPosition);

    $letter = inboxLetter($sender);
    $recipient = inboxRecipient($letter, $sender, $senderPosition, $letterOwnerPosition);

    app(CreateDispositionAction::class)->handle($sender, $senderPosition, [
        'letter_id_primary' => $letter->id,
        'letter_recipient_id' => $recipient->id,
        'penerima' => [['id' => $targetPosition->id]],
        'catatan' => 'Segera diproses',
        'followup' => [['id' => 1, 'name' => 'Tindak lanjut']],
    ]);

    $memoLetter = inboxLetter($sender);
    $memoRecipient = inboxRecipient($memoLetter, $sender, $senderPosition, $letterOwnerPosition);

    app(CreateMemoAction::class)->handle($sender, $senderPosition, [
        'letter_id_primary' => $memoLetter->id,
        'letter_recipient_id' => $memoRecipient->id,
        'penerima' => [['id' => $targetPosition->id]],
        'catatan' => 'Mohon dicek',
    ]);

    $stored = $user->notifications()->get()->keyBy(fn ($notification) => $notification->data['item_type']);

    expect($stored['disposition']->data)->toMatchArray([
        'instruction' => 'Segera diproses',
        'followup' => 'Tindak lanjut',
    ])->and($stored['memo']->data)->toMatchArray([
        'instruction' => 'Mohon dicek',
        'followup' => null,
    ]);

    $items = collect(app(NotificationCenter::class)->history($user)->items())->keyBy('item_type');

    expect($items['disposition'])->toMatchArray([
        'instruction' => 'Segera diproses',
        'followup' => 'Tindak lanjut',
    ])->and($items['memo'])->toMatchArray([
        'instruction' => 'Mohon dicek',
        'followup' => null,
    ]);
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

it('stores an internal database notification with its classification and source', function () {
    $sender = User::factory()->create();
    $senderPosition = Position::create(['name' => 'Sekretaris']);
    $target = Position::create(['name' => 'Kepala Bagian Umum']);
    $user = inboxUserOn($target);

    $letter = inboxComposeLetter($sender, $senderPosition, [$target]);
    $recipient = LetterRecipient::where('letter_id', $letter->id)->sole();

    $notification = $user->notifications()->sole();

    expect($notification->data)->toMatchArray([
        'title' => 'Surat Masuk Utama',
        'body' => $letter->subject,
        'url' => route('inbox.primary.show', [
            'recipient' => $recipient->id,
            'letter_id' => $letter->letter_id,
        ]),
        'item_type' => 'primary',
        'item_id' => $recipient->id,
        'security_classification' => 'B',
        'received_via' => 'other',
    ])->and(array_keys($notification->data))->toBe([
        'title', 'body', 'url', 'item_type', 'item_id', 'security_classification', 'received_via',
    ]);
});

it('marks the notification and its item as read when opened', function (string $type) {
    config(['company.features' => []]);

    [$sender, $senderPosition, $ownerPosition, $targetPosition, $user] = inboxNotificationScenario();
    [$item, $url] = inboxNotificationItem($type, $sender, $senderPosition, $ownerPosition, $targetPosition);
    $notification = $user->notifications()->sole();

    $this->actingAs($user)
        ->patch(route('notifications.read', $notification->id))
        ->assertRedirect($url);

    expect($notification->fresh()->read_at)->not->toBeNull()
        ->and($item->fresh()->read_at)->not->toBeNull();
})->with(['primary', 'disposition', 'memo']);

it('marks the matching notification as read when the item is opened directly', function (string $type) {
    config(['company.features' => []]);

    [$sender, $senderPosition, $ownerPosition, $targetPosition, $user] = inboxNotificationScenario();
    [$item, $showUrl] = inboxNotificationItem($type, $sender, $senderPosition, $ownerPosition, $targetPosition);
    $notification = $user->notifications()->sole();

    $readUrl = match ($type) {
        'primary' => $showUrl,
        'disposition' => route('dispo.update', [
            'disposition' => $item->id,
            'letter_id' => $item->letter->letter_id,
        ]),
        'memo' => route('memo.update', [
            'memo' => $item->id,
            'letter_id' => $item->letter->letter_id,
        ]),
    };

    $response = $this->actingAs($user)->patch($readUrl);

    if ($type === 'primary') {
        $response->assertOk();
    } else {
        $response->assertRedirect($showUrl);
    }

    expect($notification->fresh()->read_at)->not->toBeNull()
        ->and($item->fresh()->read_at)->not->toBeNull();
})->with(['primary', 'disposition', 'memo']);

it('marks every notification and its item as read when reading all', function () {
    config(['company.features' => []]);

    [$sender, $senderPosition, $ownerPosition, $targetPosition, $user] = inboxNotificationScenario();

    [$primary] = inboxNotificationItem('primary', $sender, $senderPosition, $ownerPosition, $targetPosition);
    [$disposition] = inboxNotificationItem('disposition', $sender, $senderPosition, $ownerPosition, $targetPosition);
    [$memo] = inboxNotificationItem('memo', $sender, $senderPosition, $ownerPosition, $targetPosition);

    expect($user->notifications()->count())->toBe(3);

    $this->actingAs($user)
        ->patch(route('notifications.readAll'))
        ->assertRedirect();

    expect($user->unreadNotifications()->count())->toBe(0)
        ->and($primary->fresh()->read_at)->not->toBeNull()
        ->and($disposition->fresh()->read_at)->not->toBeNull()
        ->and($memo->fresh()->read_at)->not->toBeNull();
});

it('falls back to the notification url when item_id is missing', function () {
    config(['company.features' => []]);

    [$sender, $senderPosition, $ownerPosition, $targetPosition, $user] = inboxNotificationScenario();
    [$disposition, $showUrl] = inboxNotificationItem('disposition', $sender, $senderPosition, $ownerPosition, $targetPosition);
    $notification = $user->notifications()->sole();

    $notification->update([
        'data' => array_diff_key($notification->data, ['item_id' => true]),
    ]);

    $this->actingAs($user)
        ->patch(route('notifications.read', $notification->id))
        ->assertRedirect($showUrl);

    expect($disposition->fresh()->read_at)->not->toBeNull();
});

it('opens a notification from an external link and marks both read', function () {
    config(['company.features' => []]);

    [$sender, $senderPosition, $ownerPosition, $targetPosition, $user] = inboxNotificationScenario();
    [$item, $url] = inboxNotificationItem('disposition', $sender, $senderPosition, $ownerPosition, $targetPosition);
    $notification = $user->notifications()->sole();

    $this->actingAs($user)
        ->get(url("/notifications/{$notification->id}/open"))
        ->assertRedirect($url);

    expect($notification->fresh()->read_at)->not->toBeNull()
        ->and($item->fresh()->read_at)->not->toBeNull();
});

it('links external channels to the notification open route', function () {
    config(['company.features' => []]);

    $sender = User::factory()->create();
    $senderPosition = Position::create(['name' => 'Sekretaris']);
    $target = Position::create(['name' => 'Kepala Bagian Umum']);
    $user = inboxUserOn($target);

    $letter = inboxComposeLetter($sender, $senderPosition, [$target]);
    $recipient = LetterRecipient::where('letter_id', $letter->id)->sole();

    $notification = new InboxItemNotification($recipient);
    $notification->id = (string) Str::uuid();
    $openUrl = url("/notifications/{$notification->id}/open");

    expect($notification->toMail($user)->actionUrl)->toBe($openUrl)
        ->and($notification->toTelegram($user))->toContain($openUrl)
        ->and($notification->toWebPush($user, $notification)->toArray()['data']['url'])->toBe($openUrl);
});

it('keeps restricted items in the database only and redacts their push payload', function () {
    $sender = User::factory()->create();
    $from = Position::create(['name' => 'Sekretaris']);
    $to = Position::create(['name' => 'Kepala Bagian Umum']);
    $user = inboxUserOn($to, ['telegram_chat_id' => '123456789']);

    $letter = inboxLetter($sender);
    $letter->update(['security_classification' => 'R', 'received_via' => 'email']);
    $recipient = inboxRecipient($letter, $sender, $from, $to);

    $notification = new InboxItemNotification($recipient);

    expect($notification->via($user))->toBe(['database']);

    expect($notification->toDatabase($user))->toMatchArray([
        'title' => 'Surat Masuk Utama',
        'body' => '(perihal dirahasiakan)',
        'item_type' => 'primary',
        'security_classification' => 'R',
        'received_via' => 'email',
    ]);

    $push = $notification->toWebPush($user, $notification)->toArray();
    $payload = json_encode($push);

    expect($push['data'])->toMatchArray([
        'security_classification' => 'R',
        'received_via' => 'email',
    ])->and($payload)->not->toContain($letter->subject)
        ->and($payload)->not->toContain($letter->sender);
});

it('uses the subject as the web push body for ordinary letters', function () {
    $sender = User::factory()->create();
    $from = Position::create(['name' => 'Sekretaris']);
    $to = Position::create(['name' => 'Kepala Bagian Umum']);
    $user = inboxUserOn($to);

    $letter = inboxLetter($sender);
    $recipient = inboxRecipient($letter, $sender, $from, $to);

    $push = (new InboxItemNotification($recipient))->toWebPush($user, new InboxItemNotification($recipient))->toArray();

    expect($push['title'])->toBe('Surat Masuk Utama')
        ->and($push['body'])->toBe($letter->subject)
        ->and($push['data'])->toMatchArray([
            'security_classification' => 'B',
            'received_via' => 'other',
        ]);
});

it('does not notify when the creating transaction rolls back', function () {
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

    expect(DB::table('notifications')->count())->toBe(0);
});

it('dispatches the notification only after the creating transaction commits', function () {
    Notification::fake();

    $sender = User::factory()->create();
    $senderPosition = Position::create(['name' => 'Sekretaris']);
    $letterOwnerPosition = Position::create(['name' => 'Kepala Bagian Umum']);
    $targetPosition = Position::create(['name' => 'Kepala Sub Bagian A']);

    inboxUserOn($targetPosition);

    $letter = inboxLetter($sender);
    $recipient = inboxRecipient($letter, $sender, $senderPosition, $letterOwnerPosition);

    expect(fn () => app(CreateDispositionAction::class)->handle($sender, $senderPosition, [
        'letter_id_primary' => $letter->id,
        'letter_recipient_id' => $recipient->id,
        'letter_receipt_date' => '2026-10-02',
        'penerima' => [['id' => $targetPosition->id]],
        'penerima_cc' => [['id' => 999999]],
    ]))->toThrow(QueryException::class);

    // Event harus ditunda sampai commit; fake menangkap dispatch sinkron
    // seandainya ShouldDispatchAfterCommit hilang.
    Notification::assertNothingSent();
});
