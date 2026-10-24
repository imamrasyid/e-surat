<script setup lang="ts">
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormattedDate } from '@/composables/useFormattedDate';
import type { TimelineRecipient } from '@/types';
import { computed } from 'vue';

const props = defineProps<{
    items: TimelineRecipient[];
}>();

const { formatNumericDateTime } = useFormattedDate();

const rows = computed(() =>
    props.items.map((item) => ({
        item,
        receivedLabel: formatNumericDateTime(item.received_at),
        readLabel: formatNumericDateTime(item.read_at),
    })),
);
</script>

<template>
    <ScrollArea class="rounded-lg border">
        <Table>
            <TableHeader class="bg-muted/30">
                <TableRow class="hover:bg-transparent">
                    <TableHead class="min-w-30">Tgl Kirim</TableHead>
                    <TableHead class="min-w-42">Dari</TableHead>
                    <TableHead class="min-w-42">Kepada</TableHead>
                    <TableHead class="min-w-64">Catatan</TableHead>
                    <TableHead class="min-w-44">Followup</TableHead>
                    <TableHead class="min-w-30">Tgl Baca</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="row in rows" :key="row.item.from_position_id + '-' + row.item.to_position_id + '-' + row.item.received_at">
                    <TableCell class="align-top whitespace-nowrap">{{ row.receivedLabel }}</TableCell>
                    <TableCell class="align-top">{{ row.item.from_position.name }}</TableCell>
                    <TableCell class="align-top">
                        <span v-if="row.item.is_cc" class="text-muted-foreground text-xs">cc: </span>
                        {{ row.item.to_position.name }}
                    </TableCell>
                    <TableCell class="align-top">{{ row.item.instruction ?? '-' }}</TableCell>
                    <TableCell class="align-top">{{ row.item.followup ?? '-' }}</TableCell>
                    <TableCell class="align-top whitespace-nowrap">{{ row.readLabel }}</TableCell>
                </TableRow>
                <TableRow v-if="rows.length === 0">
                    <TableCell colspan="6" class="text-muted-foreground text-center">Belum ada linimasa.</TableCell>
                </TableRow>
            </TableBody>
        </Table>
        <ScrollBar orientation="horizontal" />
    </ScrollArea>
</template>
