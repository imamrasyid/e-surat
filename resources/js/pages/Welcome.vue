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
        class="bg-background text-foreground selection:bg-primary/20 selection:text-primary relative flex min-h-screen flex-col items-center justify-center overflow-hidden"
    >
        <!-- Subtle background decoration -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div
                class="bg-primary/[0.03] dark:bg-primary/[0.06] absolute -top-1/2 left-1/2 h-[800px] w-[800px] -translate-x-1/2 rounded-full blur-3xl"
            />
            <div class="bg-primary/[0.02] dark:bg-primary/[0.04] absolute -bottom-1/3 left-1/4 h-[600px] w-[600px] rounded-full blur-3xl" />
        </div>

        <!-- Theme toggle (top-right) -->
        <div class="absolute top-6 right-6 z-10">
            <button
                type="button"
                class="border-border/60 bg-background/80 text-muted-foreground hover:bg-accent hover:text-foreground inline-flex size-9 items-center justify-center rounded-lg border backdrop-blur-sm transition-colors"
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
                    class="bg-primary text-primary-foreground shadow-primary/20 flex size-16 items-center justify-center rounded-2xl shadow-lg transition-transform duration-300 hover:scale-105 sm:size-20 sm:rounded-3xl"
                >
                    <AppLogoIcon class="size-8 fill-current text-white sm:size-10" />
                </div>
            </div>

            <!-- Greeting -->
            <h1 class="text-foreground mb-3 text-3xl font-bold tracking-tight sm:text-4xl md:text-5xl">
                Selamat Datang di
                <span class="text-primary">e-Surat</span>
            </h1>

            <p class="text-muted-foreground mx-auto mb-10 max-w-md text-base leading-relaxed sm:text-lg">
                Sistem Persuratan &amp; Disposisi Elektronik untuk tata kelola surat yang lebih cepat, rapi, dan efisien.
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col items-center gap-3 sm:flex-row sm:gap-4">
                <!-- Logged-in: go to Dashboard -->
                <Button v-if="user" as-child size="lg" class="shadow-primary/20 gap-2 shadow-md">
                    <Link :href="route('dashboard')">
                        <LayoutDashboard class="size-4.5" />
                        Buka Dashboard
                    </Link>
                </Button>

                <!-- Not logged-in: go to Login -->
                <Button v-else as-child size="lg" class="shadow-primary/20 gap-2 shadow-md">
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
        <footer class="text-muted-foreground/60 absolute bottom-6 z-10 flex items-center gap-1.5 text-xs">
            <span>&copy; {{ new Date().getFullYear() }} e-Surat</span>
            <span class="text-border">·</span>
            <Link href="/about" class="hover:text-muted-foreground transition-colors">
                <span class="inline-flex items-center gap-1">
                    Tentang
                    <ArrowRight class="size-3" />
                </span>
            </Link>
        </footer>
    </div>
</template>
