<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import HeroLetterCard from '../fragments/HeroLetterCard.vue';

defineProps<{
    signedIn: boolean;
    actionHref: string;
}>();

const facts = [
    { value: '4', label: 'Kotak Kerja', detail: '2 Kotak Masuk, Disposisi, Nota Dinas' },
    { value: '1', label: 'Nomor Agenda per Surat', detail: 'Nomor urut, kode klasifikasi, tahun' },
    { value: '2', label: 'Kanal Notifikasi', detail: 'Email dan Telegram' },
    { value: '3', label: 'Peran Bawaan', detail: 'Super Admin, Pengelola, Pengguna' },
];
</script>

<template>
    <section class="flex flex-col items-center gap-8 pt-8 text-center sm:gap-10 sm:pt-14" aria-labelledby="about-title">
        <p data-load class="text-muted-foreground text-xs font-medium tracking-[0.18em] uppercase">Tentang e-Surat</p>

        <h1 id="about-title" class="text-4xl leading-[1.02] font-semibold tracking-tighter sm:text-6xl lg:text-7xl">
            <span data-hero-line class="block">Satu surat,</span>
            <span data-hero-line class="block">satu catatan.</span>
        </h1>

        <p data-hero-lead class="text-muted-foreground max-w-[54ch] text-base text-pretty sm:text-lg">
            e-Surat mencatat surat dari kotak masuk sampai ditutup—nomor agenda otomatis, disposisi berjenjang, dan riwayat baca dalam satu jejak.
        </p>

        <div data-hero-cta class="flex flex-wrap items-center justify-center gap-3">
            <Link
                :href="actionHref"
                class="focus-visible:ring-ring focus-visible:ring-offset-background bg-primary text-primary-foreground hover:bg-primary/90 inline-flex min-h-11 items-center rounded-lg px-5 text-sm font-medium transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
            >
                {{ signedIn ? 'Buka dashboard' : 'Masuk ke sistem' }}
            </Link>
            <a
                href="#alur"
                class="focus-visible:ring-ring focus-visible:ring-offset-background hover:bg-accent hover:text-accent-foreground inline-flex min-h-11 items-center rounded-lg border px-5 text-sm font-medium transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
            >
                Lihat alurnya
            </a>
        </div>

        <dl data-hero-facts class="grid w-full max-w-3xl grid-cols-2 gap-x-4 gap-y-8 sm:grid-cols-4">
            <div v-for="fact in facts" :key="fact.label" class="flex flex-col items-center gap-1">
                <dt class="order-2 text-sm font-medium">{{ fact.label }}</dt>
                <dd class="order-1 text-3xl font-semibold tabular-nums">{{ fact.value }}</dd>
                <dd class="text-muted-foreground order-3 text-[11px] leading-snug">{{ fact.detail }}</dd>
            </div>
        </dl>

        <div data-hero-panel class="w-full max-w-xl">
            <HeroLetterCard />
        </div>
    </section>
</template>
