<script setup lang="ts">
import DialogTimeline from '@/components/DialogTimeline.vue';
import Heading from '@/components/Heading.vue';
import LetterDocPreview from '@/components/letter/LetterDocPreview.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Item, ItemContent, ItemGroup, ItemMedia, ItemTitle } from '@/components/ui/item';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { useFormattedDate } from '@/composables/useFormattedDate';
import { usePermissions } from '@/composables/usePermissions';
import { useRecipientTimeline } from '@/composables/useRecipientTimeline';
import { categoryInbox, letterReceivedVia, letterSecurityClassification } from '@/data/helpers';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Letter, LetterDoc } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { BadgeCheck, ChevronLeft, FolderPen, Printer, SquarePen, Trash2, UserRoundPen } from '@lucide/vue';
import { useDateFormat } from '@vueuse/core';
import { onMounted, ref } from 'vue';

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    letter: Letter;
}>();

const { formatDate, formatDateTime } = useFormattedDate();
const { data: timelineData, isOpen: isTimelineOpen, open: openTimeline } = useRecipientTimeline();

const createdAt = useDateFormat(props.letter.created_at, 'dddd, DD MMM YYYY, HH.mm', { locales: 'id-ID' });

function getCategoryName(value: string) {
    const found = categoryInbox.find((cat) => cat.value === value);
    return found ? found.name : '-';
}

function getOptionName(options: { value: string; name: string }[], value?: string | null) {
    return options.find((option) => option.value === value)?.name ?? '-';
}

const isPreviewOpen = ref(false);
const selectedDoc = ref<LetterDoc | null>(null);

const openPreview = (doc: LetterDoc) => {
    selectedDoc.value = doc;
    isPreviewOpen.value = true;
};

const hasHistory = ref(false);
const goBack = () => {
    window.history.back();
};

const { can } = usePermissions();
const showDeleteDialog = ref(false);
const isDeleting = ref(false);

const confirmDelete = () => {
    router.delete(route('sent.destroy', props.letter.letter_id), {
        preserveScroll: true,
        onStart: () => {
            isDeleting.value = true;
        },
        onFinish: () => {
            isDeleting.value = false;
            showDeleteDialog.value = false;
        },
    });
};

onMounted(() => {
    hasHistory.value = window.history.length > 1;
});
</script>

<template>
    <Head title="Lihat Surat Terkirim" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6 2xl:px-12">
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <Heading class="mb-0!" title="Detail Surat" description="Detail data surat terkirim" />
                <div class="flex items-center justify-between gap-1 text-sm sm:flex-col sm:items-end">
                    <p class="text-muted-foreground text-xs">{{ createdAt }}</p>
                    <p>Oleh {{ letter.sender_user?.name ?? '-' }}</p>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <TooltipProvider v-if="hasHistory">
                    <Tooltip>
                        <TooltipTrigger>
                            <Button variant="outline" size="icon" class="cursor-pointer rounded-full" @click="goBack()">
                                <ChevronLeft class="size-5" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>Kembali</p>
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>
                <div class="flex gap-2">
                    <Button variant="outline" size="icon" class="cursor-pointer rounded-full">
                        <Printer />
                    </Button>
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger>
                                <Button
                                    variant="outline"
                                    size="icon"
                                    class="cursor-pointer rounded-full"
                                    :disabled="letter.status !== 'open'"
                                    @click="$inertia.get(route('sent.editletter', letter.letter_id))"
                                >
                                    <SquarePen />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>Perbarui Surat</p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </div>
            </div>

            <dl class="mt-6 space-y-2">
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Nomor Agenda</dt>
                    <dd class="max-w-md wrap-break-word">{{ letter.agenda }}</dd>
                </div>
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Pengirim</dt>
                    <dd class="max-w-md wrap-break-word">{{ letter.sender }}</dd>
                </div>
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Nomor Surat</dt>
                    <dd class="max-w-md wrap-break-word">{{ letter.number ?? '-' }}</dd>
                </div>
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Perihal</dt>
                    <dd class="max-w-md wrap-break-word">{{ letter.subject }}</dd>
                </div>
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Tanggal Surat</dt>
                    <dd class="max-w-md wrap-break-word">{{ formatDate(letter.date) }}</dd>
                </div>
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Tanggal Diterima</dt>
                    <dd class="max-w-md wrap-break-word">{{ formatDate(letter.receipt_date) }}</dd>
                </div>
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Kategori</dt>
                    <dd class="max-w-md wrap-break-word">{{ getCategoryName(letter.category) }}</dd>
                </div>
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Sifat Surat</dt>
                    <dd class="max-w-md wrap-break-word">
                        {{ getOptionName(letterSecurityClassification, letter.security_classification) }}
                    </dd>
                </div>
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Sumber Penerimaan</dt>
                    <dd class="max-w-md wrap-break-word">{{ getOptionName(letterReceivedVia, letter.received_via) }}</dd>
                </div>
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Status Surat</dt>
                    <Badge v-if="letter.status === 'open'"> Terbuka </Badge>
                    <Badge v-else variant="secondary" class="bg-blue-500 text-white dark:bg-blue-600"> <BadgeCheck /> Selesai </Badge>
                </div>
            </dl>

            <div class="mt-6 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium">Penerima:</p>
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger>
                                <Button
                                    size="icon"
                                    variant="outline"
                                    class="cursor-pointer rounded-full"
                                    :disabled="letter.status !== 'open'"
                                    @click="$inertia.get(route('sent.editrecipients', letter.letter_id))"
                                >
                                    <UserRoundPen />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>Perbarui Penerima</p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </div>

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
                                v-for="item in letter.recipients"
                                :key="item.id"
                                class="focus-visible:bg-muted/50 cursor-pointer focus-visible:outline-none"
                                @click="openTimeline(item.id)"
                                @keydown.enter.prevent="openTimeline(item.id)"
                                @keydown.space.prevent="openTimeline(item.id)"
                            >
                                <TableCell class="align-top">{{ formatDateTime(item.received_at) }}</TableCell>
                                <TableCell class="align-top">{{ item.from_position?.name ?? '-' }}</TableCell>
                                <TableCell class="align-top">{{ item.to_position?.name ?? '-' }}</TableCell>
                                <TableCell class="align-top">{{ item.instruction ?? '-' }}</TableCell>
                                <TableCell class="align-top">{{ formatDateTime(item.read_at) }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                    <ScrollBar orientation="horizontal" />
                </ScrollArea>
            </div>

            <div class="mt-6 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium">Lampiran:</p>
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger>
                                <Button
                                    size="icon"
                                    variant="outline"
                                    class="cursor-pointer rounded-full"
                                    :disabled="letter.status !== 'open'"
                                    @click="$inertia.get(route('sent.editdocs', letter.letter_id))"
                                >
                                    <FolderPen />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>Perbarui Lampiran</p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </div>
                <div class="flex w-full flex-col">
                    <ItemGroup class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 2xl:grid-cols-4">
                        <template v-for="item in letter.docs" :key="item.id">
                            <button
                                type="button"
                                class="w-full cursor-pointer text-left"
                                :title="`Pratinjau ${item.name}`"
                                @click="openPreview(item)"
                            >
                                <Item variant="outline" class="rounded-lg">
                                    <ItemMedia variant="image">
                                        <img src="../../../assets/media/pdf.svg" :alt="item.name" class="size-12 dark:hidden" />
                                        <img src="../../../assets/media/pdf-dark.svg" :alt="item.name" class="hidden size-12 dark:block" />
                                    </ItemMedia>
                                    <ItemContent class="min-w-0 flex-1">
                                        <ItemTitle class="line-clamp-2 min-w-0 font-normal break-all">
                                            {{ item.name }}
                                        </ItemTitle>
                                    </ItemContent>
                                </Item>
                            </button>
                        </template>
                    </ItemGroup>
                </div>
            </div>

            <div v-if="can('sent.update')" class="mt-10 flex justify-end border-t pt-6">
                <Button variant="destructive" class="cursor-pointer" :disabled="isDeleting" @click="showDeleteDialog = true">
                    <Trash2 />
                    Hapus Surat
                </Button>
            </div>
        </div>

        <AlertDialog v-model:open="showDeleteDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Yakin menghapus surat?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Surat <strong>{{ letter.subject }}</strong> beserta penerima, disposisi, memo, dan lampirannya akan dihapus (soft delete).
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel :disabled="isDeleting">Batal</AlertDialogCancel>
                    <AlertDialogAction :disabled="isDeleting" @click="confirmDelete">Ya, Hapus</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <LetterDocPreview v-model:open="isPreviewOpen" :doc="selectedDoc" />

        <DialogTimeline v-model:open-dialog="isTimelineOpen" :data="timelineData" />
    </AppLayout>
</template>
