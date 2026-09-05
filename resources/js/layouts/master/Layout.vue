<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { usePermissions } from '@/composables/usePermissions';
import masterNavItems from '@/data/masterDataMenu';
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

const filteredItems: ComputedRef<NavItem[]> = computed(() => filterMenu(masterNavItems));
</script>

<template>
    <div class="px-4 py-6">
        <Heading title="Master Data" description="Manage data referensi aplikasi" />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav class="flex flex-col space-y-1 space-x-0">
                    <Button
                        v-for="item in filteredItems"
                        :key="item.href"
                        variant="ghost"
                        :class="['w-full justify-start', { 'bg-muted': item.current && route().current(item.current) }]"
                        as-child
                    >
                        <Link :href="route(item.href)">
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-2xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
