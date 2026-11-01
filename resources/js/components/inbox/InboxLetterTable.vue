<script setup lang="ts">
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { useFormattedDate } from '@/composables/useFormattedDate';
import type { LetterRecipient } from '@/types';
import { BadgeInfo } from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps<{
    items: LetterRecipient[];
}>();

const emit = defineEmits<{
    open: [item: LetterRecipient];
}>();

const { formatCreate } = useFormattedDate();

const MAX_ACTIVITIES = 4;

const rows = computed(() =>
    props.items.map((item) => {
        const activities = (item.activities ?? []).map((activity) => ({
            key: `${activity.type}-${activity.id}`,
            label: activity.type === 'disposisi' ? 'D' : 'ND',
            className: activity.type === 'disposisi' ? 'bg-primary' : 'bg-warning',
        }));

        return {
            item,
            activities: activities.slice(0, MAX_ACTIVITIES),
            overflow: Math.max(activities.length - MAX_ACTIVITIES, 0),
            receivedAt: item.received_at ? formatCreate(item.received_at) : '-',
        };
    }),
);

function openItem(item: LetterRecipient) {
    emit('open', item);
}
</script>

<template>
    <ScrollArea class="rounded-lg border">
        <Table>
            <TableHeader class="bg-muted/30">
                <TableRow class="hover:bg-transparent">
                    <TableHead class="min-w-32">Agenda</TableHead>
                    <TableHead class="min-w-42">Nomor Surat</TableHead>
                    <TableHead class="min-w-46">Perihal</TableHead>
                    <TableHead class="min-w-37">
                        <span class="inline-flex items-center gap-1">
                            Tindak Lanjut
                            <TooltipProvider>
                                <Tooltip>
                                    <TooltipTrigger aria-label="Keterangan tindak lanjut">
                                        <BadgeInfo class="size-4" />
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>D mewakili Disposisi; ND mewakili Nota Dinas.</p>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </span>
                    </TableHead>
                    <TableHead class="w-25 text-right"></TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="row in rows"
                    :key="row.item.id"
                    tabindex="0"
                    class="focus-visible:bg-muted/50 cursor-pointer focus-visible:outline-none"
                    :class="{ 'font-semibold': !row.item.read_at }"
                    @click="openItem(row.item)"
                    @keydown.enter.prevent="openItem(row.item)"
                    @keydown.space.prevent="openItem(row.item)"
                >
                    <TableCell class="align-top">{{ row.item.letter?.agenda }}</TableCell>
                    <TableCell class="align-top">{{ row.item.letter?.number ?? '-' }}</TableCell>
                    <TableCell class="align-top">
                        {{ row.item.letter?.sender }}
                        <p class="text-muted-foreground line-clamp-1" :title="row.item.letter?.subject">
                            {{ row.item.letter?.subject }}
                        </p>
                    </TableCell>
                    <TableCell class="align-top">
                        <div class="flex -space-x-2">
                            <span
                                v-for="activity in row.activities"
                                :key="activity.key"
                                class="ring-background relative inline-flex size-7 shrink-0 items-center justify-center rounded-full text-xs leading-none text-gray-100 ring-1 hover:z-5"
                                :class="activity.className"
                            >
                                {{ activity.label }}
                            </span>
                            <span
                                v-if="row.overflow > 0"
                                class="bg-muted ring-background relative inline-flex size-7 shrink-0 items-center justify-center rounded-full text-xs leading-none ring-1"
                                :title="`${row.overflow} tindak lanjut lainnya`"
                            >
                                +{{ row.overflow }}
                            </span>
                            <span v-if="row.activities.length === 0">-</span>
                        </div>
                    </TableCell>
                    <TableCell class="text-right align-top whitespace-nowrap">
                        {{ row.receivedAt }}
                    </TableCell>
                </TableRow>
                <TableRow v-if="rows.length === 0">
                    <TableCell colspan="5" class="text-muted-foreground text-center">Data tidak ditemukan.</TableCell>
                </TableRow>
            </TableBody>
        </Table>
        <ScrollBar orientation="horizontal" />
    </ScrollArea>
</template>
