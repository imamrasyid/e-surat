<script setup lang="ts">
import { computed, ref } from 'vue';
import LetterRow from './fragments/LetterRow.vue';

const middleState = ref<'disposed' | 'done'>('disposed');
const announcement = ref('');

const middleLabel = computed(() => (middleState.value === 'done' ? 'Selesai' : 'Sedang ditindaklanjuti'));

function complete() {
    middleState.value = 'done';
    announcement.value = 'Surat ditandai selesai. Status dan waktu penyelesaian tersimpan di arsip.';
}
</script>

<template>
    <section id="surat" class="wl-section wl-showcase-section" aria-labelledby="wl-showcase-title">
        <div class="wl-showcase-head">
            <h2 id="wl-showcase-title" class="wl-h2">Dari masuk hingga selesai</h2>
        </div>

        <div class="wl-copy-row">
            <p class="wl-copy-state">Baru masuk</p>
            <div class="wl-copy">
                <LetterRow state="new" />
            </div>
        </div>

        <div class="wl-copy-row">
            <p class="wl-copy-state">{{ middleLabel }}</p>
            <div class="wl-copy">
                <LetterRow :state="middleState" interactive @complete="complete" />
            </div>
        </div>

        <div class="wl-copy-row">
            <p class="wl-copy-state">Selesai</p>
            <div class="wl-copy">
                <LetterRow state="done" />
            </div>
        </div>

        <p class="sr-only" aria-live="polite">{{ announcement }}</p>
    </section>
</template>
