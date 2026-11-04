<script setup lang="ts">
import type { Component } from 'vue';
import TonePanel from '../components/TonePanel.vue';
import StackAgendaFragment from '../fragments/StackAgendaFragment.vue';
import StackCloseDemo from '../fragments/StackCloseDemo.vue';
import StackDispositionFragment from '../fragments/StackDispositionFragment.vue';
import StackMemoFragment from '../fragments/StackMemoFragment.vue';
import type { Tone } from '../tone';

type FlowStep = {
    title: string;
    body: string;
    tone: Tone;
    fragment: Component;
};

const steps: FlowStep[] = [
    {
        title: 'Dicatat sekali',
        body: 'Petugas mencatat pengirim, nomor surat, perihal, penerima, dan PDF asli. Sistem menerbitkan nomor agenda dari nomor urut, kode klasifikasi, dan tahun.',
        tone: 'primary',
        fragment: StackAgendaFragment,
    },
    {
        title: 'Didisposisikan berjenjang',
        body: 'Penerima meneruskan surat ke jabatan berikutnya dengan instruksi, tindak lanjut, dan tembusan. Setiap pengirim, penerima, waktu kirim, dan waktu baca tersimpan.',
        tone: 'warning',
        fragment: StackDispositionFragment,
    },
    {
        title: 'Dijawab dengan Nota Dinas',
        body: 'Penerima dapat mengirim Nota Dinas kepada jabatan lain pada surat yang sama, dengan catatan, tembusan, dan lampiran PDF bila diperlukan.',
        tone: 'info',
        fragment: StackMemoFragment,
    },
    {
        title: 'Ditutup dengan Nota Dinas',
        body: 'Pengelola menandai surat selesai melalui Nota Dinas penutup. Status berubah menjadi Close dan aksi disposisi, Nota Dinas baru, serta edit surat terkunci.',
        tone: 'success',
        fragment: StackCloseDemo,
    },
];
</script>

<template>
    <section id="alur" class="scroll-mt-28 space-y-8" aria-labelledby="about-flow-title">
        <div data-section-head class="space-y-3 text-center">
            <p class="text-muted-foreground text-xs font-medium tracking-[0.18em] uppercase">Alur</p>
            <h2 id="about-flow-title" data-section-title tabindex="-1" class="text-2xl font-semibold tracking-tight sm:text-3xl">
                Satu surat, empat tahap.
            </h2>
            <p class="text-muted-foreground mx-auto max-w-[54ch] text-sm text-pretty sm:text-base">
                Catatan bergerak dari pencatatan, ke jabatan berikutnya, lalu kembali sebagai Nota Dinas penutup.
            </p>
        </div>

        <ol data-flow-stack class="relative flex flex-col gap-6">
            <li v-for="(step, index) in steps" :key="step.title" data-flow-card class="relative">
                <TonePanel data-flow-surface :tone="step.tone" class="will-change-transform">
                    <div data-flow-scrim class="bg-background pointer-events-none absolute inset-0 opacity-0" aria-hidden="true"></div>

                    <div data-flow-content class="relative flex h-full flex-col gap-5 p-6 sm:p-8">
                        <div class="space-y-2">
                            <p class="text-foreground/80 font-mono text-xs tracking-[0.2em] tabular-nums">0{{ index + 1 }}</p>
                            <h3 class="text-xl font-semibold tracking-tight sm:text-2xl">{{ step.title }}</h3>
                            <p class="text-foreground/80 max-w-[62ch] text-sm leading-relaxed sm:text-base">{{ step.body }}</p>
                        </div>

                        <component :is="step.fragment" />
                    </div>
                </TonePanel>
            </li>

            <li data-flow-spacer class="pointer-events-none hidden" aria-hidden="true"></li>
        </ol>
    </section>
</template>
