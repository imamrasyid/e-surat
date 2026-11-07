<?php

use App\Actions\Letter\CreateComposeLetterAction;
use App\Models\Letter;
use App\Models\LetterDoc;
use App\Models\Position;
use App\Models\RefKdKlasifikasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('stores compose attachments on the private default disk with a server generated path', function () {
    config([
        'filesystems.default' => 'local',
        'company.features' => [],
    ]);
    Storage::fake('local');

    RefKdKlasifikasi::create(['kode' => '000', 'nama' => 'Umum', 'is_active' => true]);

    $creator = User::factory()->create();
    $fromPosition = Position::create(['name' => 'Sekretaris', 'is_active' => true]);
    $toPosition = Position::create(['name' => 'Kepala Bagian Umum', 'is_active' => true]);

    $lampiran = UploadedFile::fake()->createWithContent('Surat Rahasia.pdf', '%PDF-1.4 isi lampiran');

    app(CreateComposeLetterAction::class)->handle(
        $creator,
        $fromPosition,
        [
            'pengirim' => 'Dinas Pendidikan Provinsi',
            'nomor' => '005/1234/2026',
            'tglSuratStr' => '2026-10-01',
            'tglDiterimaStr' => '2026-10-02',
            'perihal' => 'Undangan Rapat Koordinasi',
            'kategori' => '1',
            'agenda' => '000',
            'agendaTahun' => '2026',
            'catatan' => 'Mohon hadir tepat waktu.',
            'penerima' => [
                ['id' => $toPosition->id, 'name' => $toPosition->name],
            ],
        ],
        [$lampiran]
    );

    $doc = LetterDoc::sole();

    expect($doc->name)->toBe('Surat Rahasia.pdf')
        ->and($doc->path)->toBeString()
        ->and($doc->path)->toStartWith('letter-docs/')
        ->and($doc->path)->toEndWith('.pdf')
        ->and($doc->path)->not->toContain('Surat Rahasia')
        ->and($doc->url)->toBeNull();

    Storage::disk('local')->assertExists((string) $doc->path);

    expect(Storage::disk('local')->get((string) $doc->path))->toBe('%PDF-1.4 isi lampiran');
});

it('copies legacy public-disk files to the private default disk', function () {
    config(['filesystems.default' => 'local']);
    Storage::fake('local');
    Storage::fake('public');

    $letter = Letter::create([
        'letter_id' => (string) Str::ulid(),
        'sender' => 'Dinas Pendidikan Provinsi',
        'date' => '2026-10-01',
        'receipt_date' => '2026-10-02',
        'subject' => 'Surat Legacy',
        'category' => '1',
        'agenda' => '1/ND/2026',
        'created_by' => User::factory()->create()->id,
    ]);

    $doc = LetterDoc::create([
        'letter_id' => $letter->id,
        'name' => 'lama.pdf',
        'path' => 'attachment/2026-10-02/lama.pdf',
    ]);

    Storage::disk('public')->put((string) $doc->path, '%PDF-1.4 legacy');

    $this->artisan('letter-docs:migrate-storage', ['--source-disk' => 'public'])
        ->assertSuccessful();

    Storage::disk('local')->assertExists((string) $doc->path);
    expect(Storage::disk('local')->get((string) $doc->path))->toBe('%PDF-1.4 legacy');
});
