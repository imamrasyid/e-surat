<?php

namespace App\Console\Commands;

use App\Services\TelegramBotClient;
use Illuminate\Console\Command;
use Throwable;

class SetTelegramWebhookCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'telegram:webhook:set';

    /**
     * @var string
     */
    protected $description = 'Daftarkan URL webhook bot Telegram beserta secret token';

    public function handle(TelegramBotClient $telegram): int
    {
        $secret = (string) config('services.telegram.webhook_secret');

        if (blank(config('services.telegram.bot_token')) || $secret === '') {
            $this->error('TELEGRAM_BOT_TOKEN dan TELEGRAM_WEBHOOK_SECRET harus diisi terlebih dahulu.');

            return self::FAILURE;
        }

        $url = route('telegram.webhook');

        try {
            $telegram->setWebhook($url, $secret);
        } catch (Throwable $exception) {
            $this->error('Gagal mendaftarkan webhook: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Webhook Telegram terdaftar: {$url}");

        return self::SUCCESS;
    }
}
