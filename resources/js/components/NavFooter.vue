<script setup lang="ts">
import { SidebarGroup, SidebarGroupContent, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { usePermissions } from '@/composables/usePermissions';
import footerNavItems from '@/data/footerNavMenu';
import { Link } from '@inertiajs/vue3';

const { can } = usePermissions();

function hasPermission(permission?: string): boolean {
    if (!permission) return false;
    if (permission === '*') return true;
    return can(permission);
}
</script>

<template>
    <SidebarGroup :class="`group-data-[collapsible=icon]:p-0 ${$props.class || ''}`">
        <SidebarGroupContent>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in footerNavItems" :key="item.title">
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
</template>
