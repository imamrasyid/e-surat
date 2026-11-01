<script setup lang="ts">
import { Item, ItemContent, ItemDescription, ItemMedia, ItemTitle } from '@/components/ui/item';
import type { DashboardQuickAction } from '@/types/dashboard';
import { Link } from '@inertiajs/vue3';

defineProps<{
    items: DashboardQuickAction[];
}>();
</script>

<template>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
        <Link v-for="item in items" :key="item.key" :href="item.href">
            <Item variant="outline" class="hover:border-primary hover:text-primary dark:bg-input/30 rounded-lg">
                <ItemMedia variant="icon">
                    <component :is="item.icon" aria-hidden="true" />
                </ItemMedia>
                <ItemContent>
                    <ItemTitle>{{ item.title }}</ItemTitle>
                    <ItemDescription>{{ item.description }}</ItemDescription>
                </ItemContent>
            </Item>
        </Link>
    </div>
</template>
