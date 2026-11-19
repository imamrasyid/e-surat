<?php

use App\Actions\Letter\CreateComposeLetterAction;
use App\Models\Letter;
use App\Models\LetterDoc;
use App\Models\LetterRecipient;
use App\Models\Position;
use App\Models\RefAgendaSurat;
use App\Models\RefKdKlasifikasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Position, 2: Position}
 */
function tndeRegistrar(): array
{
    $user = User::factory()->create();
    $senderPosition = Position::create(['name' => 'Registrator Kearsipan', 'is_active' => true]);
    $recipientPosition = Position::create(['name' => 'Kepala Bagian Umum', 'is_active' => true]);

    $user->positions()->sync([$senderPosition->id => ['is_active' => true]]);
    $user->givePermissionTo([
        Permission::findOrCreate('compose.create'),
        Permission::findOrCreate('compose.store'),
    ]);

    RefKdKlasifikasi::create(['kode' => 'UND', 'nama' => 'Undangan', 'is_active' => true]);

    return [$user, $senderPosition, $recipientPosition];
}

/**
 * @return array<string, mixed>
 */
function tndeInvitationPayload(Position $recipientPosition): array
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
        'security_classification' => 'T',
        'received_via' => 'email',
        'catatan' => 'Mohon ditindaklanjuti',
        'penerima' => [
            ['id' => $recipientPosition->id, 'name' => $recipientPosition->name],
        ],
        'lampiran' => [UploadedFile::fake()->createWithContent('undangan.pdf', '%PDF-1.4 isi undangan')],
    ];
}

it('registers an incoming invitation with TNDE metadata and the existing agenda flow', function () {
    Storage::fake('local');
    config(['filesystems.default' => 'local', 'company.features' => []]);

    [$user, , $recipientPosition] = tndeRegistrar();

    $this->actingAs($user)
        ->post(route('app.compose.store'), tndeInvitationPayload($recipientPosition))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('app.compose.create'));

    $letter = Letter::sole();

    expect($letter->type)->toBe('in')
        ->and($letter->category)->toBe('2')
        ->and($letter->security_classification)->toBe('T')
        ->and($letter->received_via)->toBe('email')
        ->and($letter->agenda)->not->toBeEmpty()
        ->and(RefAgendaSurat::where('nomor_agenda', $letter->agenda)->exists())->toBeTrue();

    expect(LetterRecipient::sole()->received_at)->not->toBeNull();

    $doc = LetterDoc::sole();
    expect($doc->path)->toStartWith('letter-docs/')
        ->and($doc->url)->toBeNull();

    Storage::disk('local')->assertExists((string) $doc->path);
});

it('defaults legacy direct Action callers to B and other', function () {
    Storage::fake('local');
    config(['filesystems.default' => 'local', 'company.features' => []]);

    [$user, $senderPosition, $recipientPosition] = tndeRegistrar();

    $letter = app(CreateComposeLetterAction::class)->handle(
        $user,
        $senderPosition,
        [
            'pengirim' => 'Sekretariat Daerah',
            'nomor' => '005/1234/2026',
            'tglSuratStr' => '2026-11-01',
            'tglDiterimaStr' => '2026-11-02',
            'perihal' => 'Undangan Rapat Koordinasi TNDE',
            'kategori' => '2',
            'agenda' => 'UND',
            'agendaTahun' => '2026',
            'penerima' => [
                ['id' => $recipientPosition->id, 'name' => $recipientPosition->name],
            ],
        ],
        [UploadedFile::fake()->createWithContent('undangan.pdf', '%PDF-1.4 isi undangan')],
    );

    $persisted = Letter::findOrFail($letter->id);

    expect($persisted->type)->toBe('in')
        ->and($persisted->category)->toBe('2')
        ->and($persisted->security_classification)->toBe('B')
        ->and($persisted->received_via)->toBe('other');
});

it('rejects an unsupported security class or inactive recipient', function () {
    Storage::fake('local');
    config(['filesystems.default' => 'local', 'company.features' => []]);

    [$user, , $recipientPosition] = tndeRegistrar();

    $this->actingAs($user)
        ->post(route('app.compose.store'), [
            ...tndeInvitationPayload($recipientPosition),
            'security_classification' => 'public',
        ])
        ->assertSessionHasErrors('security_classification');

    expect(Letter::count())->toBe(0);

    $this->actingAs($user)
        ->post(route('app.compose.store'), [
            ...tndeInvitationPayload($recipientPosition),
            'received_via' => 'courier',
        ])
        ->assertSessionHasErrors('received_via');

    expect(Letter::count())->toBe(0);

    $inactivePosition = Position::create(['name' => 'Jabatan Nonaktif', 'is_active' => false]);

    $this->actingAs($user)
        ->post(route('app.compose.store'), [
            ...tndeInvitationPayload($inactivePosition),
            'penerima' => [
                ['id' => $inactivePosition->id, 'name' => $inactivePosition->name],
            ],
        ])
        ->assertSessionHasErrors('penerima.0.id');

    expect(Letter::count())->toBe(0);
});
