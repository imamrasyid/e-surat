<?php

namespace App\Http\Controllers;

use App\Models\TelegramLinkToken;
use App\Models\User;
use App\Services\TelegramBotClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TelegramWebhookController extends Controller
{
    /**
     * Tangani update dari Telegram Bot API, khususnya command `/start <token>`.
     */
    public function __invoke(Request $request, TelegramBotClient $telegram): JsonResponse
    {
        $secret = (string) config('services.telegram.webhook_secret');
        $provided = (string) $request->header('X-Telegram-Bot-Api-Secret-Token', '');

        if ($secret === '' || ! hash_equals($secret, $provided)) {
            abort(403);
        }

        $chatId = data_get($request->all(), 'message.chat.id');
        $text = (string) data_get($request->all(), 'message.text', '');

        // Update selain `/start <token>` diabaikan tanpa perubahan data.
        if (! is_scalar($chatId) || ! preg_match('#^/start(?:@\w+)?\s+(\S+)\s*$#u', trim($text), $matches)) {
            return response()->json(['ok' => true]);
        }

        $chatId = (string) $chatId;

        $status = DB::transaction(fn (): string => $this->link($chatId, $matches[1]));

        // Konfirmasi dikirim setelah commit; kegagalan kirim tidak boleh membuat
        // Telegram mengulang webhook tanpa batas.
        try {
            $telegram->sendMessage($chatId, $this->message($status));
        } catch (Throwable $exception) {
            Log::warning('Gagal mengirim konfirmasi pairing Telegram.', [
                'chat_id' => $chatId,
                'exception' => $exception->getMessage(),
            ]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Ikat chat ID ke user pemilik token, lalu tandai token terpakai.
     *
     * @return string `linked`, `taken`, atau `invalid`
     */
    private function link(string $chatId, string $rawToken): string
    {
        $token = TelegramLinkToken::query()
            ->where('token_hash', hash('sha256', $rawToken))
            ->lockForUpdate()
            ->first();

        if ($token === null || ! $token->isUsable()) {
            return 'invalid';
        }

        $ownerId = User::query()->where('telegram_chat_id', $chatId)->value('id');

        if ($ownerId !== null && $ownerId !== $token->user_id) {
            return 'taken';
        }

        $user = $token->user;

        if ($user === null) {
            return 'invalid';
        }

        $user->telegram_chat_id = $chatId;
        $user->save();

        $token->used_at = now();
        $token->save();

        return 'linked';
    }

    private function message(string $status): string
    {
        return match ($status) {
            'linked' => 'Akun Telegram Anda berhasil terhubung ke e-Surat. Notifikasi surat masuk, disposisi, dan nota dinas akan dikirim ke chat ini.',
            'taken' => 'Chat Telegram ini sudah terhubung dengan akun e-Surat lain. Putuskan tautan pada akun tersebut terlebih dahulu.',
            default => 'Tautan Telegram tidak valid, sudah dipakai, atau kedaluwarsa. Silakan buat tautan baru dari halaman Profil.',
        };
    }
}
