<script setup lang="ts">
import { primaryLetter } from '../letterData';

const memo = {
    to: primaryLetter.recipients[1].to,
    cc: primaryLetter.recipients[0].from,
    note: primaryLetter.recipients[1].instruction,
};
</script>

<template>
    <div class="bg-card dark:bg-input/30 space-y-2 rounded-xl border p-4 text-xs">
        <dl class="space-y-2">
            <div class="flex items-start justify-between gap-3">
                <dt class="text-muted-foreground">Kepada</dt>
                <dd class="text-right font-medium">{{ memo.to }}</dd>
            </div>
            <div class="flex items-start justify-between gap-3">
                <dt class="text-muted-foreground">Tembusan</dt>
                <dd class="text-right font-medium">{{ memo.cc }}</dd>
            </div>
            <div class="flex items-start justify-between gap-3 border-t pt-2">
                <dt class="text-muted-foreground">Catatan</dt>
                <dd class="text-right">{{ memo.note }}</dd>
            </div>
        </dl>

        <div class="flex items-center gap-2 rounded-lg border px-3 py-2">
            <span class="bg-destructive/10 text-destructive rounded px-1.5 py-0.5 text-[10px] font-medium">PDF</span>
            <span class="truncate font-mono text-[11px]">{{ primaryLetter.attachment }}</span>
        </div>
    </div>
</template>
