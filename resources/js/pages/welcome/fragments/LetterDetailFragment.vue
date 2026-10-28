<script setup lang="ts">
import { primaryLetter } from '../letterData';
</script>

<template>
    <div class="wl-ui wl-detail" data-ui>
        <div class="wl-ui-chrome wl-detail-chrome">
            <span class="wl-ui-title">Surat masuk</span>
            <span class="wl-ui-chip">Terbuka</span>
        </div>

        <div class="wl-detail-body">
            <p class="wl-ui-caption">Nomor agenda</p>
            <p class="wl-detail-agenda">{{ primaryLetter.agenda }}</p>

            <dl class="wl-detail-fields">
                <div>
                    <dt>Pengirim</dt>
                    <dd>{{ primaryLetter.sender }}</dd>
                </div>
                <div>
                    <dt>Nomor surat</dt>
                    <dd>{{ primaryLetter.number }}</dd>
                </div>
                <div>
                    <dt>Perihal</dt>
                    <dd>{{ primaryLetter.subject }}</dd>
                </div>
                <div class="wl-detail-dates">
                    <span><b>Surat</b> {{ primaryLetter.date }}</span>
                    <span><b>Diterima</b> {{ primaryLetter.receiptDate }}</span>
                </div>
            </dl>

            <div class="wl-detail-section">
                <p class="wl-detail-section-title">Penerima</p>
                <div class="wl-detail-table" aria-hidden="true">
                    <div class="wl-detail-table-head">
                        <span>Dari</span>
                        <span>Kepada</span>
                        <span>Instruksi</span>
                        <span>Baca</span>
                    </div>
                    <div v-for="step in primaryLetter.recipients" :key="step.to" class="wl-detail-table-row">
                        <span>{{ step.from }}</span>
                        <span>{{ step.to }}</span>
                        <span>{{ step.instruction }}</span>
                        <span>{{ step.readAt }}</span>
                    </div>
                </div>
            </div>

            <div class="wl-detail-section">
                <p class="wl-detail-section-title">Lampiran</p>
                <div class="wl-detail-file" aria-hidden="true">
                    <span class="wl-file-mark">PDF</span>
                    <span class="wl-file-name">{{ primaryLetter.attachment }}</span>
                </div>
            </div>

            <div class="wl-detail-section">
                <p class="wl-detail-section-title">Riwayat</p>
                <ol class="wl-detail-trace" aria-hidden="true">
                    <li><span>13 Okt, 08.42</span>Diterima dan dicatat</li>
                    <li><span>13 Okt, 09.14</span>Diteruskan ke Kabag Umum</li>
                    <li><span>13 Okt, 10.02</span>Diteruskan ke Kasubbag Perencanaan</li>
                </ol>
            </div>

            <div class="wl-detail-section">
                <p class="wl-detail-section-title">Catatan</p>
                <p class="wl-detail-note">{{ primaryLetter.instruction }}</p>
            </div>

            <div class="wl-detail-foot" aria-hidden="true">
                <span>Status terakhir</span>
                <span>Terbuka</span>
            </div>
        </div>
    </div>
</template>
