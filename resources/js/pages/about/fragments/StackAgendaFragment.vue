<script setup lang="ts">
import { primaryLetter } from '../letterData';

const [sequence, classification, year] = primaryLetter.agenda.split('/');
</script>

<template>
    <div class="bg-card dark:bg-input/30 space-y-3 rounded-xl border p-4 text-xs">
        <div class="flex items-center gap-1 font-mono text-sm tabular-nums">
            <span class="bg-muted/50 rounded-md border px-2 py-1">{{ sequence }}</span>
            <span class="text-muted-foreground">/</span>
            <span class="bg-muted/50 rounded-md border px-2 py-1">{{ classification }}</span>
            <span class="text-muted-foreground">/</span>
            <span class="bg-muted/50 rounded-md border px-2 py-1">{{ year }}</span>
        </div>

        <div class="flex items-center gap-2 rounded-lg border px-3 py-2">
            <span class="bg-destructive/10 text-destructive rounded px-1.5 py-0.5 text-[10px] font-medium">PDF</span>
            <span class="truncate font-mono text-[11px]">{{ primaryLetter.attachment }}</span>
        </div>

        <div class="flex items-center justify-between gap-3 border-t pt-3">
            <span class="text-muted-foreground">Penerima pertama</span>
            <span class="font-medium">{{ primaryLetter.recipients[0].from }}</span>
        </div>
    </div>
</template>
