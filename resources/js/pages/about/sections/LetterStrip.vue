<script setup lang="ts">
import MailboxStripCard from '../fragments/MailboxStripCard.vue';
import { mailboxStrip } from '../letterData';
</script>

<template>
    <section class="space-y-6" aria-labelledby="about-strip-title">
        <h2 id="about-strip-title" data-section-head class="text-center text-xl font-semibold tracking-tight sm:text-2xl">
            Satu surat, lima tempat kerja.
        </h2>

        <div data-marquee-viewport class="-mx-4 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0">
            <ul data-marquee-track class="flex w-max will-change-transform">
                <li
                    v-for="copy in [1, 2]"
                    :key="copy"
                    :data-marquee-copy="copy"
                    class="flex gap-3 pr-3"
                    :class="copy === 2 ? 'hidden' : ''"
                    :aria-hidden="copy === 2 ? 'true' : undefined"
                >
                    <MailboxStripCard v-for="item in mailboxStrip" :key="`${copy}-${item.label}`" :item="item" />
                </li>
            </ul>
        </div>
    </section>
</template>
