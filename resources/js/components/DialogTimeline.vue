<script setup lang="ts">
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import { useFormattedDate } from '@/composables/useFormattedDate';
import type { TimelineRecipient } from '@/types';
import { computed } from 'vue';

const props = defineProps<{
    data: TimelineRecipient[];
}>();

const open = defineModel<boolean>('openDialog', { required: true });

const { formatDateTime } = useFormattedDate();

const TYPE_LABELS: Record<string, string> = {
    disposition: 'Disposisi',
    memo: 'Nota Dinas',
};

const entries = computed(() =>
    props.data.map((item, index) => ({
        key: `${item.type}-${item.from_position_id}-${item.to_position_id}-${item.received_at}`,
        isLast: index === props.data.length - 1,
        instructions: item.instruction,
        followup: item.followup,
        steps: [
            {
                key: 'sender',
                title: 'Dikirim Dari',
                prefix: '',
                value: item.from_position.name,
                timeLabel: formatDateTime(item.received_at),
                timeIso: item.received_at ?? undefined,
                dotClass: 'bg-primary',
            },
            {
                key: 'recipient',
                title: item.type ? `Penerima ${TYPE_LABELS[item.type] ?? item.type}` : 'Penerima',
                prefix: item.is_cc ? 'cc:' : '',
                value: item.to_position.name,
                timeLabel: formatDateTime(item.read_at),
                timeIso: item.read_at ?? undefined,
                dotClass: item.read_at ? 'bg-success' : 'bg-destructive',
            },
        ],
    })),
);
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="max-h-[90dvh] grid-rows-[auto_minmax(0,1fr)_auto] p-0">
            <DialogHeader class="p-6 pb-0">
                <DialogTitle>Tindak Lanjut Surat</DialogTitle>
                <DialogDescription>Linimasa urutan disposisi dan nota dinas surat.</DialogDescription>
                <Separator class="mt-2" />
            </DialogHeader>

            <ScrollArea class="max-h-162.5 px-6">
                <div v-for="entry in entries" :key="entry.key">
                    <ul class="flex flex-col">
                        <li
                            v-for="step in entry.steps"
                            :key="step.key"
                            class="before:border-border relative flex items-center gap-4 py-2 before:absolute before:top-0 before:bottom-0 before:left-[0.4rem] before:border-l first:before:top-1/2 last:before:bottom-1/2"
                        >
                            <span class="bg-background relative py-1">
                                <span class="border-border bg-background flex h-3.5 w-3.5 items-center justify-center rounded-full border shadow-sm">
                                    <span class="h-1.5 w-1.5 rounded-full" :class="step.dotClass" />
                                </span>
                            </span>
                            <div class="w-full">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-muted-foreground text-xs">{{ step.title }}</p>
                                        <p class="text-sm/6 font-medium">
                                            <span v-if="step.prefix" class="text-muted-foreground text-xs font-normal">{{ step.prefix }}</span>
                                            {{ step.value }}
                                        </p>
                                    </div>
                                    <time class="text-xs" :datetime="step.timeIso">{{ step.timeLabel }}</time>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div v-if="entry.instructions" class="mb-2">
                        <p class="text-muted-foreground text-xs">Catatan</p>
                        <p class="text-sm">{{ entry.instructions }}</p>
                    </div>
                    <div v-if="entry.followup" class="pb-2">
                        <p class="text-muted-foreground text-xs">Followup</p>
                        <p class="text-sm">{{ entry.followup }}</p>
                    </div>
                    <Separator v-if="!entry.isLast" class="my-2" />
                </div>

                <div v-if="entries.length === 0" class="text-center">
                    <p class="text-muted-foreground text-sm">Belum ada disposisi atau nota dinas.</p>
                </div>
            </ScrollArea>
        </DialogContent>
    </Dialog>
</template>
