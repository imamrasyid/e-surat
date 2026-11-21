// Service worker Web Push: tanpa build step dan tanpa dependency.
// Payload dikirim oleh App\Notifications\InboxItemNotification::toWebPush().

self.addEventListener('push', (event) => {
    let payload;

    try {
        payload = event.data ? event.data.json() : null;
    } catch {
        return;
    }

    if (!payload || typeof payload.title !== 'string') {
        return;
    }

    event.waitUntil(
        self.registration.showNotification(payload.title, {
            body: typeof payload.body === 'string' ? payload.body : '',
            data: payload.data && typeof payload.data === 'object' ? payload.data : {},
        }),
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const data = event.notification.data;
    const url = data && typeof data.url === 'string' ? data.url : null;

    if (!url) {
        return;
    }

    event.waitUntil(
        (async () => {
            let target;

            try {
                target = new URL(url, self.location.origin);
            } catch {
                return;
            }

            const clients = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });

            for (const client of clients) {
                if (client.url === target.href) {
                    await client.focus();
                    return;
                }
            }

            await self.clients.openWindow(target.href);
        })(),
    );
});
