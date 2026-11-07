<?php

use App\Models\Letter;
use App\Models\LetterDoc;
use App\Models\LetterRecipient;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function letterDocForDownload(): LetterDoc
{
    $creator = User::factory()->create();

    $letter = Letter::create([
        'letter_id' => (string) Str::ulid(),
        'sender' => 'Dinas Pendidikan Provinsi',
        'number' => '005/1234/2026',
        'date' => '2026-10-01',
        'receipt_date' => '2026-10-02',
        'subject' => 'Undangan Rapat Koordinasi',
        'category' => '1',
        'agenda' => '1/ND/2026',
        'created_by' => $creator->id,
    ]);

    $path = 'letter-docs/2026/10/'.Str::ulid().'.pdf';

    Storage::disk(config('filesystems.default'))->put($path, '%PDF-1.4 lampiran rahasia');

    return LetterDoc::create([
        'letter_id' => $letter->id,
        'name' => 'Surat Rahasia.pdf',
        'path' => $path,
    ]);
}

beforeEach(function () {
    config(['filesystems.default' => 'local']);
    Storage::fake('local');
});

it('lets the letter creator download the attachment', function () {
    $doc = letterDocForDownload();

    $this->actingAs(User::findOrFail($doc->letter->created_by))
        ->get(route('letter.docs.download', $doc))
        ->assertOk()
        ->assertDownload('Surat Rahasia.pdf')
        ->assertStreamedContent('%PDF-1.4 lampiran rahasia');
});

it('lets a user holding the recipient position download the attachment', function () {
    $doc = letterDocForDownload();

    $position = Position::create(['name' => 'Kepala Bagian Umum', 'is_active' => true]);
    $recipient = User::factory()->create();
    $recipient->positions()->sync([
        $position->id => ['start_date' => now()->toDateString(), 'end_date' => null, 'is_active' => true],
    ]);

    LetterRecipient::create([
        'letter_id' => $doc->letter_id,
        'from_user_id' => $doc->letter->created_by,
        'from_position_id' => Position::create(['name' => 'Sekretaris'])->id,
        'to_position_id' => $position->id,
        'received_at' => now(),
    ]);

    $this->actingAs($recipient)
        ->get(route('letter.docs.download', $doc))
        ->assertOk()
        ->assertDownload('Surat Rahasia.pdf');
});

it('forbids users without access to the letter', function () {
    $doc = letterDocForDownload();
    $outsider = User::factory()->create();

    $this->actingAs($outsider)
        ->get(route('letter.docs.download', $doc))
        ->assertForbidden();
});

it('forbids guests', function () {
    $doc = letterDocForDownload();

    $this->get(route('letter.docs.download', $doc))->assertRedirect('/login');
});

it('returns 404 for an inactive or missing attachment', function () {
    $doc = letterDocForDownload();
    $creator = User::findOrFail($doc->letter->created_by);

    $doc->update(['is_active' => false]);
    $this->actingAs($creator)->get(route('letter.docs.download', $doc))->assertNotFound();

    $doc->update(['is_active' => true]);
    Storage::disk('local')->delete((string) $doc->path);
    $this->actingAs($creator)->get(route('letter.docs.download', $doc))->assertNotFound();
});

it('never exposes the storage path and derives the download url', function () {
    $doc = letterDocForDownload();

    $serialized = $doc->toArray();

    expect($serialized)->not->toHaveKey('path')
        ->and($serialized)->not->toHaveKey('url')
        ->and($serialized['download_url'])->toBe(route('letter.docs.download', $doc->id))
        ->and($serialized['preview_url'])->toBe(route('letter.docs.preview', $doc->id));
});

it('streams the attachment inline so the browser can render it', function () {
    $doc = letterDocForDownload();

    $response = $this->actingAs(User::findOrFail($doc->letter->created_by))
        ->get(route('letter.docs.preview', $doc));

    $response->assertOk()
        ->assertStreamedContent('%PDF-1.4 lampiran rahasia')
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('X-Content-Type-Options', 'nosniff');

    expect($response->headers->get('Content-Disposition'))->toStartWith('inline')
        ->and($response->headers->get('Cache-Control'))
        ->toContain('private')
        ->toContain('no-store');
});

it('forbids users without access to the letter from previewing', function () {
    $doc = letterDocForDownload();

    $this->actingAs(User::factory()->create())
        ->get(route('letter.docs.preview', $doc))
        ->assertForbidden();
});

it('forbids guests from previewing', function () {
    $doc = letterDocForDownload();

    $this->get(route('letter.docs.preview', $doc))->assertRedirect('/login');
});

it('returns 404 when previewing an inactive or missing attachment', function () {
    $doc = letterDocForDownload();
    $creator = User::findOrFail($doc->letter->created_by);

    $doc->update(['is_active' => false]);
    $this->actingAs($creator)->get(route('letter.docs.preview', $doc))->assertNotFound();

    $doc->update(['is_active' => true]);
    Storage::disk('local')->delete((string) $doc->path);
    $this->actingAs($creator)->get(route('letter.docs.preview', $doc))->assertNotFound();
});
