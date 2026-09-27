<script setup lang="ts">
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import type { TimelineRecipient } from '@/types';
import { useDateFormat } from '@vueuse/core';
import { ref, watch } from 'vue';
import { ScrollArea } from './ui/scroll-area';
import { Separator } from './ui/separator';

const props = defineProps<{
    openDialog: boolean;
    data: TimelineRecipient[];
}>();

const emit = defineEmits<{
    (e: 'update:openDialog', value: boolean): void;
}>();

const dialogTimeline = ref(props.openDialog);

watch(
    () => props.openDialog,
    (val) => {
        dialogTimeline.value = val;
    },
);

watch(dialogTimeline, (val) => {
    emit('update:openDialog', val);
});
</script>
<template>
    <Dialog v-model:open="dialogTimeline">
        <DialogContent class="max-h-[90dvh] grid-rows-[auto_minmax(0,1fr)_auto] p-0">
            <DialogHeader class="p-6 pb-0">
                <DialogTitle>Timeline</DialogTitle>
                <DialogDescription>Linimasa urutan disposisi dan nota dinas surat.</DialogDescription>
                <Separator class="mt-2" />
            </DialogHeader>

            <ScrollArea class="grid max-h-[650px] px-6">
                <div v-for="(item, index) in data" :key="index">
                    <ul class="flex flex-col">
                        <li
                            class="relative flex items-center gap-4 py-2 before:absolute before:top-0 before:bottom-0 before:left-[0.4rem] before:border-l before:border-[#e3e3e0] first:before:top-1/2 last:before:bottom-1/2 dark:before:border-[#3E3E3A]"
                        >
                            <span class="bg-background relative py-1">
                                <span
                                    class="flex h-3.5 w-3.5 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] dark:border-[#3E3E3A] dark:bg-[#161615]"
                                >
                                    <span class="bg-primary h-1.5 w-1.5 rounded-full" />
                                </span>
                            </span>
                            <div class="w-full">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-muted-foreground text-sm">Dikirim dari</p>
                                        <h6 class="text-sm/6 font-medium">{{ item.from_position.name }}</h6>
                                    </div>
                                    <p class="text-xs">
                                        {{ item.received_at ? useDateFormat(item.received_at, 'DD MMM YYYY, HH:mm', { locales: 'id-ID' }) : '-' }}
                                    </p>
                                </div>
                            </div>
                        </li>
                        <li
                            class="relative flex items-center gap-4 py-2 before:absolute before:top-0 before:bottom-0 before:left-[0.4rem] before:border-l before:border-[#e3e3e0] first:before:top-1/2 last:before:bottom-1/2 dark:before:border-[#3E3E3A]"
                        >
                            <span class="bg-background relative py-1">
                                <span
                                    class="flex h-3.5 w-3.5 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] dark:border-[#3E3E3A] dark:bg-[#161615]"
                                >
                                    <span class="h-1.5 w-1.5 rounded-full" :class="item.read_at ? 'bg-success' : 'bg-destructive'" />
                                </span>
                            </span>
                            <div class="w-full">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-muted-foreground text-sm">Penerima</p>
                                        <h6 class="text-sm/6 font-medium"><span v-if="item.is_cc">cc: </span>{{ item.to_position.name }}</h6>
                                    </div>
                                    <p class="text-xs">
                                        {{ item.read_at ? useDateFormat(item.read_at, 'DD MMM YYYY, HH:mm', { locales: 'id-ID' }) : '-' }}
                                    </p>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div v-if="item.instruction" class="mb-2">
                        <p class="text-muted-foreground text-sm">Catatan</p>
                        <h6 class="text-sm">{{ item.instruction }}</h6>
                    </div>
                    <div v-if="item.followup" class="pb-2">
                        <p class="text-muted-foreground text-sm">Followup</p>
                        <h6 class="text-sm">{{ item.followup }}</h6>
                    </div>
                    <Separator v-if="data && index != data.length - 1" class="my-2" />
                </div>

                <div v-if="data.length == 0" class="text-center sm:text-start">
                    <p class="text-muted-foreground text-sm">Belum ada disposisi atau nota dinas.</p>
                </div>
            </ScrollArea>
        </DialogContent>
    </Dialog>
</template>
