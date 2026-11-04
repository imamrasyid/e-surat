<script setup lang="ts">
import { primaryLetter } from '../letterData';

const chain = [primaryLetter.recipients[0].from, ...primaryLetter.recipients.map((step) => step.to)];
</script>

<template>
    <div class="bg-card dark:bg-input/30 space-y-3 rounded-xl border p-4 text-xs">
        <p class="flex flex-wrap items-center gap-1.5 text-[11px] font-medium">
            <template v-for="(name, index) in chain" :key="name">
                <span>{{ name }}</span>
                <span v-if="index < chain.length - 1" class="text-muted-foreground" aria-hidden="true">→</span>
            </template>
        </p>

        <ul class="divide-border divide-y border-t">
            <li v-for="step in primaryLetter.recipients" :key="step.to" class="space-y-1 py-2">
                <div class="flex items-baseline justify-between gap-2">
                    <span class="font-medium">{{ step.from }} → {{ step.to }}</span>
                    <span class="text-muted-foreground shrink-0 tabular-nums">{{ step.readAt }}</span>
                </div>
                <p class="text-muted-foreground">{{ step.instruction }}</p>
            </li>
        </ul>
    </div>
</template>
