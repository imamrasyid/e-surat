<script setup lang="ts">
import DialogTimeline from '@/components/DialogTimeline.vue';
import Heading from '@/components/Heading.vue';
import LetterActions from '@/components/letter/LetterActions.vue';
import LetterAuditTimeline from '@/components/letter/LetterAuditTimeline.vue';
import LetterDocs from '@/components/letter/LetterDocs.vue';
import LetterMeta from '@/components/letter/LetterMeta.vue';
import LetterRecipientTable from '@/components/letter/LetterRecipientTable.vue';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { useRecipientTimeline } from '@/composables/useRecipientTimeline';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Letter } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ChevronLeft } from '@lucide/vue';
import { useDateFormat } from '@vueuse/core';

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    letter: Letter;
    recipient: number;
}>();

const createdAt = useDateFormat(props.letter.created_at, 'dddd, DD MMM YYYY, HH.mm', { locales: 'id-ID' });
const { data: timelineData, isOpen: isTimelineOpen, open: openTimeline } = useRecipientTimeline();

function goBack() {
    router.get(route('inbox.primary.index'));
}
</script>

<template>
    <Head title="Lihat Surat Masuk Utama" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6 2xl:px-12">
            <div class="flex flex-col gap-4 sm:flex-row sm:justify-between">
                <Heading class="mb-0!" title="Surat Masuk" description="Detail data surat masuk utama" />
                <div class="sm:text-end">
                    <p class="text-muted-foreground text-xs">{{ createdAt }}</p>
                    <p class="text-sm">Oleh {{ letter.sender_user?.name ?? '-' }}</p>
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
                                aria-label="Kembali ke daftar surat masuk"
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
                <LetterActions :letter="letter" :recipient="recipient" context="primary" />
            </div>

            <LetterMeta :letter="letter" />
            <LetterRecipientTable :recipients="letter.recipients ?? []" @open="openTimeline" />
            <LetterDocs :docs="letter.docs ?? []" />
            <LetterAuditTimeline :activities="letter.activities ?? []" />
        </div>

        <DialogTimeline v-model:open-dialog="isTimelineOpen" :data="timelineData" />
    </AppLayout>
</template>
