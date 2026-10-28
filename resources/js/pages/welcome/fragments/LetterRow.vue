<script setup lang="ts">
import { primaryLetter } from '../letterData';

withDefaults(
    defineProps<{
        state: 'new' | 'disposed' | 'done';
        interactive?: boolean;
    }>(),
    {
        interactive: false,
    },
);

defineEmits<{
    complete: [];
}>();
</script>

<template>
    <article class="wl-ui wl-row-fragment" data-ui :data-state="state">
        <div class="wl-row-main">
            <div class="wl-row-agenda">
                <span class="wl-ui-figure">{{ primaryLetter.agenda }}</span>
                <span v-if="state === 'new'" class="wl-ui-dot" aria-hidden="true"></span>
            </div>

            <div class="wl-row-body">
                <p class="wl-ui-name">{{ primaryLetter.senderShort }}</p>
                <p class="wl-ui-subject">{{ primaryLetter.subject }}</p>
            </div>

            <div class="wl-ui-marks" aria-hidden="true">
                <span class="wl-ui-mark is-disposition">D</span>
                <span class="wl-ui-mark is-memo">ND</span>
            </div>

            <span class="wl-ui-time">{{ state === 'done' ? primaryLetter.doneAt : primaryLetter.receivedAt }}</span>

            <span class="wl-row-status" :class="`is-${state}`">
                {{ state === 'new' ? 'Baru' : state === 'disposed' ? 'Berjalan' : 'Selesai' }}
            </span>
        </div>

        <div v-if="state === 'disposed'" class="wl-row-actions">
            <a class="wl-row-link" href="#catatan">Buka surat</a>
            <button v-if="interactive" class="wl-row-button" type="button" @click="$emit('complete')">Tandai selesai</button>
            <span v-else class="wl-row-note">Menunggu tindak lanjut</span>
        </div>

        <div v-if="state === 'done'" class="wl-row-done">
            <span>Selesai {{ primaryLetter.doneAt }}</span>
            <a class="wl-row-link" href="#catatan">Lihat arsip</a>
        </div>
    </article>
</template>
