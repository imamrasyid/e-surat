<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Satu-satunya pembungkus HTTP Telegram Bot API di aplikasi ini.
 *
 * Token bot dibaca dari config (bukan env) supaya aman saat config di-cache.
 */
class TelegramBotClient
{
    /**
     * Kirim pesan teks ke satu chat.
     */
    public function sendMessage(string $chatId, string $text): void
    {
        $this->post('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'disable_web_page_preview' => true,
        ]);
    }

    /**
     * Daftarkan URL webhook beserta secret token validasinya.
     */
    public function setWebhook(string $url, string $secretToken): void
    {
        $this->post('setWebhook', [
            'url' => $url,
            'secret_token' => $secretToken,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function post(string $method, array $payload): Response
    {
        return $this->client()
            ->post($method, $payload)
            ->throw();
    }

    private function client(): PendingRequest
    {
        $token = (string) config('services.telegram.bot_token');

        if ($token === '') {
            throw new RuntimeException('TELEGRAM_BOT_TOKEN belum dikonfigurasi.');
        }

        return Http::baseUrl("https://api.telegram.org/bot{$token}")
            ->asJson()
            ->connectTimeout(3)
            ->timeout(10);
    }
}
