<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $username
 * @property string $email
 * @property bool $is_active
 * @property string|null $telegram_chat_id
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'username', 'email', 'is_active', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token', 'telegram_chat_id'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasPushSubscriptions, HasRoles, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Id akun administrator default hasil `DatabaseSeeder`. Akun ini tidak boleh
     * diedit lewat master data, jadi jalankan seeder sebelum membuat user lain.
     */
    public const SUPER_ADMIN_ID = 1;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return MorphToMany<Position, $this>
     */
    public function positions(): MorphToMany
    {
        return $this->morphToMany(Position::class, 'model', 'model_has_position')
            ->withPivot(['start_date', 'end_date', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Ambil jabatan aktif saat ini
     *
     * @return MorphToMany<Position, $this>
     */
    public function currentPosition(): MorphToMany
    {
        return $this->positions()->wherePivot('is_active', true);
    }

    /**
     * Chat ID Telegram tujuan notifikasi; null bila akun belum ditautkan.
     */
    public function routeNotificationForTelegram(): ?string
    {
        return filled($this->telegram_chat_id) ? (string) $this->telegram_chat_id : null;
    }

    /**
     * Hak khusus disposisi aktif
     *
     * @return MorphToMany<Position, $this>
     */
    public function specialDispositions(): MorphToMany
    {
        return $this->morphToMany(Position::class, 'model', 'model_has_special_dispositions', 'model_id', 'to_position_id')
            ->withPivot(['is_active', 'granted_at', 'revoked_at'])
            ->wherePivot('is_active', true)
            ->withTimestamps();
    }

    /**
     * Semua hak disposisi (termasuk nonaktif)
     *
     * @return MorphToMany<Position, $this>
     */
    public function allSpecialDispositions(): MorphToMany
    {
        return $this->morphToMany(Position::class, 'model', 'model_has_special_dispositions', 'model_id', 'to_position_id')
            ->withPivot(['is_active', 'granted_at', 'revoked_at'])
            ->withTimestamps();
    }

    /**
     * Update hak khusus (tanpa menghapus row lama, hanya aktif/nonaktif)
     *
     * @param  array<int, int|string>  $positionIds
     */
    public function updateSpecialDispositions(array $positionIds): void
    {
        $existing = ModelHasSpecialDisposition::where('model_type', self::class)
            ->where('model_id', $this->id)
            ->get()
            ->keyBy('to_position_id');

        // 1. Aktifkan / insert yang dipilih
        foreach ($positionIds as $posId) {
            if (isset($existing[$posId])) {
                // Sudah ada → aktifkan kembali jika perlu
                $existing[$posId]->update([
                    'is_active' => true,
                    'revoked_at' => null,
                    'updated_at' => now(),
                ]);
            } else {
                // Belum ada → insert baru
                ModelHasSpecialDisposition::create([
                    'model_type' => self::class,
                    'model_id' => $this->id,
                    'to_position_id' => $posId,
                    'is_active' => true,
                    'granted_at' => now(),
                ]);
            }
        }

        // 2. Nonaktifkan yang tidak dipilih
        foreach ($existing as $posId => $record) {
            if (! in_array($posId, $positionIds)) {
                $record->update([
                    'is_active' => false,
                    'revoked_at' => now(),
                ]);
            }
        }
    }
}
