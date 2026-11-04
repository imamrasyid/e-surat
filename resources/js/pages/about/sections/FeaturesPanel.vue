<script setup lang="ts">
import LetterDetailFragment from '../fragments/LetterDetailFragment.vue';

type Note = {
    id: string;
    title: string;
    text: string;
};

const notes: Note[] = [
    {
        id: 'agenda',
        title: 'Nomor agenda',
        text: 'Dibuat otomatis dari nomor urut, kode klasifikasi, dan tahun.',
    },
    {
        id: 'pengirim',
        title: 'Pengirim dan nomor asli',
        text: 'Identitas surat tetap menempel pada catatan dan lampiran aslinya.',
    },
    {
        id: 'penerima',
        title: 'Penerima',
        text: 'Disposisi mencatat jabatan pengirim, jabatan penerima, instruksi, dan waktu baca.',
    },
    {
        id: 'lampiran',
        title: 'Lampiran',
        text: 'Surat masuk wajib memiliki PDF; disposisi dan Nota Dinas dapat menyertakan PDF.',
    },
    {
        id: 'riwayat',
        title: 'Riwayat baca',
        text: 'Waktu kirim, waktu baca, instruksi, dan tindak lanjut tetap terlihat.',
    },
    {
        id: 'catatan',
        title: 'Catatan',
        text: 'Catatan pelaksana dan tindak lanjut menjadi bagian dari item yang diterima.',
    },
];
</script>

<template>
    <section id="catatan" class="scroll-mt-28 space-y-6" aria-labelledby="about-features-title">
        <div data-section-head class="space-y-3 text-center">
            <p class="text-muted-foreground text-xs font-medium tracking-[0.18em] uppercase">Catatan</p>
            <h2 id="about-features-title" data-section-title tabindex="-1" class="text-2xl font-semibold tracking-tight sm:text-3xl">
                Yang tetap terbaca.
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
            <div data-plate-panel class="bg-muted/30 rounded-xl border p-3 sm:p-4">
                <LetterDetailFragment />
            </div>

            <dl class="grid grid-cols-1 content-start gap-3 sm:grid-cols-2">
                <div v-for="note in notes" :key="note.id" data-note class="bg-card dark:bg-input/30 rounded-xl border p-4">
                    <dt class="text-sm font-medium">{{ note.title }}</dt>
                    <dd class="text-muted-foreground mt-1 text-sm leading-relaxed">{{ note.text }}</dd>
                </div>
            </dl>
        </div>
    </section>
</template>
