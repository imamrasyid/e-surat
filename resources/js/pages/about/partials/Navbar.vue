<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { useAppearance } from '@/composables/useAppearance';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutDashboard, LogIn, Menu, Moon, Sun, X } from '@lucide/vue';
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const { resolvedAppearance, updateAppearance } = useAppearance();
const isMobileMenuOpen = ref(false);

function toggleTheme() {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
}

function closeMobileMenu() {
    isMobileMenuOpen.value = false;
}
</script>

<template>
    <header class="sticky top-0 z-50 w-full border-b border-border/40 bg-background/90 backdrop-blur-md">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-3 sm:px-6 lg:px-8">
            <!-- Brand -->
            <Link :href="route('home')" class="flex items-center gap-2.5 transition-opacity hover:opacity-90 sm:gap-3">
                <div
                    class="flex size-8.5 items-center justify-center rounded-lg bg-primary text-primary-foreground shadow-xs shadow-primary/20 sm:size-9"
                >
                    <AppLogoIcon class="size-4.5 fill-current text-white sm:size-5" />
                </div>
                <div class="flex flex-col">
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <span class="text-base font-bold tracking-tight text-foreground sm:text-lg">e-Surat</span>
                        <span
                            class="hidden items-center gap-1 rounded-full border border-primary/20 bg-primary/10 px-2 py-0.5 text-[10px] font-medium text-primary sm:inline-flex"
                        >
                            <span class="size-1.5 animate-pulse rounded-full bg-primary" />
                            Sistem Aktif
                        </span>
                    </div>
                    <span class="hidden text-[11px] text-muted-foreground md:inline">Persuratan & Disposisi Elektronik</span>
                </div>
            </Link>

            <!-- Desktop Navigation Links -->
            <nav class="hidden items-center gap-6 md:flex">
                <a href="#fitur" class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"> Fitur </a>
                <a href="#alur-kerja" class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"> Cara Kerja </a>
                <a href="#keamanan" class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"> Keamanan </a>
            </nav>

            <!-- Actions -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Theme Toggle Button -->
                <button
                    type="button"
                    class="inline-flex size-8.5 items-center justify-center rounded-lg border border-border/60 bg-background text-muted-foreground transition-colors hover:bg-accent hover:text-foreground sm:size-9"
                    :title="resolvedAppearance === 'dark' ? 'Ganti ke Mode Terang' : 'Ganti ke Mode Gelap'"
                    @click="toggleTheme"
                >
                    <Sun v-if="resolvedAppearance === 'dark'" class="size-4 sm:size-4.5" />
                    <Moon v-else class="size-4 sm:size-4.5" />
                    <span class="sr-only">Toggle Theme</span>
                </button>

                <!-- Auth Action Button -->
                <Button v-if="user" as-child class="gap-1.5 px-3 shadow-xs shadow-primary/20 sm:gap-2 sm:px-4">
                    <Link :href="route('dashboard')">
                        <LayoutDashboard class="size-4" />
                        <span class="hidden sm:inline">Buka Dashboard</span>
                        <span class="sm:hidden">Dashboard</span>
                    </Link>
                </Button>
                <Button v-else as-child class="gap-1.5 px-3 shadow-xs shadow-primary/20 sm:gap-2 sm:px-4">
                    <Link :href="route('login')">
                        <LogIn class="size-4" />
                        Masuk
                    </Link>
                </Button>

                <!-- Mobile Menu Button -->
                <button
                    type="button"
                    class="inline-flex size-8.5 items-center justify-center rounded-lg border border-border/60 bg-background text-muted-foreground transition-colors hover:bg-accent hover:text-foreground md:hidden"
                    :aria-expanded="isMobileMenuOpen"
                    aria-label="Buka Menu Navigasi"
                    @click="isMobileMenuOpen = !isMobileMenuOpen"
                >
                    <X v-if="isMobileMenuOpen" class="size-4.5" />
                    <Menu v-else class="size-4.5" />
                </button>
            </div>
        </div>

        <!-- Mobile Navigation Menu Dropdown -->
        <div v-if="isMobileMenuOpen" class="border-b border-border/60 bg-background/95 px-4 pt-2 pb-4 shadow-lg backdrop-blur-md md:hidden">
            <div class="flex flex-col space-y-3 pt-1">
                <a
                    href="#fitur"
                    class="rounded-md px-3 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent"
                    @click="closeMobileMenu"
                >
                    Fitur
                </a>
                <a
                    href="#alur-kerja"
                    class="rounded-md px-3 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent"
                    @click="closeMobileMenu"
                >
                    Cara Kerja
                </a>
                <a
                    href="#keamanan"
                    class="rounded-md px-3 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent"
                    @click="closeMobileMenu"
                >
                    Keamanan
                </a>
            </div>
        </div>
    </header>
</template>
