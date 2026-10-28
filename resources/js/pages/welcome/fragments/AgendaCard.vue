<script setup lang="ts">
import { primaryLetter } from '../letterData';
</script>

<template>
    <div
        class="wl-ui wl-agenda-card"
        data-ui
        role="img"
        :aria-label="`Nomor agenda ${primaryLetter.agenda} terbit dari kode klasifikasi 005 untuk tahun 2026.`"
    >
        <div class="wl-agenda-card-head">
            <span class="wl-ui-caption">Nomor agenda</span>
            <span class="wl-ui-chip">Terbit</span>
        </div>
        <p class="wl-agenda-number">{{ primaryLetter.agenda }}</p>
        <p class="wl-agenda-subject">{{ primaryLetter.subject }}</p>
        <dl class="wl-agenda-meta">
            <div>
                <dt>Klasifikasi</dt>
                <dd>005 · Undangan</dd>
            </div>
            <div>
                <dt>Tahun</dt>
                <dd>2026</dd>
            </div>
        </dl>
        <div class="wl-agenda-route" aria-hidden="true">
            <span>Masuk</span>
            <span>Disposisi</span>
            <span>Selesai</span>
        </div>
        <p class="wl-agenda-foot">Tercatat di buku agenda</p>
    </div>
</template>
