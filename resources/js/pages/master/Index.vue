<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Item, ItemContent, ItemDescription, ItemMedia, ItemTitle } from '@/components/ui/item';
import { usePermissions } from '@/composables/usePermissions';
import masterNavItems from '@/data/masterDataMenu';
import AppLayout from '@/layouts/AppLayout.vue';
import MasterLayout from '@/layouts/master/Layout.vue';
import { type BreadcrumbItem, type NavItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { computed, type ComputedRef } from 'vue';

defineProps<{
    breadcrumbItems: BreadcrumbItem[];
}>();

const { can } = usePermissions();

function hasPermission(permission?: string): boolean {
    if (!permission) return false;
    if (permission === '*') return true;
    return can(permission);
}

const menuItems: ComputedRef<NavItem[]> = computed(() => masterNavItems.filter((item) => hasPermission(item.permission)));
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Master Data" />

        <MasterLayout>
            <HeadingSmall title="Referensi Aplikasi" description="Silahkan pilih menu yang tersedia" />
            <div class="-mt-4 grid gap-4 sm:grid-cols-2">
                <Link v-for="item in menuItems" :key="item.href" :href="route(item.href)">
                    <Item variant="outline" class="hover:border-primary hover:text-primary rounded-lg">
                        <ItemMedia variant="icon">
                            <component :is="item.icon" />
                        </ItemMedia>
                        <ItemContent>
                            <ItemTitle>{{ item.title }}</ItemTitle>
                            <ItemDescription> Kelola data referensi {{ item.title.toLowerCase() }} </ItemDescription>
                        </ItemContent>
                    </Item>
                </Link>
            </div>
        </MasterLayout>
    </AppLayout>
</template>
