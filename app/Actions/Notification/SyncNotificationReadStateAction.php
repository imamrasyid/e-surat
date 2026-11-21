<?php

namespace App\Actions\Notification;

use App\Actions\Disposition\MarkDispositionAsReadAction;
use App\Actions\Letter\MarkRecipientAsReadAction;
use App\Actions\Memo\MarkMemoAsReadAction;
use App\Models\Disposition;
use App\Models\LetterRecipient;
use App\Models\Memo;
use App\Models\User;
use App\Services\NotificationCenter;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Route as RouteFacade;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SyncNotificationReadStateAction
{
    public function __construct(
        private readonly NotificationCenter $center,
        private readonly MarkRecipientAsReadAction $markRecipient,
        private readonly MarkDispositionAsReadAction $markDisposition,
        private readonly MarkMemoAsReadAction $markMemo,
    ) {}

    /**
     * Tandai notifikasi dan item tujuannya sudah dibaca, lalu kembalikan URL detail.
     */
    public function open(User $user, string $notificationId): string
    {
        $notification = $this->center->findOwned($user, $notificationId);

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        $this->markItemAsRead($user, $notification);

        $url = $notification->data['url'] ?? null;

        return is_string($url) && $url !== '' ? $url : route('notifications.index');
    }

    /**
     * Tandai semua notifikasi unread dan item terkait sebagai dibaca.
     */
    public function markAll(User $user): void
    {
        $notifications = $user->unreadNotifications()->get();

        foreach ($notifications as $notification) {
            $this->markItemAsRead($user, $notification);
        }

        $user->unreadNotifications()->update(['read_at' => now()]);
    }

    /**
     * Tandai notifikasi user yang menunjuk ke item yang baru saja dibaca.
     */
    public function markForItem(User $user, string $itemType, int $itemId, string $detailUrl): void
    {
        $path = $this->urlPath($detailUrl);

        $user->unreadNotifications()
            ->get()
            ->filter(function (DatabaseNotification $notification) use ($itemType, $itemId, $path): bool {
                $data = $notification->data;
                $storedPath = is_string($data['url'] ?? null) ? $this->urlPath($data['url']) : null;

                if ($path !== null && $storedPath === $path) {
                    return true;
                }

                return ($data['item_id'] ?? null) === $itemId
                    && in_array($data['item_type'] ?? null, $this->itemTypes($itemType), true);
            })
            ->each(fn (DatabaseNotification $notification) => $notification->markAsRead());
    }

    private function markItemAsRead(User $user, DatabaseNotification $notification): void
    {
        $positionId = (int) $user->currentPosition()->value('positions.id');

        if ($positionId === 0) {
            return;
        }

        $item = $this->resolveItem($notification);

        if ($item === null || $item->to_position_id !== $positionId || $item->read_at !== null) {
            return;
        }

        match (true) {
            $item instanceof LetterRecipient => $this->markRecipient->handle($item),
            $item instanceof Disposition => $this->markDisposition->handle($item),
            $item instanceof Memo => $this->markMemo->handle($item),
        };
    }

    private function resolveItem(DatabaseNotification $notification): LetterRecipient|Disposition|Memo|null
    {
        $data = $notification->data;
        $itemId = $data['item_id'] ?? null;

        if (is_int($itemId)) {
            return match ($data['item_type'] ?? null) {
                'primary', 'invitation' => LetterRecipient::find($itemId),
                'disposition' => Disposition::find($itemId),
                'memo' => Memo::find($itemId),
                default => null,
            };
        }

        $url = $data['url'] ?? null;

        return is_string($url) ? $this->resolveLegacyItem($url) : null;
    }

    private function resolveLegacyItem(string $url): LetterRecipient|Disposition|Memo|null
    {
        try {
            $route = RouteFacade::getRoutes()->match(Request::create($url));
        } catch (NotFoundHttpException) {
            return null;
        }

        $recipient = $route->parameter('recipient');

        if (! is_numeric($recipient)) {
            return null;
        }

        $id = (int) $recipient;

        if ($id === 0) {
            return null;
        }

        return match ($route->getName()) {
            'inbox.primary.show', 'inbox.undangan.show' => LetterRecipient::query()->whereKey($id)->first(),
            'dispo.show' => Disposition::query()->whereKey($id)->first(),
            'memo.show' => Memo::query()->whereKey($id)->first(),
            default => null,
        };
    }

    private function urlPath(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);

        return is_string($path) ? $path : null;
    }

    /**
     * @return array<int, string>
     */
    private function itemTypes(string $itemType): array
    {
        return match ($itemType) {
            'primary', 'invitation' => ['primary', 'invitation', 'letter'],
            default => [$itemType],
        };
    }
}
