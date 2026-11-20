import type { AppPageProps } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, shallowRef } from 'vue';

// VAPID public key dikirim sebagai base64url; Push API meminta bytes.
function decodeApplicationServerKey(key: string): Uint8Array<ArrayBuffer> {
    const padding = '='.repeat((4 - (key.length % 4)) % 4);
    const base64 = (key + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw = window.atob(base64);
    const bytes = new Uint8Array(new ArrayBuffer(raw.length));

    for (let index = 0; index < raw.length; index += 1) {
        bytes[index] = raw.charCodeAt(index);
    }

    return bytes;
}

export function useWebPushNotifications() {
    const page = usePage<AppPageProps>();

    const supported = typeof window !== 'undefined' && 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;

    const permission = shallowRef<NotificationPermission>(supported ? Notification.permission : 'denied');
    const subscribed = shallowRef(false);
    const processing = shallowRef(false);
    const error = shallowRef<string | null>(null);

    const enabled = computed(() => page.props.notificationCenter.web_push.enabled);
    const publicKey = computed(() => page.props.notificationCenter.web_push.public_key);

    async function currentSubscription(): Promise<PushSubscription | null> {
        const registration = await navigator.serviceWorker.getRegistration('/sw.js');

        return registration ? registration.pushManager.getSubscription() : null;
    }

    async function refresh(): Promise<void> {
        if (!supported) {
            return;
        }

        try {
            subscribed.value = (await currentSubscription()) !== null;
        } catch {
            subscribed.value = false;
        }
    }

    async function enable(): Promise<void> {
        if (!supported) {
            error.value = 'Browser ini tidak mendukung notifikasi push.';
            return;
        }

        if (!enabled.value || !publicKey.value) {
            error.value = 'Notifikasi browser tidak tersedia pada server ini.';
            return;
        }

        processing.value = true;
        error.value = null;

        try {
            const granted = await Notification.requestPermission();
            permission.value = granted;

            if (granted !== 'granted') {
                error.value = 'Izin notifikasi ditolak. Aktifkan izin situs melalui pengaturan browser.';
                return;
            }

            const registration = await navigator.serviceWorker.register('/sw.js');
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: decodeApplicationServerKey(publicKey.value),
            });
            const json = subscription.toJSON();

            await (() => {
                const { promise, resolve, reject } = Promise.withResolvers<void>();

                router.post(
                    route('notifications.push-subscription.store'),
                    {
                        endpoint: subscription.endpoint,
                        keys: {
                            p256dh: json.keys?.p256dh ?? '',
                            auth: json.keys?.auth ?? '',
                        },
                        content_encoding: 'aes128gcm',
                    },
                    {
                        preserveScroll: true,
                        onSuccess: () => resolve(),
                        onError: () => reject(new Error('Server menolak subscription browser.')),
                    },
                );

                return promise;
            })();

            subscribed.value = true;
        } catch (cause) {
            error.value = cause instanceof Error ? cause.message : 'Gagal mengaktifkan notifikasi browser.';
        } finally {
            processing.value = false;
        }
    }

    async function disable(): Promise<void> {
        processing.value = true;
        error.value = null;

        try {
            const subscription = await currentSubscription();

            if (subscription) {
                await (() => {
                    const { promise, resolve, reject } = Promise.withResolvers<void>();

                    router.delete(route('notifications.push-subscription.destroy'), {
                        data: { endpoint: subscription.endpoint },
                        preserveScroll: true,
                        onSuccess: () => resolve(),
                        onError: () => reject(new Error('Server gagal menghapus subscription browser.')),
                    });

                    return promise;
                })();

                await subscription.unsubscribe();
            }

            subscribed.value = false;
        } catch (cause) {
            error.value = cause instanceof Error ? cause.message : 'Gagal mematikan notifikasi browser.';
        } finally {
            processing.value = false;
        }
    }

    onMounted(refresh);

    return { supported, permission, enabled, subscribed, processing, error, enable, disable };
}
