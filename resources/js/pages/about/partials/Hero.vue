<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link, usePage } from '@inertiajs/vue3';
import { CheckCircle2, FileText, LayoutDashboard, LogIn, ShieldCheck, Sparkles, Zap } from '@lucide/vue';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <section class="relative overflow-hidden pt-8 pb-12 sm:pt-16 sm:pb-20 lg:pt-20 lg:pb-24">
        <!-- Background Ambient Glow -->
        <div class="pointer-events-none absolute inset-0 -z-10 flex items-center justify-center overflow-hidden">
            <div class="bg-primary/10 dark:bg-primary/15 h-[280px] w-[340px] rounded-full blur-[90px] sm:h-[380px] sm:w-[620px] sm:blur-[120px]" />
            <div
                class="bg-success/10 absolute -top-20 right-1/4 h-[200px] w-[200px] rounded-full blur-[80px] sm:-top-32 sm:h-[300px] sm:w-[300px] sm:blur-[100px]"
            />
        </div>

        <div class="mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <!-- Badge -->
            <div
                class="border-primary/20 bg-primary/5 text-primary inline-flex items-center gap-2 rounded-full border px-3.5 py-1 text-xs font-semibold shadow-xs transition-transform hover:scale-105 sm:px-4 sm:py-1.5"
            >
                <Sparkles class="text-primary size-3.5 shrink-0" />
                <span class="truncate">Persuratan & Disposisi jadi Lebih Mudah</span>
            </div>

            <!-- Main Heading -->
            <h1 class="text-foreground mt-5 text-3xl font-extrabold tracking-tight sm:mt-6 sm:text-5xl lg:text-6xl">
                Urus Surat Dinas Tanpa Ribet,
                <span class="from-primary block bg-gradient-to-r via-emerald-500 to-teal-500 bg-clip-text text-transparent sm:inline">
                    Semua Serba Digital.
                </span>
            </h1>

            <!-- Subtitle -->
            <p class="text-muted-foreground mx-auto mt-4 max-w-2xl text-sm leading-relaxed sm:mt-6 sm:text-base lg:text-lg">
                Tidak perlu lagi bolak-balik antar ruangan untuk mengantar surat. Mulai dari mencatat surat masuk, menyusun surat keluar, sampai
                disposisi pimpinan — semua bisa dilakukan dari satu tempat yang aman dan mudah diakses.
            </p>

            <!-- Call to Actions -->
            <div class="mt-6 flex flex-col items-stretch justify-center gap-3 sm:mt-8 sm:flex-row sm:items-center sm:gap-4">
                <Button v-if="user" as-child size="lg" class="shadow-primary/25 w-full gap-2 px-6 text-base font-semibold shadow-md sm:w-auto">
                    <Link :href="route('dashboard')">
                        <LayoutDashboard class="size-5" />
                        <span>Buka Dashboard</span>
                    </Link>
                </Button>
                <Button v-else as-child size="lg" class="shadow-primary/25 w-full gap-2 px-6 text-base font-semibold shadow-md sm:w-auto">
                    <Link :href="route('login')">
                        <LogIn class="size-5" />
                        <span>Masuk ke Sistem</span>
                    </Link>
                </Button>

                <Button as-child variant="outline" size="lg" class="w-full gap-2 px-6 text-base font-medium sm:w-auto">
                    <a href="#alur-kerja">
                        <FileText class="size-4.5" />
                        <span>Lihat Cara Kerjanya</span>
                    </a>
                </Button>
            </div>

            <!-- Trust Badges -->
            <div
                class="text-muted-foreground mt-10 flex flex-wrap items-center justify-center gap-x-6 gap-y-2.5 text-xs font-medium sm:mt-12 sm:gap-x-8 sm:text-sm"
            >
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <ShieldCheck class="text-primary size-4" />
                    <span>Login Aman dengan 2FA</span>
                </div>
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <Zap class="text-primary size-4" />
                    <span>Disposisi Langsung dari Gawai</span>
                </div>
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <CheckCircle2 class="text-primary size-4" />
                    <span>Riwayat Surat Selalu Tercatat</span>
                </div>
            </div>
        </div>
    </section>
</template>
