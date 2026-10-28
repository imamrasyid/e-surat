<script setup lang="ts">
import LetterDetailFragment from './fragments/LetterDetailFragment.vue';

type Note = {
    id: string;
    side: 'left' | 'right';
    title: string;
    text: string;
};

const notes: Note[] = [
    {
        id: 'agenda',
        side: 'left',
        title: 'Nomor agenda',
        text: 'Terbit dari kode klasifikasi dan tahun. Tidak ada nomor yang diketik dua kali.',
    },
    {
        id: 'pengirim',
        side: 'left',
        title: 'Pengirim dan nomor asli',
        text: 'Identitas surat tetap menempel pada berkas aslinya.',
    },
    {
        id: 'penerima',
        side: 'left',
        title: 'Penerima',
        text: 'Disposisi berjenjang, dari pimpinan ke pelaksana.',
    },
    {
        id: 'lampiran',
        side: 'right',
        title: 'Lampiran',
        text: 'PDF surat asli disimpan pada nomor agenda yang sama.',
    },
    {
        id: 'riwayat',
        side: 'right',
        title: 'Riwayat baca',
        text: 'Waktu kirim, waktu baca, dan instruksi tercatat.',
    },
    {
        id: 'catatan',
        side: 'right',
        title: 'Catatan',
        text: 'Instruksi untuk pelaksana tetap terbaca di halaman surat.',
    },
];

const leftNotes = notes.filter((note) => note.side === 'left');
const rightNotes = notes.filter((note) => note.side === 'right');
</script>

<template>
    <section id="catatan" class="wl-section wl-features" aria-labelledby="wl-features-title">
        <div class="wl-plate">
            <h2 id="wl-features-title" class="wl-features-title">Yang tercatat pada surat</h2>

            <div class="wl-plate-panel">
                <LetterDetailFragment />
            </div>

            <div class="wl-notes is-left">
                <div v-for="note in leftNotes" :key="note.id" class="wl-note">
                    <p class="wl-note-title">{{ note.title }}</p>
                    <p class="wl-note-text">{{ note.text }}</p>
                </div>
            </div>

            <div class="wl-notes is-right">
                <div v-for="note in rightNotes" :key="note.id" class="wl-note">
                    <p class="wl-note-title">{{ note.title }}</p>
                    <p class="wl-note-text">{{ note.text }}</p>
                </div>
            </div>
        </div>

        <div class="wl-notes-mobile" aria-label="Catatan pada halaman surat">
            <div v-for="note in notes" :key="note.id" class="wl-note-mobile">
                <p class="wl-note-title">{{ note.title }}</p>
                <p class="wl-note-text">{{ note.text }}</p>
            </div>
        </div>
    </section>
</template>
