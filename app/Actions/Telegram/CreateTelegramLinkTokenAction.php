<?php

namespace App\Actions\Telegram;

use App\Models\TelegramLinkToken;
use App\Models\User;
use Illuminate\Support\Str;

class CreateTelegramLinkTokenAction
{
    /**
     * Buat token pairing Telegram sekali pakai untuk user.
     *
     * Token mentah hanya dikembalikan sekali ke pemanggil; yang disimpan
     * hanyalah hash SHA-256 sehingga bocornya database tidak membocorkan token.
     */
    public function handle(User $user): string
    {
        TelegramLinkToken::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->delete();

        $rawToken = Str::random(64);

        TelegramLinkToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $rawToken),
            'expires_at' => now()->addMinutes(10),
        ]);

        return $rawToken;
    }
}
