<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import SiteMenu from './SiteMenu.vue';

defineProps<{
    signedIn: boolean;
    actionHref: string;
}>();
</script>

<template>
    <div
        data-site-dock
        class="bg-card/95 invisible fixed top-3 right-3 z-40 flex items-center gap-1.5 rounded-xl border p-1.5 backdrop-blur"
        role="navigation"
        aria-label="Navigasi ringkas"
    >
        <Link
            :href="actionHref"
            class="focus-visible:ring-ring focus-visible:ring-offset-background bg-primary text-primary-foreground hover:bg-primary/90 inline-flex min-h-9 items-center rounded-lg px-3 text-sm font-medium whitespace-nowrap transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
        >
            {{ signedIn ? 'Dashboard' : 'Masuk' }}
        </Link>

        <SiteMenu label="Buka menu navigasi ringkas" />
    </div>
</template>
