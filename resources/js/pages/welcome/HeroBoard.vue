<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { useAppearance } from '@/composables/useAppearance';
import { Link } from '@inertiajs/vue3';
import { Moon, Sun } from '@lucide/vue';
import AgendaCard from './fragments/AgendaCard.vue';
import ArchiveFragment from './fragments/ArchiveFragment.vue';
import InboxFragment from './fragments/InboxFragment.vue';

defineProps<{
    signedIn: boolean;
    actionHref: string;
    about?: boolean;
}>();

const { resolvedAppearance, updateAppearance } = useAppearance();

function toggleTheme() {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
}
</script>

<template>
    <section class="wl-hero" aria-labelledby="wl-title">
        <div class="wl-top">
            <div class="wl-tile-logo rounded-lg">
                <AppLogoIcon class="size-8" />
                <span class="sr-only">e-Surat</span>
            </div>

            <div class="wl-nav-tile rounded-lg">
                <span class="wl-nav-word">e-Surat</span>
                <nav class="wl-nav" aria-label="Navigasi utama">
                    <a href="#alur">Alur</a>
                    <a href="#surat">Surat</a>
                    <a href="#catatan">Catatan</a>
                    <a href="#tanya">Tanya</a>
                    <Link :href="about ? route('home') : route('about')">{{ about ? 'Beranda' : 'Tentang' }}</Link>
                </nav>
                <button
                    type="button"
                    class="wl-theme rounded-lg"
                    :aria-label="resolvedAppearance === 'dark' ? 'Ganti ke mode terang' : 'Ganti ke mode gelap'"
                    @click="toggleTheme"
                >
                    <Sun v-if="resolvedAppearance === 'dark'" class="size-5" />
                    <Moon v-else class="size-5" />
                </button>
            </div>

            <Link class="wl-action-tile rounded-lg" :href="actionHref">
                <span>{{ signedIn ? 'Dashboard' : 'Masuk' }}</span>
            </Link>
        </div>

        <div class="wl-board">
            <div class="wl-tile wl-tile-name rounded-lg">
                <h1 id="wl-title" class="wl-name">e-Surat</h1>
                <p class="wl-lead">Sistem tata kelola persuratan dan disposisi elektronik.</p>
            </div>

            <div class="wl-tile wl-tile-media-inbox rounded-lg">
                <div class="wl-frag-wrap">
                    <div class="wl-shutter">
                        <InboxFragment />
                    </div>
                </div>
            </div>

            <div class="wl-tile wl-tile-media wl-tile-archive rounded-lg">
                <div class="wl-frag-wrap">
                    <ArchiveFragment />
                </div>
            </div>

            <div class="wl-split">
                <div class="wl-tile wl-tile-dark rounded-lg">
                    <p>Satu nomor agenda untuk seluruh jejak surat.</p>
                    <ol class="wl-tile-dark-steps" aria-hidden="true">
                        <li>Masuk</li>
                        <li>Didisposisi</li>
                        <li>Selesai</li>
                    </ol>
                    <span class="wl-tile-dark-foot">Terakhir dicatat 13 Okt 2026</span>
                </div>
                <AgendaCard />
            </div>
        </div>
    </section>
</template>
