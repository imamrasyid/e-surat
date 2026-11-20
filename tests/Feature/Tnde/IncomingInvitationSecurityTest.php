<?php

use App\Actions\Letter\CreateComposeLetterAction;
use App\Models\Letter;
use App\Models\LetterDoc;
use App\Models\LetterRecipient;
use App\Models\Position;
use App\Models\RefKdKlasifikasi;
use App\Models\User;
use App\Notifications\InboxItemNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Position, 2: Position}
 */
function tndeSecurityRegistrar(): array
{
    $registrar = User::factory()->create();
    $registrarPosition = Position::create(['name' => 'Registrator Kearsipan', 'is_active' => true]);
    $targetPosition = Position::create(['name' => 'Kepala Bagian Umum', 'is_active' => true]);

    $registrar->positions()->sync([$registrarPosition->id => ['is_active' => true]]);
    $registrar->givePermissionTo([
        Permission::findOrCreate('compose.create'),
        Permission::findOrCreate('compose.store'),
    ]);

    RefKdKlasifikasi::firstOrCreate(['kode' => 'UND'], ['nama' => 'Undangan', 'is_active' => true]);

    return [$registrar, $registrarPosition, $targetPosition];
}

/**
 * @return array<string, mixed>
 */
function tndeSecurityPayload(Position $target, string $classification = 'T', string $via = 'email'): array
{
    return [
        'pengirim' => 'Sekretariat Daerah',
        'nomor' => '005/1234/2026',
        'tglSuratStr' => '2026-11-01',
        'tglDiterimaStr' => '2026-11-02',
        'perihal' => 'Undangan Rapat Koordinasi TNDE',
        'kategori' => '2',
        'agenda' => 'UND',
        'agendaTahun' => '2026',
        'security_classification' => $classification,
        'received_via' => $via,
        'catatan' => 'Mohon ditindaklanjuti',
        'penerima' => [
            ['id' => $target->id, 'name' => $target->name],
        ],
        'lampiran' => [UploadedFile::fake()->createWithContent('undangan.pdf', '%PDF-1.4 isi undangan')],
    ];
}

function tndeSecurityUserOn(Position $position, string ...$permissions): User
{
    $user = User::factory()->create();
    $user->positions()->attach($position->id, ['is_active' => true]);

    foreach ($permissions as $permission) {
        $user->givePermissionTo(Permission::findOrCreate($permission));
    }

    return $user;
}

it('records registration and read activities for an invitation', function () {
    Storage::fake('local');
    config(['filesystems.default' => 'local', 'company.features' => []]);

    [$registrar, , $targetPosition] = tndeSecurityRegistrar();

    $this->actingAs($registrar)
        ->post(route('app.compose.store'), tndeSecurityPayload($targetPosition, 'T', 'email'))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('app.compose.create'));

    $letter = Letter::sole();
    $recipient = LetterRecipient::sole();

    $registration = $letter->activities()->where('action', 'register_incoming')->sole();

    expect($registration->user_id)->toBe($registrar->id)
        ->and($registration->changes)->toMatchArray([
            'category' => '2',
            'security_classification' => 'T',
            'received_via' => 'email',
        ]);

    $reader = tndeSecurityUserOn($targetPosition, 'undangan.show');

    $this->actingAs($reader)
        ->patch(route('inbox.undangan.show', [
            'recipient' => $recipient->id,
            'letter_id' => $letter->letter_id,
        ]))
        ->assertOk();

    $read = $letter->activities()->where('action', 'read_inbox')->sole();

    expect($read->user_id)->toBe($reader->id)
        ->and($read->changes)->toMatchArray(['recipient_id' => $recipient->id]);

    $this->actingAs($reader)
        ->patch(route('inbox.undangan.show', [
            'recipient' => $recipient->id,
            'letter_id' => $letter->letter_id,
        ]))
        ->assertOk();

    expect($letter->activities()->where('action', 'read_inbox')->count())->toBe(1);
});

it('records registration for a direct action call using its actor', function () {
    Storage::fake('local');
    config(['filesystems.default' => 'local', 'company.features' => []]);

    [$registrar, $registrarPosition, $targetPosition] = tndeSecurityRegistrar();

    $payload = tndeSecurityPayload($targetPosition);
    $lampiran = $payload['lampiran'];
    unset($payload['lampiran']);

    $letter = app(CreateComposeLetterAction::class)
        ->handle($registrar, $registrarPosition, $payload, $lampiran);

    $registration = $letter->activities()->where('action', 'register_incoming')->sole();

    expect($registration->user_id)->toBe($registrar->id)
        ->and($registration->changes)->toMatchArray([
            'category' => '2',
            'security_classification' => 'T',
            'received_via' => 'email',
        ]);
});

it('does not send external notifications for restricted invitations', function () {
    Notification::fake();
    Storage::fake('local');
    config(['filesystems.default' => 'local', 'company.features' => ['email', 'telegram']]);

    [$registrar, , $targetPosition] = tndeSecurityRegistrar();
    $targetUser = tndeSecurityUserOn($targetPosition);
    $targetUser->update(['telegram_chat_id' => '100200300']);

    $this->actingAs($registrar)
        ->post(route('app.compose.store'), tndeSecurityPayload($targetPosition, 'R', 'email'))
        ->assertSessionHasNoErrors();

    // Riwayat database tetap dibuat untuk user internal; hanya kanal eksternal
    // (email/Telegram) yang dilarang untuk klasifikasi R.
    Notification::assertSentTo($targetUser, InboxItemNotification::class, function (InboxItemNotification $notification, array $channels) use ($targetUser) {
        return $channels === ['database']
            && $notification->toDatabase($targetUser)['security_classification'] === 'R';
    });

    expect(LetterRecipient::sole()->read_at)->toBeNull()
        ->and(Letter::sole()->security_classification)->toBe('R');
});

it('denies an unrelated user access to attachment and recipient timeline', function () {
    Storage::fake('local');
    config(['filesystems.default' => 'local', 'company.features' => []]);

    [$registrar, , $targetPosition] = tndeSecurityRegistrar();

    $this->actingAs($registrar)
        ->post(route('app.compose.store'), tndeSecurityPayload($targetPosition))
        ->assertSessionHasNoErrors();

    $letter = Letter::sole();
    $recipient = LetterRecipient::sole();
    $doc = LetterDoc::sole();

    $reader = tndeSecurityUserOn($targetPosition, 'undangan.show');
    $outsider = tndeSecurityUserOn(Position::create(['name' => 'Jabatan Lain', 'is_active' => true]));
    $privileged = tndeSecurityUserOn(
        Position::create(['name' => 'Pengelola Arsip', 'is_active' => true]),
        'sent.show'
    );

    $timelineUrl = route('api.timeline.recipient', $recipient->id);

    $this->actingAs($outsider)->get(route('letter.docs.preview', $doc))->assertForbidden();
    $this->actingAs($outsider)->get($timelineUrl)->assertForbidden();

    $this->actingAs($reader)->get(route('letter.docs.preview', $doc))->assertOk();
    $this->actingAs($reader)->get($timelineUrl)->assertOk()->assertExactJson([]);
    $this->actingAs($registrar)->get($timelineUrl)->assertOk();
    $this->actingAs($privileged)->get($timelineUrl)->assertOk();
});
