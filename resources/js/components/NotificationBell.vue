<script setup lang="ts">
import NotificationListItem from '@/components/NotificationListItem.vue';
import { Button } from '@/components/ui/button';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { useWebPushNotifications } from '@/composables/useWebPushNotifications';
import type { AppPageProps, NotificationListItem as NotificationItem } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Bell } from '@lucide/vue';
import { computed } from 'vue';

const page = usePage<AppPageProps>();
const center = computed(() => page.props.notificationCenter);

const badge = computed(() => (center.value.unread_count > 99 ? '99+' : String(center.value.unread_count)));
const ariaLabel = computed(() => (center.value.unread_count > 0 ? `Pemberitahuan, ${center.value.unread_count} belum dibaca` : 'Pemberitahuan'));

const { supported, permission, enabled, subscribed, processing, error, enable, disable } = useWebPushNotifications();

function open(notification: NotificationItem): void {
    router.patch(route('notifications.read', notification.id));
}

function readAll(): void {
    router.patch(route('notifications.readAll'), {}, { preserveScroll: true });
}
</script>

<template>
    <Sheet>
        <SheetTrigger as-child>
            <Button variant="ghost" size="icon" class="relative" :aria-label="ariaLabel">
                <Bell />
                <span
                    v-if="center.unread_count > 0"
                    class="bg-destructive absolute -top-0.5 -right-0.5 flex h-4 min-w-4 items-center justify-center rounded-full px-1 text-[10px] font-semibold text-white"
                >
                    {{ badge }}
                </span>
            </Button>
        </SheetTrigger>

        <SheetContent side="right" class="flex w-full flex-col gap-0 p-0 sm:max-w-md">
            <SheetHeader class="px-4 py-4">
                <SheetTitle>Pemberitahuan Terbaru</SheetTitle>
                <SheetDescription>{{ center.unread_count }} pemberitahuan belum dibaca</SheetDescription>
            </SheetHeader>

            <ScrollArea class="flex-1 px-2">
                <NotificationListItem :notifications="center.latest" :compact="true" @open="open" />
                <p v-if="center.latest.length === 0" class="text-muted-foreground px-4 py-8 text-center text-sm">Belum ada pemberitahuan.</p>
            </ScrollArea>

            <div class="space-y-3 border-t px-4 py-4">
                <div v-if="enabled" class="space-y-2">
                    <p class="text-sm font-medium">Notifikasi Browser</p>
                    <p v-if="!supported" class="text-muted-foreground text-xs">Browser ini tidak mendukung notifikasi push.</p>
                    <template v-else>
                        <Button size="sm" :disabled="processing" @click="subscribed ? disable() : enable()">
                            {{ subscribed ? 'Matikan notifikasi browser' : 'Aktifkan notifikasi browser' }}
                        </Button>
                        <p v-if="permission === 'denied'" class="text-muted-foreground text-xs">
                            Izin notifikasi diblokir. Aktifkan izin situs melalui pengaturan browser.
                        </p>
                        <p v-if="error" class="text-destructive text-xs">{{ error }}</p>
                    </template>
                </div>

                <Button variant="secondary" size="sm" class="w-full" :disabled="center.unread_count === 0" @click="readAll">
                    Tandai semua telah dibaca
                </Button>
                <Button as-child variant="outline" size="sm" class="w-full">
                    <Link :href="route('notifications.index')">Lihat semua pemberitahuan</Link>
                </Button>
            </div>
        </SheetContent>
    </Sheet>
</template>
