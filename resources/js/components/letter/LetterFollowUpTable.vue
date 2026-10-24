<script setup lang="ts">
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormattedDate } from '@/composables/useFormattedDate';
import type { LetterFollowUpItem } from '@/types';
import { ChevronDown } from '@lucide/vue';
import { computed, reactive } from 'vue';

const props = defineProps<{
    items: LetterFollowUpItem[];
}>();

const emit = defineEmits<{
    open: [item: LetterFollowUpItem];
}>();

const { formatCreate } = useFormattedDate();

const expandedIds = reactive(new Set<number>());

const rows = computed(() =>
    props.items.map((item) => ({
        item,
        receivedAt: item.received_at ? formatCreate(item.received_at) : '-',
    })),
);

function isExpanded(id: number): boolean {
    return expandedIds.has(id);
}

function openItem(item: LetterFollowUpItem, event: KeyboardEvent): void {
    if (event.target !== event.currentTarget) return;

    event.preventDefault();
    emit('open', item);
}

function toggleDetails(id: number): void {
    if (expandedIds.has(id)) {
        expandedIds.delete(id);

        return;
    }

    expandedIds.add(id);
}
</script>

<template>
    <ScrollArea class="rounded-lg border">
        <Table>
            <TableHeader class="bg-muted/30">
                <TableRow class="hover:bg-transparent">
                    <TableHead class="min-w-42">Agenda</TableHead>
                    <TableHead class="min-w-42">Dari</TableHead>
                    <TableHead class="min-w-64">Catatan</TableHead>
                    <TableHead class="min-w-44">Followup</TableHead>
                    <TableHead class="w-32 text-right"></TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <template v-for="row in rows" :key="row.item.id">
                    <TableRow
                        tabindex="0"
                        class="focus-visible:bg-muted/50 cursor-pointer focus-visible:outline-none"
                        :class="{ 'font-semibold': !row.item.read_at }"
                        @click="emit('open', row.item)"
                        @keydown.enter="openItem(row.item, $event)"
                        @keydown.space="openItem(row.item, $event)"
                    >
                        <TableCell class="align-top">{{ row.item.letter?.agenda }}</TableCell>
                        <TableCell class="align-top">
                            <p v-if="row.item.is_cc" class="text-muted-foreground text-xs font-normal">CC Dari:</p>
                            {{ row.item.from_position?.name ?? '-' }}
                        </TableCell>
                        <TableCell class="align-top">{{ row.item.instruction ?? '-' }}</TableCell>
                        <TableCell class="align-top">{{ row.item.followup ?? '-' }}</TableCell>
                        <TableCell class="text-right align-top whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                {{ row.receivedAt }}
                                <button
                                    type="button"
                                    class="hover:bg-accent focus-visible:ring-ring/50 rounded-md p-1 transition-colors focus-visible:ring-3 focus-visible:outline-none"
                                    :aria-expanded="isExpanded(row.item.id)"
                                    :aria-controls="`follow-up-detail-${row.item.id}`"
                                    :aria-label="isExpanded(row.item.id) ? 'Sembunyikan informasi surat' : 'Tampilkan informasi surat'"
                                    @click.stop="toggleDetails(row.item.id)"
                                >
                                    <ChevronDown class="size-4 transition-transform" :class="{ 'rotate-180': isExpanded(row.item.id) }" />
                                </button>
                            </div>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="isExpanded(row.item.id)" :id="`follow-up-detail-${row.item.id}`" class="hover:bg-transparent">
                        <TableCell colspan="5" class="bg-muted/30">
                            <dl class="grid gap-x-8 gap-y-2 text-sm sm:grid-cols-3">
                                <div class="min-w-0">
                                    <dt class="text-muted-foreground text-xs">Asal Surat</dt>
                                    <dd class="font-medium wrap-break-word">{{ row.item.letter?.sender ?? '-' }}</dd>
                                </div>
                                <div class="min-w-0">
                                    <dt class="text-muted-foreground text-xs">Nomor Surat</dt>
                                    <dd class="font-medium wrap-break-word">{{ row.item.letter?.number ?? '-' }}</dd>
                                </div>
                                <div class="min-w-0">
                                    <dt class="text-muted-foreground text-xs">Perihal</dt>
                                    <dd class="font-medium wrap-break-word">{{ row.item.letter?.subject ?? '-' }}</dd>
                                </div>
                            </dl>
                        </TableCell>
                    </TableRow>
                </template>
                <TableRow v-if="rows.length === 0">
                    <TableCell colspan="5" class="text-muted-foreground text-center">Data tidak ditemukan.</TableCell>
                </TableRow>
            </TableBody>
        </Table>
        <ScrollBar orientation="horizontal" />
    </ScrollArea>
</template>
