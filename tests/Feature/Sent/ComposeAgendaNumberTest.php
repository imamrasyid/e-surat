<?php

use App\Actions\Letter\CreateComposeLetterAction;
use App\Models\Position;
use App\Models\RefAgendaSurat;
use App\Models\RefKdKlasifikasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function composePayload(Position $recipientPosition): array
{
    return [
        'pengirim' => 'Dinas Pendidikan Provinsi',
        'nomor' => '005/1234/2026',
        'tglSuratStr' => '2026-10-01',
        'tglDiterimaStr' => '2026-10-02',
        'perihal' => 'Undangan Rapat Koordinasi',
        'kategori' => '1',
        'agenda' => 'ND',
        'agendaTahun' => '2026',
        'catatan' => 'Mohon ditindaklanjuti',
        'penerima' => [
            ['id' => $recipientPosition->id, 'name' => $recipientPosition->name],
        ],
    ];
}

function composeSenderWith(string $agendaCode): array
{
    $user = User::factory()->create();
    $senderPosition = Position::create(['name' => 'Sekretaris', 'is_active' => true]);
    $recipientPosition = Position::create(['name' => 'Kepala Bagian Umum', 'is_active' => true]);

    $kode = RefKdKlasifikasi::create([
        'kode' => $agendaCode,
        'nama' => $agendaCode === 'ND' ? 'Nota Dinas' : 'Surat Tugas',
        'is_active' => true,
    ]);

    return [$user, $senderPosition, composePayload($recipientPosition), $kode];
}

it('numbers sequential letters under one classification without gaps', function () {
    [$user, $senderPosition, $payload] = composeSenderWith('ND');

    foreach ([1, 2, 3] as $expectedNumber) {
        app(CreateComposeLetterAction::class)->handle($user, $senderPosition, $payload);

        expect(
            RefAgendaSurat::query()
                ->where('tahun', 2026)
                ->whereRelation('klasifikasi', 'kode', 'ND')
                ->count()
        )->toBe($expectedNumber)
            ->and(RefAgendaSurat::query()->where('nomor_agenda', "{$expectedNumber}/ND/2026")->exists())
            ->toBeTrue();
    }
});

it('starts over at one for a new classification code', function () {
    [$user, $senderPosition, $payload] = composeSenderWith('ND');

    app(CreateComposeLetterAction::class)->handle($user, $senderPosition, $payload);

    [$user2, $senderPosition2, $payload2, $kodeSt] = composeSenderWith('ST');
    app(CreateComposeLetterAction::class)->handle(
        $user2,
        $senderPosition2,
        [...$payload2, 'agenda' => $kodeSt->kode]
    );

    expect(RefAgendaSurat::query()->where('tahun', 2026)->count())->toBe(2)
        ->and(RefAgendaSurat::query()
            ->where('tahun', 2026)
            ->whereRelation('klasifikasi', 'kode', 'ST')
            ->sole()
            ->nomor_urut)
        ->toBe(1);
});
