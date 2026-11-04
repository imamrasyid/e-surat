<script setup lang="ts">
import { computed } from 'vue';
import type { Tone } from '../tone';

const props = defineProps<{
    tone: Tone;
}>();

/**
 * The tone only tints the panel shell. Inner mock UI keeps an opaque `bg-card`
 * surface so contrast never depends on the mixed background.
 */
const toneStyle = computed(() => ({
    '--about-tone': `var(--${props.tone})`,
    backgroundColor: 'color-mix(in srgb, var(--about-tone) 40%, var(--card))',
}));
</script>

<template>
    <div class="relative overflow-hidden rounded-2xl border" :style="toneStyle">
        <slot />
    </div>
</template>
