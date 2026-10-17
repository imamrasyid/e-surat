<script setup lang="ts">
import DialogTimeline from '@/components/DialogTimeline.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Item, ItemContent, ItemGroup, ItemMedia, ItemTitle } from '@/components/ui/item';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { categoryInbox } from '@/data/helpers';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Letter, TimelineRecipient } from '@/types';
import { Head, useHttp } from '@inertiajs/vue3';
import { BadgeCheck, ChevronLeft, FolderPen, Printer, SquarePen, UserRoundPen } from '@lucide/vue';
import { useDateFormat } from '@vueuse/core';
import { onMounted, ref } from 'vue';

const props = defineProps<{
    breadcrumbItems: BreadcrumbItem[];
    letter: Letter;
}>();

const createdAt = useDateFormat(props.letter.created_at, 'dddd, DD MMM YYYY, HH.mm', { locales: 'id-ID' });

function getCategoryName(value: number) {
    const found = categoryInbox.find((cat) => cat.value === value);
    return found ? found.name : '-';
}

const http = useHttp();
const dialogTimeline = ref(false);
const timelineData = ref<TimelineRecipient[]>([]);

async function handleRowClick(itemId: number) {
    try {
        const response = await http.get(route('api.timeline.recipient', itemId));
        timelineData.value = response as TimelineRecipient[];

        dialogTimeline.value = true;
    } catch (err) {
        console.error('Gagal fetch data:', err);
    }
}

const hasHistory = ref(false);
const goBack = () => { window.history.back() };

onMounted(() => {
    hasHistory.value = window.history.length > 1;
});
</script>

<template>
    <Head title="Lihat Surat Terkirim" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="px-4 py-6 xl:px-12">
            <div class="flex flex-col gap-4 mb-8 sm:flex-row sm:items-center sm:justify-between">
                <Heading class="mb-0!" title="Detail Surat" description="Detail data surat terkirim" />
                <div class="flex items-center justify-between gap-1 text-sm sm:flex-col sm:items-end">
                    <p class="text-xs text-muted-foreground">{{ createdAt }}</p>
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
                    <dd class="max-w-md wrap-break-word">{{ letter.number }}</dd>
                </div>
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Perihal</dt>
                    <dd class="max-w-md wrap-break-word">{{ letter.subject }}</dd>
                </div>
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Tanggal Surat</dt>
                    <dd class="max-w-md wrap-break-word">{{ useDateFormat(letter.date, 'DD MMMM YYYY', { locales: 'id-ID' }) }}</dd>
                </div>
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Tanggal Diterima</dt>
                    <dd class="max-w-md wrap-break-word">{{ useDateFormat(letter.receipt_date, 'DD MMMM YYYY', { locales: 'id-ID' }) }}</dd>
                </div>
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Kategori</dt>
                    <dd class="max-w-md wrap-break-word">{{ getCategoryName(Number(letter.category)) }}</dd>
                </div>
                <div class="flex items-start space-x-4 text-sm lg:space-x-12">
                    <dt class="w-37 shrink-0 font-medium">Status Surat</dt>
                    <Badge v-if="letter.status === 'open'">
                        Open
                    </Badge>
                    <Badge v-else variant="secondary" class="bg-blue-500 text-white dark:bg-blue-600">
                        <BadgeCheck /> Close
                    </Badge>
                </div>
            </dl>

            <div class="flex flex-col gap-4 mt-6">
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

                <div class="rounded-md border overflow-hidden">
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
                            <TableRow v-for="item in letter.recipients" :key="item.id" class="cursor-pointer" @click="handleRowClick(item.id)">
                                <TableCell class="align-top">
                                    {{ item.received_at ? useDateFormat(item.received_at, 'DD MMM YYYY, HH:mm', { locales: 'id-ID' }) : '-' }}
                                </TableCell>
                                <TableCell class="align-top">{{ item.from_position?.name ?? '-' }}</TableCell>
                                <TableCell class="align-top">{{ item.to_position?.name ?? '-' }}</TableCell>
                                <TableCell class="align-top">{{ item.instruction ?? '-' }}</TableCell>
                                <TableCell class="align-top">
                                    {{ item.read_at ? useDateFormat(item.read_at, 'DD MMM YYYY, HH:mm', { locales: 'id-ID' }) : '-' }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>

            <div class="flex flex-col gap-4 mt-6">
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
                            <a :href="item.url" target="_blank" :title="item.name" rel="noopener noreferrer">
                                <Item variant="outline">
                                    <ItemMedia variant="image">
                                        <img src="../../../assets/media/pdf.svg" :alt="item.name" class="size-12 dark:hidden">
                                        <img src="../../../assets/media/pdf-dark.svg" :alt="item.name" class="size-12 dark:flex">
                                    </ItemMedia>
                                    <ItemContent class="min-w-0 flex-1">
                                        <ItemTitle class="line-clamp-2 font-normal min-w-0 break-all">
                                            {{ item.name }}
                                        </ItemTitle>
                                    </ItemContent>
                                </Item>
                            </a>
                        </template>
                    </ItemGroup>
                </div>
            </div>
        </div>

        <DialogTimeline v-model:open-dialog="dialogTimeline" :data="timelineData" />
    </AppLayout>
</template>
