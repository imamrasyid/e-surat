<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { useAppearance } from '@/composables/useAppearance';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, Info, LayoutDashboard, LogIn, Moon, Sun } from '@lucide/vue';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const { resolvedAppearance, updateAppearance } = useAppearance();

function toggleTheme() {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
}
</script>

<template>
    <Head title="e-Surat • Sistem Persuratan & Disposisi Elektronik" />

    <div
        class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-background text-foreground selection:bg-primary/20 selection:text-primary"
    >
        <!-- Subtle background decoration -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div
                class="absolute -top-1/2 left-1/2 h-[800px] w-[800px] -translate-x-1/2 rounded-full bg-primary/[0.03] blur-3xl dark:bg-primary/[0.06]"
            />
            <div class="absolute -bottom-1/3 left-1/4 h-[600px] w-[600px] rounded-full bg-primary/[0.02] blur-3xl dark:bg-primary/[0.04]" />
        </div>

        <!-- Theme toggle (top-right) -->
        <div class="absolute top-6 right-6 z-10">
            <button
                type="button"
                class="inline-flex size-9 items-center justify-center rounded-lg border border-border/60 bg-background/80 text-muted-foreground backdrop-blur-sm transition-colors hover:bg-accent hover:text-foreground"
                :title="resolvedAppearance === 'dark' ? 'Ganti ke Mode Terang' : 'Ganti ke Mode Gelap'"
                @click="toggleTheme"
            >
                <Sun v-if="resolvedAppearance === 'dark'" class="size-4.5" />
                <Moon v-else class="size-4.5" />
                <span class="sr-only">Toggle Theme</span>
            </button>
        </div>

        <!-- Main content -->
        <main class="relative z-10 flex flex-col items-center px-6 text-center">
            <!-- Logo -->
            <div class="mb-8 flex items-center justify-center">
                <div
                    class="flex size-16 items-center justify-center rounded-2xl bg-primary text-primary-foreground shadow-lg shadow-primary/20 transition-transform duration-300 hover:scale-105 sm:size-20 sm:rounded-3xl"
                >
                    <AppLogoIcon class="size-8 fill-current text-white sm:size-10" />
                </div>
            </div>

            <!-- Greeting -->
            <h1 class="mb-3 text-3xl font-bold tracking-tight text-foreground sm:text-4xl md:text-5xl">
                Selamat Datang di
                <span class="text-primary">e-Surat</span>
            </h1>

            <p class="mx-auto mb-10 max-w-md text-base leading-relaxed text-muted-foreground sm:text-lg">
                Sistem Persuratan &amp; Disposisi Elektronik untuk tata kelola surat yang lebih cepat, rapi, dan efisien.
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col items-center gap-3 sm:flex-row sm:gap-4">
                <!-- Logged-in: go to Dashboard -->
                <Button v-if="user" as-child size="lg" class="gap-2 shadow-md shadow-primary/20">
                    <Link :href="route('dashboard')">
                        <LayoutDashboard class="size-4.5" />
                        Buka Dashboard
                    </Link>
                </Button>

                <!-- Not logged-in: go to Login -->
                <Button v-else as-child size="lg" class="gap-2 shadow-md shadow-primary/20">
                    <Link :href="route('login')">
                        <LogIn class="size-4.5" />
                        Masuk ke Sistem
                    </Link>
                </Button>

                <!-- About / Tentang link -->
                <Button as-child variant="outline" size="lg" class="gap-2">
                    <Link :href="route('about')">
                        <Info class="size-4.5" />
                        Tentang Aplikasi
                    </Link>
                </Button>
            </div>
        </main>

        <!-- Footer -->
        <footer class="absolute bottom-6 z-10 flex items-center gap-1.5 text-xs text-muted-foreground/60">
            <span>&copy; {{ new Date().getFullYear() }} e-Surat</span>
            <span class="text-border">·</span>
            <Link href="/about" class="transition-colors hover:text-muted-foreground">
                <span class="inline-flex items-center gap-1">
                    Tentang
                    <ArrowRight class="size-3" />
                </span>
            </Link>
        </footer>
    </div>
</template>
