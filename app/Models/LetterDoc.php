<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $agenda_id
 * @property int $letter_id
 * @property string $name
 * @property string|null $path
 * @property string|null $url
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read string $download_url
 * @property-read string $preview_url
 * @property-read Letter|null $letter
 */
#[Fillable([
    'agenda_id',
    'letter_id',
    'name',
    'path',
    'is_active',
])]
#[Hidden(['path', 'url'])]
#[Appends(['download_url', 'preview_url'])]
class LetterDoc extends Model
{
    use SoftDeletes;

    /**
     * Endpoint unduhan yang memeriksa akses ke surat; URL publik tidak pernah
     * disimpan sehingga lampiran tetap privat di object storage.
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('letter.docs.download', $this->id);
    }

    /**
     * Endpoint pratinjau inline dengan otorisasi yang sama seperti unduhan;
     * browser merender PDF dari route ini, bukan dari path storage.
     */
    public function getPreviewUrlAttribute(): string
    {
        return route('letter.docs.preview', $this->id);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Letter, $this>
     */
    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class);
    }
}
