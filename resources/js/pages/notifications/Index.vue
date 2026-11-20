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
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, NotificationListItem as NotificationItem, PaginatedData } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Trash2 } from '@lucide/vue';

defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    notifications: PaginatedData<NotificationItem>;
}>();

function open(notification: NotificationItem): void {
    router.patch(
        route('notifications.read', notification.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => router.visit(notification.url),
        },
    );
}

function remove(id: string): void {
    router.delete(route('notifications.destroy', id), { preserveScroll: true });
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
                <Heading class="mb-0!" title="Pemberitahuan" description="Riwayat seluruh pemberitahuan inbox Anda" />

                <AlertDialog>
                    <AlertDialogTrigger as-child>
                        <Button variant="outline" size="sm" :disabled="notifications.total === 0">
                            <Trash2 />
                            Hapus semua
                        </Button>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>Hapus semua pemberitahuan?</AlertDialogTitle>
                            <AlertDialogDescription>
                                Seluruh riwayat pemberitahuan Anda, baik yang sudah dibaca maupun belum, akan dihapus permanen.
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>Batal</AlertDialogCancel>
                            <AlertDialogAction class="bg-destructive hover:bg-destructive/90 text-white" @click="destroyAll">
                                Hapus semua
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </div>

            <div class="rounded-lg border">
                <NotificationListItem
                    v-for="notification in notifications.data"
                    :key="notification.id"
                    :notification="notification"
                    @open="open"
                    @delete="remove"
                />
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
    </AppLayout>
</template>
