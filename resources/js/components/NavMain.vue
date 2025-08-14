<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { inboxItems, platformItems } from '@/data/mainNavMenu';
import { Link, usePage } from '@inertiajs/vue3';

const { props } = usePage();
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
                    <Link :href="route(item.href)" class="justify-between">
                        <div class="flex items-center gap-2">
                            <component :is="item.icon" class="h-4 w-4" />
                            <span>{{ item.title }}</span>
                        </div>
                        <div v-if="item.notif === 1 && props.inbox.primary > 0" class="self-center">
                            <span class="text-xs font-semibold">{{ props.inbox.primary }}</span>
                        </div>
                        <div v-if="item.notif === 2 && props.inbox.undangan > 0" class="self-center">
                            <span class="text-xs font-semibold">{{ props.inbox.undangan }}</span>
                        </div>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
