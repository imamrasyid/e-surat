<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from '@lucide/vue';

defineProps<{
    signedIn: boolean;
    actionHref: string;
    about?: boolean;
}>();

const year = new Date().getFullYear();
</script>

<template>
    <section class="wl-close" aria-labelledby="wl-close-title">
        <div class="wl-close-cta">
            <h2 id="wl-close-title" class="wl-close-title">Mulai hari ini.</h2>

            <div class="wl-close-bottom">
                <p>Masuk dengan akun terdaftar dan buka kotak masuk hari ini.</p>
                <Link class="wl-close-action rounded-xl" :href="actionHref">
                    <span>{{ signedIn ? 'Buka dashboard' : 'Masuk ke sistem' }}</span>
                    <ArrowRight class="size-4" aria-hidden="true" />
                </Link>
            </div>
        </div>

        <footer class="wl-footer">
            <p class="wl-footer-name">e-Surat</p>
            <div class="wl-footer-meta">
                <p>Sistem persuratan dan disposisi elektronik untuk tata kelola surat yang lebih cepat, rapi, dan efisien.</p>
                <div class="wl-footer-links">
                    <Link :href="about ? route('home') : route('about')">{{ about ? 'Beranda' : 'Tentang aplikasi' }}</Link>
                    <a href="#top">Kembali ke atas</a>
                </div>
                <p class="wl-footer-copy">© {{ year }} e-Surat</p>
            </div>
        </footer>
    </section>
</template>
