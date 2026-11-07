<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Telegram\CreateTelegramLinkTokenAction;
use App\Actions\Telegram\RenderTelegramQrAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Services\CompanyFeature;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request, CompanyFeature $features): Response
    {
        $user = $request->user();

        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'telegramConnected' => filled($user->telegram_chat_id),
            'telegramEnabled' => $features->enabled('telegram'),
        ]);
    }

    /**
     * Buat deep link pairing bot Telegram untuk user yang sedang login,
     * lengkap dengan QR SVG untuk dialog di frontend.
     */
    public function linkTelegram(
        Request $request,
        CreateTelegramLinkTokenAction $tokens,
        RenderTelegramQrAction $qrCode,
        CompanyFeature $features
    ): JsonResponse {
        // Route sudah memakai middleware `feature:telegram`; pemeriksaan ini
        // menjaga enforcement tetap ada bila route dipanggil lewat jalur lain.
        abort_unless($features->enabled('telegram'), 404);

        $botUsername = $this->telegramBotUsername();

        if ($botUsername === '' || blank(config('services.telegram.bot_token'))) {
            return response()->json([
                'errors' => [
                    'telegram' => 'Telegram belum dikonfigurasi. Hubungi administrator untuk mengisi TELEGRAM_BOT_TOKEN dan TELEGRAM_BOT_USERNAME.',
                ],
            ], 422);
        }

        $url = "https://t.me/{$botUsername}?start=".$tokens->handle($request->user());

        return response()->json([
            'url' => $url,
            'svg' => $qrCode->handle($url),
        ]);
    }

    /**
     * Status tautan Telegram user, dipakai dialog untuk menunggu konfirmasi /start.
     */
    public function telegramStatus(Request $request): JsonResponse
    {
        return response()->json([
            'connected' => filled($request->user()->telegram_chat_id),
        ]);
    }

    /**
     * Username bot tanpa prefiks "@" karena URL t.me tidak menerima karakter itu.
     */
    private function telegramBotUsername(): string
    {
        return ltrim(trim((string) config('services.telegram.bot_username')), '@');
    }

    /**
     * Lepaskan tautan Telegram user yang sedang login.
     */
    public function unlinkTelegram(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->telegram_chat_id = null;
        $user->save();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Tautan Telegram berhasil diputuskan.',
        ]);

        return to_route('profile.edit');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Informasi Pengguna berhasil diperbarui.')]);

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
