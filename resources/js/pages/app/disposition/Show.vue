<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import LetterActions from '@/components/letter/LetterActions.vue';
import LetterDocs from '@/components/letter/LetterDocs.vue';
import LetterMeta from '@/components/letter/LetterMeta.vue';
import LetterTimelineTable from '@/components/letter/LetterTimelineTable.vue';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { useFormattedDate } from '@/composables/useFormattedDate';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Disposition, Letter, TimelineRecipient } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ChevronLeft } from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    from: { id: number; name: string };
    disposition: Disposition;
    recipients: TimelineRecipient[];
    letter: Letter;
}>();

const { formatFullDateTime } = useFormattedDate();

const receivedAt = computed(() => formatFullDateTime(props.disposition.received_at));

function goBack() {
    router.get(route('dispo.index'));
}
</script>

<template>
    <Head title="Lihat Disposisi Masuk" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6 2xl:px-12">
            <div class="flex flex-col gap-4 sm:flex-row sm:justify-between">
                <Heading class="mb-0!" title="Disposisi" description="Detail data disposisi masuk" />
                <div class="sm:text-end">
                    <p class="text-muted-foreground text-xs">{{ receivedAt }}</p>
                    <p class="text-sm">Oleh {{ from.name }}</p>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button
                                variant="outline"
                                size="icon"
                                class="cursor-pointer rounded-full"
                                aria-label="Kembali ke daftar disposisi"
                                @click="goBack"
                            >
                                <ChevronLeft class="size-5" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>Kembali</p>
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>
                <LetterActions :letter="letter" :recipient="disposition.letter_recipient_id" context="disposisi" :disabled="disposition.is_cc" />
            </div>

            <LetterMeta :letter="letter" show-category />

            <div class="mt-6">
                <Accordion type="single" collapsible default-value="timeline">
                    <AccordionItem value="timeline">
                        <AccordionTrigger class="cursor-pointer text-sm">Timeline:</AccordionTrigger>
                        <AccordionContent>
                            <LetterTimelineTable :items="recipients" />
                        </AccordionContent>
                    </AccordionItem>
                </Accordion>
            </div>

            <LetterDocs :docs="letter.docs ?? []" />
        </div>
    </AppLayout>
</template>
