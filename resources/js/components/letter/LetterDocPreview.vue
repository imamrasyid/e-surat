<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import type { LetterDoc } from '@/types';
import { Download, X } from '@lucide/vue';

defineProps<{
    doc: LetterDoc | null;
}>();

const open = defineModel<boolean>('open', { required: true });
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent
            :show-close-button="false"
            class="inset-0 top-0 left-0 grid h-dvh max-w-none translate-x-0 translate-y-0 grid-rows-[auto_minmax(0,1fr)] gap-0 rounded-none border-0 bg-transparent p-0 shadow-none sm:max-w-none"
        >
            <DialogHeader class="bg-background/80 flex-row items-center gap-3 px-3 py-2 text-left backdrop-blur">
                <DialogClose as-child>
                    <Button variant="ghost" size="icon" class="shrink-0 cursor-pointer">
                        <X />
                        <span class="sr-only">Tutup pratinjau</span>
                    </Button>
                </DialogClose>

                <DialogTitle class="line-clamp-1 min-w-0 flex-1 text-sm font-medium break-all">
                    {{ doc?.name ?? 'Pratinjau Lampiran' }}
                </DialogTitle>
                <DialogDescription class="sr-only">Pratinjau lampiran PDF di dalam aplikasi.</DialogDescription>

                <Button v-if="doc" as-child size="sm" class="shrink-0 cursor-pointer">
                    <a :href="doc.download_url" target="_blank" rel="noopener noreferrer">
                        <Download />
                        Unduh
                    </a>
                </Button>
            </DialogHeader>

            <div class="min-h-0">
                <iframe v-if="doc" :src="doc.preview_url" :title="`Pratinjau lampiran ${doc.name}`" class="size-full border-0" />
            </div>
        </DialogContent>
    </Dialog>
</template>
