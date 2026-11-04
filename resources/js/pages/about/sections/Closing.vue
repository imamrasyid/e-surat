<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from '@lucide/vue';

defineProps<{
    signedIn: boolean;
    actionHref: string;
}>();

const year = new Date().getFullYear();
</script>

<template>
    <div class="space-y-6">
        <section
            aria-labelledby="about-close-title"
            class="bg-primary text-primary-foreground flex flex-col gap-6 rounded-2xl p-6 sm:flex-row sm:items-end sm:justify-between sm:p-10"
        >
            <div class="space-y-2">
                <h2 id="about-close-title" data-close-title class="text-2xl font-semibold tracking-tight sm:text-4xl">Mulai dari kotak masuk.</h2>
                <p data-close-body class="max-w-[46ch] text-sm opacity-90 sm:text-base">
                    Masuk dengan akun terdaftar dan lihat surat yang menunggu tindak lanjut.
                </p>
            </div>

            <Link
                data-close-cta
                :href="actionHref"
                class="focus-visible:ring-ring focus-visible:ring-offset-background bg-background text-foreground hover:bg-background/90 inline-flex min-h-11 items-center gap-2 self-start rounded-lg px-4 text-sm font-medium transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none sm:self-auto"
            >
                <span>{{ signedIn ? 'Buka dashboard' : 'Masuk ke sistem' }}</span>
                <ArrowRight class="size-4" aria-hidden="true" />
            </Link>
        </section>

        <footer
            data-footer
            class="text-muted-foreground flex flex-col gap-3 border-t pt-6 text-xs sm:flex-row sm:flex-wrap sm:items-center sm:justify-between"
        >
            <span class="text-foreground text-sm font-semibold">e-Surat</span>
            <p class="max-w-[52ch]">Sistem persuratan dan disposisi elektronik untuk tata kelola surat yang lebih cepat, rapi, dan efisien.</p>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('home')"
                    class="focus-visible:ring-ring focus-visible:ring-offset-background hover:text-foreground rounded transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
                >
                    Beranda
                </Link>
                <a
                    href="#top"
                    class="focus-visible:ring-ring focus-visible:ring-offset-background hover:text-foreground rounded transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
                >
                    Kembali ke atas
                </a>
            </div>
            <p>© {{ year }} e-Surat</p>
        </footer>
    </div>
</template>
