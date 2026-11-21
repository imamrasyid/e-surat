<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import NotificationListItem from '@/components/NotificationListItem.vue';
import SmartPagination from '@/components/SmartPagination.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import type { AppPageProps, BreadcrumbItem, NotificationListItem as NotificationItem, PaginatedData } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { EllipsisVertical } from '@lucide/vue';
import { computed, shallowRef } from 'vue';

const page = usePage<AppPageProps>();
const unreadCount = computed(() => page.props.notificationCenter.unread_count);
const deleteAllOpen = shallowRef(false);

defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    notifications: PaginatedData<NotificationItem>;
}>();

function open(notification: NotificationItem): void {
    router.patch(route('notifications.read', notification.id));
}

function readAll(): void {
    router.patch(route('notifications.readAll'), {}, { preserveScroll: true });
}

function destroyAll(): void {
    router.delete(route('notifications.destroyAll'), { preserveScroll: true });
}
</script>

<template>
    <Head title="Pemberitahuan" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6">
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <Heading class="mb-0!" title="Pemberitahuan" description="Riwayat seluruh pemberitahuan" />

                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="outline" size="icon-sm"><EllipsisVertical /></Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" side="bottom">
                        <DropdownMenuLabel>Pengaturan</DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem :disabled="unreadCount === 0" @select="readAll">Baca Semua</DropdownMenuItem>
                        <DropdownMenuItem variant="destructive" :disabled="notifications.total === 0" @select="deleteAllOpen = true">
                            Hapus Semua
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>

            <div class="overflow-hidden">
                <NotificationListItem :notifications="notifications.data" @open="open" />
                <p v-if="notifications.data.length === 0" class="text-muted-foreground px-4 py-10 text-center text-sm">Belum ada pemberitahuan.</p>
            </div>

            <div v-if="notifications.total > 0" class="mt-6 flex justify-end">
                <SmartPagination
                    :links="notifications.links"
                    :total="notifications.total"
                    :items-per-page="notifications.per_page"
                    class="justify-end"
                />
            </div>
        </div>

        <AlertDialog v-model:open="deleteAllOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Hapus semua pemberitahuan?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Seluruh riwayat pemberitahuan, baik yang sudah dibaca maupun belum, akan dihapus permanen.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Batal</AlertDialogCancel>
                    <AlertDialogAction class="bg-destructive hover:bg-destructive/90 text-white" @click="destroyAll"> Hapus semua </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
