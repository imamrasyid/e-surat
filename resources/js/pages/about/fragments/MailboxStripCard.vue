<script setup lang="ts">
import type { MailboxSample } from '../letterData';

defineProps<{
    item: MailboxSample;
}>();
</script>

<template>
    <article class="bg-card dark:bg-input/30 flex w-60 shrink-0 flex-col gap-3 rounded-xl border p-4 text-xs sm:w-72">
        <div class="flex items-center justify-between gap-2">
            <span class="text-foreground flex items-center gap-1.5 text-[11px] font-medium">
                <span class="size-1.5 rounded-full" :style="{ backgroundColor: `var(--${item.tone})` }" aria-hidden="true"></span>
                {{ item.label }}
            </span>
            <span class="text-muted-foreground text-[11px] whitespace-nowrap">{{ item.time }}</span>
        </div>

        <p class="text-muted-foreground font-mono text-[11px] tabular-nums">{{ item.agenda }}</p>

        <div class="min-w-0 space-y-0.5">
            <p class="truncate font-medium">{{ item.sender }}</p>
            <p class="text-muted-foreground line-clamp-2 leading-snug">{{ item.subject }}</p>
        </div>

        <div class="mt-auto flex items-center gap-1" aria-hidden="true">
            <span
                v-for="mark in item.marks"
                :key="mark"
                class="rounded px-1.5 py-0.5 text-[10px] font-medium"
                :class="mark === 'D' ? 'bg-primary/10 text-primary' : 'bg-warning/10 text-warning'"
            >
                {{ mark }}
            </span>
        </div>
    </article>
</template>
