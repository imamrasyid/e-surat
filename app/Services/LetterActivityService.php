<?php

namespace App\Services;

use App\Models\LetterActivity;
use Illuminate\Support\Facades\Auth;

class LetterActivityService
{
    /**
     * Log perubahan data surat.
     * 
     * @param int $letterId
     * @param string $action
     * @param string $description
     * @param array|null $changes
     * @return \App\Models\LetterActivity
     */
    public static function log(int $letterId, string $action, string $description, ?array $changes = null): LetterActivity
    {
        return LetterActivity::create([
            'letter_id'   => $letterId,
            'user_id'     => Auth::id(),
            'action'      => $action,
            'description' => $description,
            'changes'     => $changes,
        ]);
    }

    /**
     * Log perubahan field pada surat (otomatis diff old vs new).
     * 
     * @param int $letterId
     * @param string $action
     * @param string $description
     * @param array $oldData
     * @param array $newData
     * @return \App\Models\LetterActivity|null
     */
    public static function logDiff(int $letterId, string $action, string $description, array $oldData, array $newData): ?LetterActivity
    {
        $changes = [];

        foreach ($newData as $key => $newValue) {
            $oldValue = $oldData[$key] ?? null;
            if ($oldValue != $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        if (empty($changes)) {
            return null;
        }

        return self::log($letterId, $action, $description, $changes);
    }
}