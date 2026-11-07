<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import { useAppearance } from '@/composables/useAppearance';
import { Link } from '@inertiajs/vue3';
import { Moon, Sun } from '@lucide/vue';
import { aboutSections } from './nav';
import SiteMenu from './SiteMenu.vue';

defineProps<{
    signedIn: boolean;
    actionHref: string;
}>();

const { resolvedAppearance, updateAppearance } = useAppearance();

function toggleTheme() {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
}
</script>

<template>
    <header data-site-header class="flex items-center justify-between gap-3 py-3 sm:py-4">
        <Link
            :href="route('home')"
            data-load
            class="focus-visible:ring-ring focus-visible:ring-offset-background flex items-center gap-2.5 transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
            aria-label="e-Surat — beranda"
        >
            <AppLogo />
        </Link>

        <nav class="hidden items-center gap-4 lg:flex" aria-label="Bagian halaman">
            <a
                v-for="section in aboutSections"
                :key="section.href"
                :href="section.href"
                data-load
                class="text-muted-foreground hover:text-foreground text-sm underline-offset-4 hover:underline"
            >
                {{ section.label }}
            </a>
        </nav>

        <div class="flex items-center gap-1.5 sm:gap-2">
            <Link :href="route('home')" data-load class="text-muted-foreground hover:text-foreground text-sm underline-offset-4 hover:underline">
                Beranda
            </Link>

            <button
                type="button"
                data-load
                class="focus-visible:ring-ring focus-visible:ring-offset-background hover:bg-accent hover:text-accent-foreground dark:bg-input/30 bg-background text-foreground grid size-10 place-items-center rounded-lg border transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
                :aria-label="resolvedAppearance === 'dark' ? 'Ganti ke mode terang' : 'Ganti ke mode gelap'"
                @click="toggleTheme"
            >
                <Sun v-if="resolvedAppearance === 'dark'" class="size-4" aria-hidden="true" />
                <Moon v-else class="size-4" aria-hidden="true" />
            </button>

            <Link
                :href="actionHref"
                data-load
                class="focus-visible:ring-ring focus-visible:ring-offset-background bg-primary text-primary-foreground hover:bg-primary/90 inline-flex min-h-10 items-center rounded-lg px-3.5 text-sm font-medium whitespace-nowrap transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
            >
                {{ signedIn ? 'Dashboard' : 'Masuk' }}
            </Link>

            <div class="lg:hidden">
                <SiteMenu />
            </div>
        </div>
    </header>
</template>
