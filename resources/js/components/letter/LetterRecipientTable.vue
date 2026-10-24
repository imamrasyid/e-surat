<script setup lang="ts">
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormattedDate } from '@/composables/useFormattedDate';
import type { LetterRecipient } from '@/types';
import { computed } from 'vue';

const props = defineProps<{
    recipients: LetterRecipient[];
}>();

const emit = defineEmits<{
    open: [recipientId: number];
}>();

const { formatDateTime } = useFormattedDate();

const rows = computed(() =>
    props.recipients.map((recipient) => ({
        ...recipient,
        receivedLabel: formatDateTime(recipient.received_at),
        readLabel: formatDateTime(recipient.read_at),
    })),
);
</script>

<template>
    <div class="mt-6">
        <Accordion type="single" collapsible>
            <AccordionItem value="recipients">
                <AccordionTrigger class="cursor-pointer text-sm">Penerima:</AccordionTrigger>
                <AccordionContent>
                    <ScrollArea class="rounded-lg border">
                        <Table>
                            <TableHeader class="bg-muted/30">
                                <TableRow class="hover:bg-transparent">
                                    <TableHead class="min-w-38">Waktu Kirim</TableHead>
                                    <TableHead class="min-w-42">Dari</TableHead>
                                    <TableHead class="min-w-42">Kepada</TableHead>
                                    <TableHead class="min-w-50">Catatan</TableHead>
                                    <TableHead class="min-w-38">Waktu Baca</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="row in rows"
                                    :key="row.id"
                                    tabindex="0"
                                    class="focus-visible:bg-muted/50 cursor-pointer focus-visible:outline-none"
                                    @click="emit('open', row.id)"
                                    @keydown.enter.prevent="emit('open', row.id)"
                                    @keydown.space.prevent="emit('open', row.id)"
                                >
                                    <TableCell class="align-top">{{ row.receivedLabel }}</TableCell>
                                    <TableCell class="align-top">{{ row.from_position?.name ?? '-' }}</TableCell>
                                    <TableCell class="align-top">{{ row.to_position?.name ?? '-' }}</TableCell>
                                    <TableCell class="align-top">{{ row.instruction ?? '-' }}</TableCell>
                                    <TableCell class="align-top">{{ row.readLabel }}</TableCell>
                                </TableRow>
                                <TableRow v-if="rows.length === 0">
                                    <TableCell colspan="5" class="text-muted-foreground text-center">Belum ada penerima.</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                        <ScrollBar orientation="horizontal" />
                    </ScrollArea>
                </AccordionContent>
            </AccordionItem>
        </Accordion>
    </div>
</template>
