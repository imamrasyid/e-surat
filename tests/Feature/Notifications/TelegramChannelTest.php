<?php

use App\Models\Letter;
use App\Models\LetterRecipient;
use App\Models\Position;
use App\Models\User;
use App\Notifications\InboxItemNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

// Kanal Telegram hanya dipakai bila feature aktif pada server.
beforeEach(fn () => config(['company.features' => ['telegram']]));

function telegramChannelItem(): LetterRecipient
{
    $creator = User::factory()->create();
    $fromPosition = Position::create(['name' => 'Sekretaris']);
    $toPosition = Position::create(['name' => 'Kepala Bagian Umum']);

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

    return LetterRecipient::create([
        'letter_id' => $letter->id,
        'from_user_id' => $creator->id,
        'from_position_id' => $fromPosition->id,
        'to_position_id' => $toPosition->id,
        'received_at' => now(),
    ]);
}

function telegramChannelUser(?string $chatId = null): User
{
    $user = User::factory()->create();

    if ($chatId !== null) {
        $user->telegram_chat_id = $chatId;
        $user->save();
    }

    return $user;
}

it('sends the notification text to the user chat id', function () {
    Http::preventStrayRequests();
    Http::fake(['api.telegram.org/bot*/sendMessage' => Http::response(['ok' => true])]);

    config(['services.telegram.bot_token' => 'test-token']);

    $user = telegramChannelUser('987654321');
    $item = telegramChannelItem();

    $user->notifyNow(new InboxItemNotification($item));

    Http::assertSent(fn (Request $request) => $request->url() === 'https://api.telegram.org/bottest-token/sendMessage'
        && $request['chat_id'] === '987654321'
        && str_contains($request['text'], 'Surat Masuk Utama')
        && str_contains($request['text'], $item->letter->subject));
});

it('does not call the bot for users without a chat id', function () {
    Http::preventStrayRequests();
    Http::fake();

    config(['services.telegram.bot_token' => 'test-token']);

    $user = telegramChannelUser();
    $item = telegramChannelItem();

    $user->notifyNow(new InboxItemNotification($item));

    Http::assertNothingSent();
});

it('throws when telegram rejects the message so the queue can retry', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.telegram.org/bot*/sendMessage' => Http::response(['ok' => false, 'description' => 'Bad Request'], 400),
    ]);

    config(['services.telegram.bot_token' => 'test-token']);

    $user = telegramChannelUser('556677889');
    $item = telegramChannelItem();

    expect(fn () => $user->notifyNow(new InboxItemNotification($item)))
        ->toThrow(RequestException::class);
});

it('skips the telegram channel when the feature is disabled on the server', function () {
    Http::preventStrayRequests();
    Http::fake();

    config([
        'services.telegram.bot_token' => 'test-token',
        'company.features' => [],
    ]);

    $user = telegramChannelUser('123123123');
    $item = telegramChannelItem();

    $user->notifyNow(new InboxItemNotification($item));

    Http::assertNothingSent();
});
