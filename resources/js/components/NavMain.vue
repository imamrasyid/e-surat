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
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const { props } = usePage();
const { can } = usePermissions();

const notifMap: Record<number, keyof typeof props.inbox> = {
    1: 'primary',
    2: 'undangan',
    8: 'disposisi',
    9: 'memo',
};

function hasPermission(permission?: string): boolean {
    if (!permission) return false;
    if (permission === '*') return true;
    return can(permission);
}

function getNotifCount(notif?: number): number {
    if (!notif) return 0;
    const key = notifMap[notif];
    return key ? props.inbox[key] : 0;
}

const visibleItemsInbox = computed(() => inboxItems.filter((item) => hasPermission(item.permission)));
const visibleItemsPlatform = computed(() => platformItems.filter((item) => hasPermission(item.permission)));
</script>

<template>
    <SidebarGroup v-if="visibleItemsPlatform.length > 0" class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarGroupContent>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in platformItems" :key="item.title">
                    <SidebarMenuButton
                        v-if="hasPermission(item.permission)"
                        as-child
                        :is-active="item.current ? route().current(item.current) : false"
                        :tooltip="item.title"
                    >
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
                <SidebarMenuItem v-for="item in inboxItems" :key="item.title">
                    <SidebarMenuButton
                        v-if="hasPermission(item.permission)"
                        as-child
                        :is-active="item.current ? route().current(item.current) : false"
                        :tooltip="item.title"
                    >
                        <Link :href="route(item.href)">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                    <SidebarMenuBadge v-if="hasPermission(item.permission) && getNotifCount(item.notif) > 0">
                        {{ getNotifCount(item.notif) }}
                    </SidebarMenuBadge>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
</template>
