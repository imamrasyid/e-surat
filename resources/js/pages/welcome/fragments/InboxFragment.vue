<script setup lang="ts">
import { inboxLetters } from '../letterData';
</script>

<template>
    <div class="wl-ui wl-inbox" data-ui role="img" aria-label="Kotak masuk e-Surat: tiga surat dengan nomor agenda, pengirim, dan tanda disposisi.">
        <div class="wl-ui-chrome">
            <span class="wl-ui-title">Kotak masuk</span>
            <span class="wl-ui-count">5 surat</span>
        </div>

        <div class="wl-ui-tabs" aria-hidden="true">
            <span class="wl-ui-tab is-active">Primary <b>2</b></span>
            <span class="wl-ui-tab">Undangan <b>1</b></span>
        </div>

        <div class="wl-inbox-rows">
            <div v-for="letter in inboxLetters" :key="letter.agenda" class="wl-inbox-row" :class="{ 'is-unread': letter.unread }">
                <div class="wl-inbox-agenda">
                    <span class="wl-ui-figure">{{ letter.agenda }}</span>
                    <span v-if="letter.unread" class="wl-ui-dot" aria-hidden="true"></span>
                </div>
                <div class="wl-inbox-body">
                    <p class="wl-ui-name">{{ letter.sender }}</p>
                    <p class="wl-ui-subject">{{ letter.subject }}</p>
                </div>
                <div class="wl-ui-marks" aria-hidden="true">
                    <span v-for="mark in letter.marks" :key="mark" class="wl-ui-mark" :class="`is-${mark === 'D' ? 'disposition' : 'memo'}`">
                        {{ mark }}
                    </span>
                </div>
                <span class="wl-ui-time">{{ letter.time }}</span>
            </div>
        </div>
    </div>
</template>
