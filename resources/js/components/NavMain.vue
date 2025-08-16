<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuAction,
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
                    <SidebarMenuAction>
                        <div v-if="item.notif === 1 && props.inbox.primary > 0">
                            <span class="text-xs font-semibold">{{ props.inbox.primary }}</span>
                        </div>
                        <div v-if="item.notif === 2 && props.inbox.undangan > 0">
                            <span class="text-xs font-semibold">{{ props.inbox.undangan }}</span>
                        </div>
                        <div v-if="item.notif === 8 && props.inbox.disposisi > 0">
                            <span class="text-xs font-semibold">{{ props.inbox.disposisi }}</span>
                        </div>
                        <div v-if="item.notif === 9 && props.inbox.memo > 0">
                            <span class="text-xs font-semibold">{{ props.inbox.memo }}</span>
                        </div>
                    </SidebarMenuAction>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
</template>
