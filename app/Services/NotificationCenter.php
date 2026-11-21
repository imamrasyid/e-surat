<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Satu sumber bentuk data notification center untuk props Inertia dan halaman
 * riwayat. Semua query dibatasi ke notifiable milik user yang sedang login.
 */
class NotificationCenter
{
    /**
     * Ringkasan bell: jumlah unread dan sejumlah item terbaru.
     *
     * @return array{unread_count: int, latest: array<int, array<string, mixed>>}
     */
    public function summary(User $user, int $limit = 10): array
    {
        return [
            'unread_count' => $user->unreadNotifications()->count(),
            'latest' => $user->notifications()
                ->whereNull('read_at')
                ->latest()
                ->limit($limit)
                ->get()
                ->map(fn (DatabaseNotification $notification): array => $this->toItem($notification))
                ->all(),
        ];
    }

    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function history(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->notifications()
            ->latest()
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (DatabaseNotification $notification): array => $this->toItem($notification));
    }

    public function findOwned(User $user, string $id): DatabaseNotification
    {
        /** @var DatabaseNotification $notification */
        $notification = $user->notifications()->whereKey($id)->firstOrFail();

        return $notification;
    }

    /**
     * @return array{enabled: bool, public_key: string|null}
     */
    public function webPushProps(): array
    {
        $eligible = app(CompanyFeature::class)->webPushEligible();

        return [
            'enabled' => $eligible,
            'public_key' => $eligible ? (string) config('webpush.vapid.public_key') : null,
        ];
    }

    private function itemType(mixed $value): string
    {
        // Baris lama menyimpan 'letter' sebelum primary/undangan dipisah.
        if ($value === 'letter') {
            return 'primary';
        }

        return in_array($value, ['primary', 'invitation', 'disposition', 'memo'], true) ? $value : 'memo';
    }

    /**
     * @return array<string, mixed>
     */
    private function toItem(DatabaseNotification $notification): array
    {
        $data = $notification->data;

        return [
            'id' => $notification->id,
            'title' => is_string($data['title'] ?? null) ? $data['title'] : '',
            'body' => is_string($data['body'] ?? null) ? $data['body'] : '',
            'url' => is_string($data['url'] ?? null) ? $data['url'] : '',
            'item_type' => $this->itemType($data['item_type'] ?? null),
            'security_classification' => in_array($data['security_classification'] ?? null, ['SR', 'R', 'T', 'B'], true)
                ? $data['security_classification']
                : 'B',
            'received_via' => in_array($data['received_via'] ?? null, ['email', 'physical', 'internal', 'other'], true)
                ? $data['received_via']
                : 'other',
            'instruction' => is_string($data['instruction'] ?? null) ? $data['instruction'] : null,
            'followup' => is_string($data['followup'] ?? null) ? $data['followup'] : null,
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String() ?? '',
        ];
    }
}
