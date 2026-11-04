<script setup lang="ts">
import { primaryLetter } from '../letterData';

const [sequence, classification, year] = primaryLetter.agenda.split('/');
</script>

<template>
    <div data-hero-card class="bg-card dark:bg-input/30 w-full overflow-hidden rounded-2xl border text-left">
        <div class="flex items-center justify-between gap-3 border-b px-4 py-3 sm:px-5">
            <span class="text-muted-foreground text-[11px] font-medium tracking-[0.16em] uppercase">Surat masuk</span>

            <span class="bg-primary/10 text-primary relative inline-flex h-6 w-23 items-center justify-center rounded-full text-[11px] font-medium">
                <span data-hero-status-layer class="absolute inset-0 grid place-items-center">Open</span>
                <span data-hero-status-layer aria-hidden="true" class="absolute inset-0 grid place-items-center opacity-0">Disposisi</span>
                <span data-hero-status-layer aria-hidden="true" class="absolute inset-0 grid place-items-center opacity-0">Close</span>
            </span>
        </div>

        <div class="space-y-4 px-4 py-5 sm:px-5">
            <div class="space-y-1">
                <p class="text-muted-foreground text-[10px] tracking-wide uppercase">Nomor agenda</p>
                <p class="font-mono text-2xl font-semibold tabular-nums sm:text-3xl">
                    {{ sequence }}<span class="text-muted-foreground/60">/</span>{{ classification }}<span class="text-muted-foreground/60">/</span
                    >{{ year }}
                </p>
            </div>

            <div class="space-y-1">
                <p class="text-sm font-medium">{{ primaryLetter.subject }}</p>
                <p class="text-muted-foreground text-xs">{{ primaryLetter.sender }}</p>
            </div>

            <dl class="divide-border text-xs">
                <div class="flex items-center justify-between gap-3 border-t py-2">
                    <dt class="text-muted-foreground">Diterima</dt>
                    <dd class="tabular-nums">{{ primaryLetter.receivedAt }}</dd>
                </div>
                <div class="flex items-center justify-between gap-3 border-t py-2">
                    <dt class="text-muted-foreground">Disposisi</dt>
                    <dd class="tabular-nums">{{ primaryLetter.recipients[0].readAt }}</dd>
                </div>
                <div class="flex items-center justify-between gap-3 border-t py-2">
                    <dt class="text-muted-foreground">Close</dt>
                    <dd class="tabular-nums">{{ primaryLetter.doneAt }}</dd>
                </div>
            </dl>
        </div>
    </div>
</template>
