<script setup lang="ts">
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useAppearance } from '@/composables/useAppearance';
import { Link } from '@inertiajs/vue3';
import { Menu, Moon, Sun } from '@lucide/vue';
import { ref } from 'vue';
import { aboutSections } from './nav';

withDefaults(
    defineProps<{
        label?: string;
    }>(),
    {
        label: 'Buka menu bagian halaman',
    },
);

const open = ref(false);
const { resolvedAppearance, updateAppearance } = useAppearance();

function toggleTheme() {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
}

/**
 * The menu restores focus to its trigger on close, which would scroll the page
 * back to the header. Stop that, and hand focus to the target heading instead.
 */
function onSectionClick(href: string) {
    open.value = false;

    requestAnimationFrame(() => {
        document.querySelector<HTMLElement>(`${href} [data-section-title]`)?.focus({ preventScroll: true });
    });
}
</script>

<template>
    <DropdownMenu v-model:open="open">
        <DropdownMenuTrigger as-child>
            <button
                type="button"
                data-load
                class="focus-visible:ring-ring focus-visible:ring-offset-background hover:bg-accent hover:text-accent-foreground dark:bg-input/30 bg-background text-foreground grid size-10 place-items-center rounded-lg border transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
                :aria-label="label"
            >
                <Menu class="size-4" aria-hidden="true" />
            </button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end" class="w-48" @close-auto-focus.prevent>
            <DropdownMenuItem v-for="section in aboutSections" :key="section.href" as-child>
                <a :href="section.href" class="w-full" @click="onSectionClick(section.href)">{{ section.label }}</a>
            </DropdownMenuItem>

            <DropdownMenuSeparator />

            <DropdownMenuItem as-child>
                <Link :href="route('home')" class="w-full" @click="open = false">Beranda</Link>
            </DropdownMenuItem>

            <DropdownMenuSeparator />

            <DropdownMenuItem @select.prevent="toggleTheme">
                <Sun v-if="resolvedAppearance === 'dark'" aria-hidden="true" />
                <Moon v-else aria-hidden="true" />
                <span>{{ resolvedAppearance === 'dark' ? 'Mode terang' : 'Mode gelap' }}</span>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
