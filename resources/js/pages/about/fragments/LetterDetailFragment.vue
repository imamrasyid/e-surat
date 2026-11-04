<script setup lang="ts">
import { primaryLetter } from '../letterData';
</script>

<template>
    <div class="bg-card dark:bg-input/30 overflow-hidden rounded-lg border text-xs">
        <div class="flex items-center justify-between border-b px-3 py-2">
            <span class="font-medium">Surat masuk</span>
            <span class="bg-primary/10 text-primary rounded px-1.5 py-0.5 text-[10px] font-medium">Open</span>
        </div>

        <div class="space-y-3 p-3">
            <div>
                <p class="text-muted-foreground text-[10px] tracking-wide uppercase">Nomor agenda</p>
                <p class="font-mono text-base font-semibold tabular-nums">{{ primaryLetter.agenda }}</p>
            </div>

            <dl class="divide-border divide-y border-y">
                <div class="flex justify-between gap-3 py-1.5">
                    <dt class="text-muted-foreground shrink-0">Pengirim</dt>
                    <dd class="truncate text-right font-medium">{{ primaryLetter.sender }}</dd>
                </div>
                <div class="flex justify-between gap-3 py-1.5">
                    <dt class="text-muted-foreground shrink-0">Nomor surat</dt>
                    <dd class="truncate text-right font-mono text-[11px]">{{ primaryLetter.number }}</dd>
                </div>
                <div class="flex justify-between gap-3 py-1.5">
                    <dt class="text-muted-foreground shrink-0">Perihal</dt>
                    <dd class="truncate text-right">{{ primaryLetter.subject }}</dd>
                </div>
                <div class="text-muted-foreground flex flex-wrap justify-between gap-2 py-1.5">
                    <span><b class="text-foreground font-medium">Surat</b> {{ primaryLetter.date }}</span>
                    <span><b class="text-foreground font-medium">Diterima</b> {{ primaryLetter.receiptDate }}</span>
                </div>
            </dl>

            <div class="space-y-1.5">
                <p class="text-muted-foreground text-[10px] tracking-wide uppercase">Penerima</p>
                <div class="overflow-hidden rounded-md border" aria-hidden="true">
                    <div class="text-muted-foreground bg-muted/50 grid grid-cols-[1fr_1fr_1.2fr_auto] gap-2 border-b px-2 py-1 text-[10px]">
                        <span>Dari</span>
                        <span>Kepada</span>
                        <span>Instruksi</span>
                        <span>Baca</span>
                    </div>
                    <div
                        v-for="step in primaryLetter.recipients"
                        :key="step.to"
                        class="grid grid-cols-[1fr_1fr_1.2fr_auto] gap-2 border-b px-2 py-1 last:border-b-0"
                    >
                        <span class="truncate">{{ step.from }}</span>
                        <span class="truncate">{{ step.to }}</span>
                        <span class="text-muted-foreground truncate">{{ step.instruction }}</span>
                        <span class="text-muted-foreground text-[10px] tabular-nums">{{ step.readAt }}</span>
                    </div>
                </div>
            </div>

            <div class="space-y-1.5">
                <p class="text-muted-foreground text-[10px] tracking-wide uppercase">Lampiran</p>
                <div class="flex items-center gap-2 rounded-md border px-2 py-1.5" aria-hidden="true">
                    <span class="bg-destructive/10 text-destructive rounded px-1.5 py-0.5 text-[10px] font-medium">PDF</span>
                    <span class="truncate font-mono text-[11px]">{{ primaryLetter.attachment }}</span>
                </div>
            </div>

            <div class="space-y-1.5">
                <p class="text-muted-foreground text-[10px] tracking-wide uppercase">Riwayat</p>
                <ol class="space-y-1" aria-hidden="true">
                    <li class="flex gap-2">
                        <span class="text-muted-foreground w-20 shrink-0 tabular-nums">13 Okt, 08.42</span>
                        <span>Diterima dan dicatat</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="text-muted-foreground w-20 shrink-0 tabular-nums">13 Okt, 09.14</span>
                        <span>Diteruskan ke Kabag Umum</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="text-muted-foreground w-20 shrink-0 tabular-nums">13 Okt, 10.02</span>
                        <span>Diteruskan ke Kasubbag Perencanaan</span>
                    </li>
                </ol>
            </div>

            <div class="space-y-1.5">
                <p class="text-muted-foreground text-[10px] tracking-wide uppercase">Catatan</p>
                <p class="bg-muted/40 rounded-md p-2">{{ primaryLetter.instruction }}</p>
            </div>

            <div class="text-muted-foreground flex items-center justify-between gap-2 border-t pt-2 text-[11px]" aria-hidden="true">
                <span>Status terakhir</span>
                <span class="text-foreground">Open</span>
            </div>
        </div>
    </div>
</template>
