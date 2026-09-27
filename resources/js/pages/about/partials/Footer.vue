<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer class="border-border/60 bg-muted/30 border-t">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
            <div class="flex flex-col items-center justify-between gap-6 sm:flex-row">
                <!-- Brand & Info -->
                <div class="flex items-center gap-3">
                    <div class="bg-primary text-primary-foreground flex size-8 shrink-0 items-center justify-center rounded-lg shadow-xs">
                        <AppLogoIcon class="size-4.5 fill-current text-white" />
                    </div>
                    <div class="flex flex-col text-left">
                        <span class="text-foreground text-sm font-bold">e-Surat</span>
                        <span class="text-muted-foreground text-xs">Persuratan & Disposisi Elektronik</span>
                    </div>
                </div>

                <!-- Footer Navigation -->
                <div class="text-muted-foreground flex flex-wrap items-center justify-center gap-5 text-xs sm:gap-6">
                    <a href="#fitur" class="hover:text-foreground transition-colors">Fitur</a>
                    <a href="#alur-kerja" class="hover:text-foreground transition-colors">Cara Kerja</a>
                    <a href="#keamanan" class="hover:text-foreground transition-colors">Keamanan</a>
                    <Link :href="route('login')" class="text-primary font-medium hover:underline"> Masuk ke Sistem </Link>
                </div>
            </div>

            <div
                class="border-border/50 text-muted-foreground mt-6 flex flex-col items-center justify-between gap-2 border-t pt-5 text-center text-xs sm:mt-8 sm:flex-row sm:pt-6 sm:text-left"
            >
                <p>&copy; {{ new Date().getFullYear() }} e-Surat. Seluruh hak cipta dilindungi.</p>
                <p class="text-[11px]">Dibuat untuk mempermudah tata kelola persuratan dinas Anda.</p>
            </div>
        </div>
    </footer>
</template>
