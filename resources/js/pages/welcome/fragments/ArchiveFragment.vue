<script setup lang="ts">
import { archiveLetters } from '../letterData';
</script>

<template>
    <div
        class="wl-ui wl-archive"
        data-ui
        role="img"
        aria-label="Arsip surat e-Surat: pencarian rapat menampilkan tiga surat dengan nomor agenda, kategori, dan status."
    >
        <div class="wl-ui-chrome">
            <span class="wl-ui-title">Arsip surat</span>
            <span class="wl-ui-count">5 hasil</span>
        </div>

        <div class="wl-archive-toolbar" aria-hidden="true">
            <div class="wl-ui-tabs">
                <span class="wl-ui-tab is-active">Semua</span>
                <span class="wl-ui-tab">Primary</span>
                <span class="wl-ui-tab">Undangan</span>
            </div>
            <div class="wl-ui-search rounded-lg">
                <span class="wl-ui-search-icon" aria-hidden="true"></span>
                <span>rapat</span>
            </div>
        </div>

        <div class="wl-archive-table" aria-hidden="true">
            <div class="wl-archive-head">
                <span>Agenda</span>
                <span>Surat</span>
                <span>Kategori</span>
                <span>Status</span>
            </div>
            <div v-for="letter in archiveLetters" :key="letter.agenda" class="wl-archive-row">
                <span class="wl-ui-figure">{{ letter.agenda }}</span>
                <span class="wl-archive-subject">
                    <b>{{ letter.sender }}</b>
                    {{ letter.subject }}
                </span>
                <span>{{ letter.category }}</span>
                <span class="wl-archive-status" :class="{ 'is-done': letter.status === 'Selesai' }">{{ letter.status }}</span>
            </div>
        </div>
    </div>
</template>
