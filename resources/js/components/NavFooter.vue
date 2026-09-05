<script setup lang="ts">
import { SidebarGroup, SidebarGroupContent, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { usePermissions } from '@/composables/usePermissions';
import footerNavItems from '@/data/footerNavMenu';
import type { NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { computed, type ComputedRef } from 'vue';

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

const filteredItems: ComputedRef<NavItem[]> = computed(() => filterMenu(footerNavItems));
const visibleItems = computed(() => footerNavItems.filter((item) => hasPermission(item.permission)));
</script>

<template>
    <SidebarGroup v-if="visibleItems.length > 0" :class="`group-data-[collapsible=icon]:p-0`">
        <SidebarGroupContent>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in filteredItems" :key="item.title">
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
</template>
