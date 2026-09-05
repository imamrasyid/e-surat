<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuBadge,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { usePermissions } from '@/composables/usePermissions';
import { inboxItems, platformItems } from '@/data/mainNavMenu';
import type { NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, type ComputedRef } from 'vue';

const { props } = usePage();
const { can } = usePermissions();

function hasPermission(permission?: string): boolean {
    if (!permission) return false;
    if (permission === '*') return true;
    return can(permission);
}

function filterMenu(menu: NavItem[]): NavItem[] {
    return menu
        .filter((item) => hasPermission(item.permission))
        .map((item) => {
            const newItem = { ...item };
            return newItem;
        });
}

const filteredPlatformItems: ComputedRef<NavItem[]> = computed(() => filterMenu(platformItems));
const filteredInboxItems: ComputedRef<NavItem[]> = computed(() => filterMenu(inboxItems));

const visibleItemsInbox = computed(() => inboxItems.filter((item) => hasPermission(item.permission)));
const visibleItemsPlatform = computed(() => platformItems.filter((item) => hasPermission(item.permission)));

const notifMap: Record<number, keyof typeof props.inbox> = {
    1: 'primary',
    2: 'undangan',
    8: 'disposisi',
    9: 'memo',
};

function getNotifCount(notif?: number): number {
    if (!notif) return 0;
    const key = notifMap[notif];
    return key ? props.inbox[key] : 0;
}
</script>

<template>
    <SidebarGroup v-if="visibleItemsPlatform.length > 0" class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarGroupContent>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in filteredPlatformItems" :key="item.title">
                    <SidebarMenuButton as-child :is-active="item.current ? route().current(item.current) : false" :tooltip="item.title">
                        <Link :href="route(item.href)">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
    <SidebarGroup v-if="visibleItemsInbox.length > 0" class="px-2 py-0">
        <SidebarGroupLabel>Surat Masuk</SidebarGroupLabel>
        <SidebarGroupContent>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in filteredInboxItems" :key="item.title">
                    <SidebarMenuButton as-child :is-active="item.current ? route().current(item.current) : false" :tooltip="item.title">
                        <Link :href="route(item.href)">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                    <SidebarMenuBadge v-if="getNotifCount(item.notif) > 0">
                        {{ getNotifCount(item.notif) }}
                    </SidebarMenuBadge>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
</template>
