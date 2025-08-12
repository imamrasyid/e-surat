<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { inboxItems, platformItems } from '@/data/mainNavMenu';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in platformItems" :key="item.title">
                <SidebarMenuButton as-child :is-active="item.current ? route().current(item.current) : false" :tooltip="item.title">
                    <Link :href="route(item.href)">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Surat Masuk</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in inboxItems" :key="item.title">
                <SidebarMenuButton as-child :is-active="item.current ? route().current(item.current) : false" :tooltip="item.title">
                    <Link :href="route(item.href)">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
