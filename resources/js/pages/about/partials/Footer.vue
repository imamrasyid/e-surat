<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer class="border-t border-border/60 bg-muted/30">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
            <div class="flex flex-col items-center justify-between gap-6 sm:flex-row">
                <!-- Brand & Info -->
                <div class="flex items-center gap-3">
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary text-primary-foreground shadow-xs">
                        <AppLogoIcon class="size-4.5 fill-current text-white" />
                    </div>
                    <div class="flex flex-col text-left">
                        <span class="text-sm font-bold text-foreground">e-Surat</span>
                        <span class="text-xs text-muted-foreground">Persuratan & Disposisi Elektronik</span>
                    </div>
                </div>

                <!-- Footer Navigation -->
                <div class="flex flex-wrap items-center justify-center gap-5 text-xs text-muted-foreground sm:gap-6">
                    <a href="#fitur" class="transition-colors hover:text-foreground">Fitur</a>
                    <a href="#alur-kerja" class="transition-colors hover:text-foreground">Cara Kerja</a>
                    <a href="#keamanan" class="transition-colors hover:text-foreground">Keamanan</a>
                    <Link :href="route('login')" class="font-medium text-primary hover:underline"> Masuk ke Sistem </Link>
                </div>
            </div>

            <div
                class="mt-6 flex flex-col items-center justify-between gap-2 border-t border-border/50 pt-5 text-center text-xs text-muted-foreground sm:mt-8 sm:flex-row sm:pt-6 sm:text-left"
            >
                <p>&copy; {{ new Date().getFullYear() }} e-Surat. Seluruh hak cipta dilindungi.</p>
                <p class="text-[11px]">Dibuat untuk mempermudah tata kelola persuratan dinas Anda.</p>
            </div>
        </div>
    </footer>
</template>
