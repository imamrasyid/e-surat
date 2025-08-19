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
import { inboxItems, platformItems } from '@/data/mainNavMenu';
import { Link, usePage } from '@inertiajs/vue3';

const { props } = usePage();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarGroupContent>
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
        </SidebarGroupContent>
    </SidebarGroup>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Surat Masuk</SidebarGroupLabel>
        <SidebarGroupContent>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in inboxItems" :key="item.title">
                    <SidebarMenuButton as-child :is-active="item.current ? route().current(item.current) : false" :tooltip="item.title">
                        <Link :href="route(item.href)">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                    <SidebarMenuBadge v-if="item.notif === 1 && props.inbox.primary > 0">
                        {{ props.inbox.primary }}
                    </SidebarMenuBadge>
                    <SidebarMenuBadge v-if="item.notif === 2 && props.inbox.undangan > 0">
                        {{ props.inbox.undangan }}
                    </SidebarMenuBadge>
                    <SidebarMenuBadge v-if="item.notif === 8 && props.inbox.disposisi > 0">
                        {{ props.inbox.disposisi }}
                    </SidebarMenuBadge>
                    <SidebarMenuBadge v-if="item.notif === 9 && props.inbox.memo > 0">
                        {{ props.inbox.memo }}
                    </SidebarMenuBadge>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
</template>
