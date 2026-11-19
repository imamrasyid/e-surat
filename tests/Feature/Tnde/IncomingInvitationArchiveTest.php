<?php

use App\Models\Letter;
use App\Models\LetterActivity;
use App\Models\LetterDoc;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function tndeArchiveUserWith(string ...$permissions): User
{
    $user = User::factory()->create();

    foreach ($permissions as $permission) {
        $user->givePermissionTo(Permission::findOrCreate($permission));
    }

    return $user;
}

/**
 * @param  array<string, mixed>  $attributes
 */
function tndeArchiveLetter(User $registrar, array $attributes = []): Letter
{
    return Letter::create(array_merge([
        'letter_id' => (string) Str::ulid(),
        'sender' => 'Sekretariat Daerah',
        'number' => '005/1234/2026',
        'date' => '2026-11-01',
        'receipt_date' => '2026-11-02',
        'subject' => 'Undangan Rapat Koordinasi TNDE',
        'category' => '2',
        'security_classification' => 'T',
        'received_via' => 'email',
        'agenda' => 'UND-2026-001',
        'type' => 'in',
        'status' => 'close',
        'created_by' => $registrar->id,
    ], $attributes));
}

it('lists only closed incoming letters and supports search', function () {
    $viewer = tndeArchiveUserWith('arsip.index');
    $registrar = User::factory()->create();

    $closed = tndeArchiveLetter($registrar, ['agenda' => 'UND-2026-001']);
    tndeArchiveLetter($registrar, ['agenda' => 'UND-2026-002', 'status' => 'open']);
    tndeArchiveLetter($registrar, ['agenda' => 'OUT-2026-003', 'type' => 'out']);
    tndeArchiveLetter($registrar, [
        'agenda' => 'UND-2026-009',
        'sender' => 'Dinas Lain',
        'number' => '007/9999/2026',
        'subject' => 'Lain hal',
    ]);

    $this->actingAs($viewer)
        ->get(route('arsip.index', ['search' => 'Sekretariat Daerah']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('archive/Index')
            ->has('letters.data', 1)
            ->where('letters.data.0.letter_id', $closed->letter_id)
            ->where('letters.data.0.status', 'close')
            ->where('letters.total', 1)
        );

    $this->actingAs($viewer)
        ->get(route('arsip.index', ['search' => 'UND-2026-001']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('archive/Index')
            ->has('letters.data', 1)
            ->where('letters.data.0.letter_id', $closed->letter_id)
            ->where('letters.total', 1)
        );
});

it('requires archive permission and allows a permitted user to preview its private document', function () {
    Storage::fake('local');
    config(['filesystems.default' => 'local']);

    $registrar = User::factory()->create();
    $letter = tndeArchiveLetter($registrar);

    $path = 'letter-docs/2026/11/'.Str::ulid().'.pdf';
    Storage::disk('local')->put($path, '%PDF-1.4 arsip undangan');

    $doc = LetterDoc::create([
        'letter_id' => $letter->id,
        'name' => 'Arsip Undangan.pdf',
        'path' => $path,
    ]);

    LetterActivity::create([
        'letter_id' => $letter->id,
        'user_id' => $registrar->id,
        'action' => 'register_incoming',
        'description' => 'Mendaftarkan surat masuk',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('arsip.index'))
        ->assertForbidden();

    $this->actingAs(tndeArchiveUserWith('arsip.index'))
        ->get(route('arsip.show', $letter->letter_id))
        ->assertForbidden();

    $archivist = tndeArchiveUserWith('arsip.index', 'arsip.show');

    $this->actingAs($archivist)
        ->get(route('arsip.show', $letter->letter_id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('archive/Show')
            ->where('letter.letter_id', $letter->letter_id)
            ->where('letter.security_classification', 'T')
            ->has('letter.activities', 1)
            ->where('letter.activities.0.description', 'Mendaftarkan surat masuk')
            ->where('letter.activities.0.user.name', $registrar->name)
        );

    $this->actingAs($archivist)
        ->get(route('letter.docs.preview', $doc))
        ->assertOk()
        ->assertStreamedContent('%PDF-1.4 arsip undangan');

    $open = tndeArchiveLetter($registrar, ['status' => 'open']);
    $outgoing = tndeArchiveLetter($registrar, ['type' => 'out']);

    $this->actingAs($archivist)->get(route('arsip.show', $open->letter_id))->assertNotFound();
    $this->actingAs($archivist)->get(route('arsip.show', $outgoing->letter_id))->assertNotFound();
});
