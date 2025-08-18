<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'is_active',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
        ];
    }

    public function positions()
    {
        return $this->morphToMany(Position::class, 'model', 'model_has_position')
                    ->withPivot(['start_date', 'end_date', 'is_active'])
                    ->withTimestamps();
    }

    // Ambil jabatan aktif saat ini
    public function currentPosition()
    {
        return $this->positions()->wherePivot('is_active', true);
    }

    // Hak khusus disposisi aktif
    public function specialDispositions()
    {
        return $this->morphToMany(Position::class, 'model', 'model_has_special_dispositions', 'model_id', 'to_position_id')
                    ->withPivot(['is_active', 'granted_at', 'revoked_at'])
                    ->wherePivot('is_active', true)
                    ->withTimestamps();
    }

    // Semua hak disposisi (termasuk nonaktif)
    public function allSpecialDispositions()
    {
        return $this->morphToMany(Position::class, 'model', 'model_has_special_dispositions', 'model_id', 'to_position_id')
                    ->withPivot(['is_active', 'granted_at', 'revoked_at'])
                    ->withTimestamps();
    }

    // Update hak khusus (tanpa menghapus row lama, hanya aktif/nonaktif)
    public function updateSpecialDispositions(array $positionIds)
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
            if (!in_array($posId, $positionIds)) {
                $record->update([
                    'is_active' => false,
                    'revoked_at' => now(),
                ]);
            }
        }
    }
}
